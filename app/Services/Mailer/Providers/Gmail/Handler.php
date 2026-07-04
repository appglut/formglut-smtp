<?php

namespace FormglutMail\App\Services\Mailer\Providers\Gmail;

use FormglutMail\App\Models\Settings;
use FormglutMail\Includes\Support\Arr;
use FormglutMail\App\Services\Mailer\BaseHandler;

class Handler extends BaseHandler
{

    public function send()
    {
        if ($this->preSend() && $this->phpMailer->preSend()) {
            return $this->postSend();
        }

        return $this->handleResponse(new \WP_Error(422, 'Something went wrong!', []));
    }

    protected function postSend()
    {
        try {
            $returnResponse = $this->sendViaApi();
        } catch (\Exception $e) {
            $returnResponse = new \WP_Error(422, $e->getMessage(), []);
        }

        $this->response = $returnResponse;

        return $this->handleResponse($this->response);
    }

    public function setSettings($settings)
    {
        if (Arr::get($settings, 'key_store') == 'wp_config') {
            $settings['client_id'] = defined('FORMGLUTMAIL_GMAIL_CLIENT_ID') ? FORMGLUTMAIL_GMAIL_CLIENT_ID : '';
            $settings['client_secret'] = defined('FORMGLUTMAIL_GMAIL_CLIENT_SECRET') ? FORMGLUTMAIL_GMAIL_CLIENT_SECRET : '';
        }

        $this->settings = $settings;

        return $this;
    }

    private function sendViaApi()
    {
        if (!class_exists('\FormglutSmtpLib\Google\Service\Gmail\Message')) {
            require_once FORMGLUTMAIL_PLUGIN_PATH . 'includes/libs/google-api-client/build/vendor/autoload.php';
        }

        $message = $this->phpMailer->getSentMIMEMessage();

        $data = $this->getSetting();

        $googleApiMessage = new \FormglutSmtpLib\Google\Service\Gmail\Message();

        $file_size = strlen($message);
        $googleClient = $this->getApiClient($data);

        if (is_wp_error($googleClient)) {
            return $googleClient;
        }

        $googleService = new \FormglutSmtpLib\Google\Service\Gmail($googleClient);

        $result = array();
        try {
            $googleClient->setDefer(true);
            $result = $googleService->users_messages->send('me', $googleApiMessage, array('uploadType' => 'resumable'));

            $chunkSizeBytes = 1 * 1024 * 1024;

            // create mediafile upload
            $media = new \FormglutSmtpLib\Google\Http\MediaFileUpload(
                $googleClient,
                $result,
                'message/rfc822',
                $message,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize($file_size);

            $status = false;
            while (!$status) {
                $status = $media->nextChunk();
            }
            $result = false;

            // Reset to the client to execute requests immediately in the future.
            $googleClient->setDefer(false);

            $googleMessageId = $status->getId();

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return new \WP_Error(422, $errorMessage, []);
        }

        return array(
            'MessageId' => $googleMessageId,
        );
    }

    public function validateProviderInformation($connection)
    {
        $errors = [];

        $keyStoreType = $connection['key_store'];

        $clientId = Arr::get($connection, 'client_id');
        $clientSecret = Arr::get($connection, 'client_secret');

        if ($keyStoreType == 'db') {
            if (!$clientId) {
                $errors['client_id']['required'] = __('Application Cluent ID is required.', 'formglut-smtp');
            }

            if (!$clientSecret) {
                $errors['client_secret']['required'] = __('Application Client Secret key is required.', 'formglut-smtp');
            }
        } else if ($keyStoreType == 'wp_config') {
            if (!defined('FORMGLUTMAIL_GMAIL_CLIENT_ID') || !FORMGLUTMAIL_GMAIL_CLIENT_ID) {
                $errors['client_id']['required'] = __('Please define FORMGLUTMAIL_GMAIL_CLIENT_ID in wp-config.php file.', 'formglut-smtp');
            } else {
                $clientId = FORMGLUTMAIL_GMAIL_CLIENT_ID;
            }

            if (!defined('FORMGLUTMAIL_GMAIL_CLIENT_SECRET') || !FORMGLUTMAIL_GMAIL_CLIENT_SECRET) {
                $errors['client_secret']['required'] = __('Please define FORMGLUTMAIL_GMAIL_CLIENT_SECRET in wp-config.php file.', 'formglut-smtp');
            } else {
                $clientSecret = FORMGLUTMAIL_GMAIL_CLIENT_SECRET;
            }
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }

        $accessToken = Arr::get($connection, 'access_token');
        $authToken = Arr::get($connection, 'auth_token');

        if (!$accessToken && $authToken) {
            // this is new, We have to generate the tokens
            $body = [
                'code'          => $authToken,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => apply_filters('formglutsmtp_gapi_callback', 'https://formglutsmtp.com/gapi/'), // 'urn:ietf:wg:oauth:2.0:oob'
                'client_id'     => $clientId,
                'client_secret' => $clientSecret
            ];
            $tokens = $this->makeRequest('https://accounts.google.com/o/oauth2/token', $body, 'POST');
            if (is_wp_error($tokens)) {
                $errors['auth_token']['required'] = $tokens->get_error_message();
            } else {
                add_filter('formglutmail_saving_connection_data', function ($con, $provider) use ($connection, $tokens) {

                    if ($provider != 'gmail') {
                        return $con;
                    }

                    if (Arr::get($con, 'connection.sender_email') != $connection['sender_email']) {
                        return $con;
                    }

                    $con['connection']['refresh_token'] = $tokens['refresh_token'];
                    $con['connection']['access_token'] = $tokens['access_token'];
                    $con['connection']['auth_token'] = '';
                    $con['connection']['expire_stamp'] = time() + $tokens['expires_in'];
                    $con['connection']['expires_in'] = $tokens['expires_in'];
                    $con['connection']['version'] = 2;
                    return $con;
                }, 10, 2);
            }
        } else if (!$authToken && !$accessToken) {
            $errors['auth_token']['required'] = __('Please Provide Auth Token.', 'formglut-smtp');
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }
    }

    private function makeRequest($url, $bodyArgs, $type = 'GET', $headers = false)
    {
        if (!$headers) {
            $headers = array(
                'Content-Type'              => 'application/http',
                'Content-Transfer-Encoding' => 'binary',
                'MIME-Version'              => '1.0',
            );
        }

        $args = [
            'headers' => $headers
        ];
        if ($bodyArgs) {
            $args['body'] = json_encode($bodyArgs);
        }


        $args['method'] = $type;
        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            return new \WP_Error(422, $message);
        }

        $body = json_decode(wp_remote_retrieve_body($request), true);

        if (!empty($body['error'])) {
            $error = 'Unknown Error';
            if (isset($body['error_description'])) {
                $error = $body['error_description'];
            } else if (!empty($body['error']['message'])) {
                $error = $body['error']['message'];
            }
            return new \WP_Error(422, $error);
        }

        return $body;
    }

