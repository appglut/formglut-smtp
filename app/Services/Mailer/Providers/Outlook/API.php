<?php

namespace FormglutMail\App\Services\Mailer\Providers\Outlook;

use FormglutMail\Includes\Support\Arr;

class API
{
    private $clientId;
    private $clientSecret;

    public function __construct($clientId = '', $clientSecret = '')
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAuthUrl()
    {

        $formglutClient = new \FormglutMail\Includes\OAuth2Provider($this->getConfig());

        return $formglutClient->getAuthorizationUrl();

    }

    public function generateToken($authCode)
    {
        return $this->sendTokenRequest('authorization_code', [
            'code' => $authCode
        ]);
    }

    /**
     * @return mixed|string
     */
    public function sendTokenRequest($type, $params)
    {
        $formglutClient = new \FormglutMail\Includes\OAuth2Provider($this->getConfig());
        try {
            $tokens = $formglutClient->getAccessToken($type, $params);
            return $tokens;
        } catch (\Exception$exception) {
            return new \WP_Error(422, $exception->getMessage());
        }
    }

    /**
     * @return array | \WP_Error
     */
    public function sendMime($mime, $accessToken)
    {
        $response = wp_remote_request('https://graph.microsoft.com/v1.0/me/sendMail', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'text/plain'
            ],
            'body'    => $mime
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $responseCode = wp_remote_retrieve_response_code($response);

        if ($responseCode >= 300) {
            $error = Arr::get($response, 'response.message');

            if (!$error) {
                $responseBody = json_decode(wp_remote_retrieve_body($response), true);

                $error = Arr::get($responseBody, 'error.message');
                if (!$error) {
                    $error = 'Something with wrong with Outlook API. Please check your API Settings';
                }
            }

            return new \WP_Error($responseCode, $error);
        }

        $header = wp_remote_retrieve_headers($response);

        return $header->getAll();
    }

    public function getRedirectUrl()
    {
        return rest_url('formglut-smtp/outlook_callback');
    }

    private function getConfig()
    {
        return [
            'clientId'                => $this->clientId,
            'clientSecret'            => $this->clientSecret,
            'redirectUri'             => $this->getRedirectUrl(),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'https://graph.microsoft.com/user.read https://graph.microsoft.com/mail.readwrite https://graph.microsoft.com/mail.send https://graph.microsoft.com/mail.send.shared offline_access'
        ];
    }

}
