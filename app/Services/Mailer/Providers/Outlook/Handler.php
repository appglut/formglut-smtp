<?php

namespace FormglutMail\App\Services\Mailer\Providers\Outlook;

use FormglutMail\App\Models\Settings;
use FormglutMail\Includes\Support\Arr;
use FormglutMail\App\Services\Mailer\BaseHandler;

class Handler extends BaseHandler
{

    public function send()
    {
        $this->phpMailer->Encoding = 'base64';

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
            $settings['client_id'] = defined('FORMGLUTMAIL_OUTLOOK_CLIENT_ID') ? FORMGLUTMAIL_OUTLOOK_CLIENT_ID : '';
            $settings['client_secret'] = defined('FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET') ? FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET : '';
        }

        $this->settings = $settings;

        return $this;
    }

    private function sendViaApi()
    {
        $mime = chunk_split(base64_encode($this->phpMailer->getSentMIMEMessage()), 76, "\n");

        $data = $this->getSetting();

        $accessToken = $this->getAccessToken($data);

        $api = (new API($data['client_id'], $data['client_secret']));

        $result = $api->sendMime($mime, $accessToken);

        if(is_wp_error($result)) {
            $errorMessage = $result->get_error_message();
            return new \WP_Error(422, $errorMessage, []);
        } else {
            return array(
                'RequestId' => $result['request-id'],
            );
        }

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
            if (!defined('FORMGLUTMAIL_OUTLOOK_CLIENT_ID') || !FORMGLUTMAIL_OUTLOOK_CLIENT_ID) {
                $errors['client_id']['required'] = __('Please define FORMGLUTMAIL_OUTLOOK_CLIENT_ID in wp-config.php file.', 'formglut-smtp');
            } else {
                $clientId = FORMGLUTMAIL_OUTLOOK_CLIENT_ID;
            }

            if (!defined('FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET') || !FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET) {
                $errors['client_secret']['required'] = __('Please define FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET in wp-config.php file.', 'formglut-smtp');
            } else {
                $clientSecret = FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET;
            }
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }

        $accessToken = Arr::get($connection, 'access_token');
        $authToken = Arr::get($connection, 'auth_token');

        if (!$accessToken && $authToken) {
            $tokens = (new API($clientId, $clientSecret))->generateToken($authToken);
            if (is_wp_error($tokens)) {
                $errors['auth_token']['required'] = $tokens->get_error_message();
            } else {
                add_filter('formglutmail_saving_connection_data', function ($con, $provider) use ($connection, $tokens) {

                    if ($provider != 'outlook') {
                        return $con;
                    }

                    if (Arr::get($con, 'connection.sender_email') != $connection['sender_email']) {
                        return $con;
                    }

                    $con['connection']['refresh_token'] = $tokens['refresh_token'];
                    $con['connection']['access_token'] = $tokens['access_token'];
                    $con['connection']['auth_token'] = '';
                    $con['connection']['expire_stamp'] = time() + $tokens['expires_in'];

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

    private function saveNewTokens($existingData, $tokens)
    {
        if (empty($tokens['access_token']) || empty($tokens['refresh_token'])) {
            return false;
        }

        $senderEmail = $existingData['sender_email'];

        $existingData['access_token'] = $tokens['access_token'];
        $existingData['refresh_token'] = $tokens['refresh_token'];
        $existingData['expire_stamp'] = $tokens['expires_in'] + time();

        (new Settings())->updateConnection($senderEmail, $existingData);
        return formglutMailGetProvider($senderEmail, true); // we are clearing the static cache here
    }

    private function getAccessToken($config)
    {
        $accessToken = $config['access_token'];
        // check if expired or will be expired in 300 seconds
        if ( ($config['expire_stamp'] - 300) < time()) {
            $formglutAPi = (new API($config['client_id'], $config['client_secret']));

            $tokens = $formglutAPi->sendTokenRequest('refresh_token', [
                'refresh_token' => $config['refresh_token']
            ]);

            if(is_wp_error($tokens)) {
                return false;
            }

            $this->saveNewTokens($config, $tokens);

            $accessToken =  $tokens['access_token'];
        }

        return $accessToken;
    }

    public function getConnectionInfo($connection)
    {
        if (Arr::get($connection, 'key_store') == 'wp_config') {
            $connection['client_id'] = defined('FORMGLUTMAIL_OUTLOOK_CLIENT_ID') ? FORMGLUTMAIL_OUTLOOK_CLIENT_ID : '';
            $connection['client_secret'] = defined('FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET') ? FORMGLUTMAIL_OUTLOOK_CLIENT_SECRET : '';
        }

        $this->getAccessToken($connection);
        $info = formglutMailgetConnection($connection['sender_email']);
        $connection = $info->getSetting();

        $extraRow = [
            'title'   => __('Token Validity', 'formglut-smtp'),
            'content' => 'Valid (' . intval((($connection['expire_stamp'] - time()) / 60)) . 'm)'
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