    private function saveNewTokens($existingData, $tokens)
    {
        if (empty($tokens['access_token']) || empty($tokens['refresh_token'])) {
            return false;
        }

        $senderEmail = $existingData['sender_email'];

        $existingData['access_token'] = $tokens['access_token'];
        $existingData['refresh_token'] = $tokens['refresh_token'];
        $existingData['expire_stamp'] = $tokens['expires_in'] + time();
        $existingData['expires_in'] = $tokens['expires_in'];

        (new Settings())->updateConnection($senderEmail, $existingData);
        formglutMailGetProvider($senderEmail, true); // we are clearing the static cache here
        wp_schedule_single_event($existingData['expire_stamp'] - 360, 'formglutsmtp_renew_gmail_token');
        return true;
    }

    private function getApiClient($data)
    {
        $senderEmail = $data['sender_email'];

        static $cachedServices = [];
        if (isset($cachedServices[$senderEmail])) {
            return $cachedServices[$senderEmail];
        }

        if (!class_exists('\FormglutSmtpLib\Google\Client')) {
            require_once FORMGLUTMAIL_PLUGIN_PATH . 'includes/libs/google-api-client/build/vendor/autoload.php';
        }

        $client = new \FormglutSmtpLib\Google\Client();
        $client->setClientId($data['client_id']);
        $client->setClientSecret($data['client_secret']);
        $client->addScope("https://www.googleapis.com/auth/gmail.compose");
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $tokens = [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in'    => $data['expire_stamp'] - time()
        ];

        $client->setAccessToken($tokens);

        // check if expired or will be expired in 5 minutes
        if (($data['expire_stamp'] - 300) < time()) {
            $newTokens = $client->refreshToken($data['refresh_token']);

            $result = $this->saveNewTokens($data, $newTokens);

            if (!$result) {
                $errorDescription = Arr::get($newTokens, 'error_description');
                if (!$errorDescription) {
                    $errorDescription = 'Failed to renew token with Gmail Api';
                }

                return new \WP_Error('api_error', $errorDescription);
            }

            $client->setAccessToken($newTokens);
        }

        $cachedServices[$senderEmail] = $client;

        return $cachedServices[$senderEmail];
    }

    public function getConnectionInfo($connection)
    {
        if (Arr::get($connection, 'key_store') == 'wp_config') {
            $connection['client_id'] = defined('FORMGLUTMAIL_GMAIL_CLIENT_ID') ? FORMGLUTMAIL_GMAIL_CLIENT_ID : '';
            $connection['client_secret'] = defined('FORMGLUTMAIL_GMAIL_CLIENT_SECRET') ? FORMGLUTMAIL_GMAIL_CLIENT_SECRET : '';
        }

        if (!class_exists('\FormglutSmtpLib\Google\Client')) {
            require_once FORMGLUTMAIL_PLUGIN_PATH . 'includes/libs/google-api-client/build/vendor/autoload.php';
        }

        $client = $this->getApiClient($connection);

        if (is_wp_error($client)) {
            return '<p style="color: red; text-align: center; font-size: 18px;">ERROR: ' . $connection->get_error_message() . '</p>';
        }

        $info = formglutMailgetConnection($connection['sender_email']);

        $connection = $info->getSetting();

        $extraRow = [
            'title'   => __('Token Validity', 'formglut-smtp'),
            'content' => 'Valid (' . (int)(($connection['expire_stamp'] - time()) / 60) . 'minutes)'
        ];

        if (($connection['expire_stamp']) < time()) {
            $extraRow['content'] = 'Invalid. Please re-authenticate';
        }

        $connection['extra_rows'] = [$extraRow];

        return [
            'info' => (string)formglutMail('view')->make('admin.general_connection_info', [
                'connection' => $connection
            ])
        ];
    }
}
