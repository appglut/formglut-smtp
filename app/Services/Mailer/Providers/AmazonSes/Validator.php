<?php

namespace FormglutMail\App\Services\Mailer\Providers\AmazonSes;

use FormglutMail\App\Models\Settings;
use FormglutMail\Includes\Support\Arr;
use FormglutMail\Includes\Support\ValidationException;
use FormglutMail\App\Services\Mailer\Providers\AmazonSes\SimpleEmailService;

class Validator
{
    protected $errors = null;

    protected $provider = null;

    public function __construct($provider, $errors)
    {
        $this->errors = $errors;
        $this->provider = $provider;
    }

    public function validate()
    {

        $data = formglutMail('request')->except(['action', 'nonce']);

        $inputs = Arr::only(
            $data['provider']['options'], ['access_key', 'secret_key', 'region']
        );

        $ses = new SimpleEmailService(
            $inputs['access_key'],
            $inputs['secret_key'],
            'email.' . $inputs['region'] . '.amazonaws.com',
            false
        );

        $result = $ses->listVerifiedEmailAddresses();

        if (is_wp_error($result)) {
            throw new ValidationException($result->get_error_message(), 400);
        }

        if ($result) {
            $senderEmail = Arr::get(
                $data, 'provider.options.sender_email'
            );

            if (!in_array($senderEmail, $result['Addresses'])) {
                throw new \Exception('The from email is not verified', 400);
            }

            formglutMail(Settings::class)->saveVerifiedEmails($result['Addresses']);
        }
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (isset($errcontext['e'])) {
            $message = $errcontext['e']['Message'];
        } else {
            $message = $errcontext['error']['message'];
        }

        $newException = new ValidationException(
            '', $errno, null, array_merge(
                $this->errors, [
                    'sender_email' => [
                        $errcontext['functionname'] => $message
                    ]
                ]
            )
        );

        restore_error_handler();

        throw $newException;
    }
}
