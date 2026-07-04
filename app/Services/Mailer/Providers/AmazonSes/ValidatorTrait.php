<?php

namespace FormglutMail\App\Services\Mailer\Providers\AmazonSes;

use FormglutMail\Includes\Support\Arr;
use FormglutMail\App\Services\Mailer\ValidatorTrait as BaseValidatorTrait;

trait ValidatorTrait
{
    use BaseValidatorTrait;

    public function validateProviderInformation($connection)
    {
        $errors = [];

        $keyStoreType = $connection['key_store'];

        if ($keyStoreType == 'db') {
            if (!Arr::get($connection, 'access_key')) {
                $errors['access_key']['required'] = __('Access key is required.', 'formglut-smtp');
            }

            if (!Arr::get($connection, 'secret_key')) {
                $errors['secret_key']['required'] = __('Secret key is required.', 'formglut-smtp');
            }
        } else if ($keyStoreType == 'wp_config') {
            if (!defined('FORMGLUTMAIL_AWS_ACCESS_KEY_ID') || !FORMGLUTMAIL_AWS_ACCESS_KEY_ID) {
                $errors['access_key']['required'] = __('Please define FORMGLUTMAIL_AWS_ACCESS_KEY_ID in wp-config.php file.', 'formglut-smtp');
            }

            if (!defined('FORMGLUTMAIL_AWS_SECRET_ACCESS_KEY') || !FORMGLUTMAIL_AWS_SECRET_ACCESS_KEY) {
                $errors['secret_key']['required'] = __('Please define FORMGLUTMAIL_AWS_SECRET_ACCESS_KEY in wp-config.php file.', 'formglut-smtp');
            }
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }
    }

    public function checkConnection($connection)
    {
        $connection = $this->filterConnectionVars($connection);
        $region = 'email.' . $connection['region'] . '.amazonaws.com';

        $ses = new SimpleEmailService(
            $connection['access_key'],
            $connection['secret_key'],
            $region,
            true
        );

        $lists = $ses->listVerifiedEmailAddresses();

        if (is_wp_error($lists)) {
            $this->throwValidationException(['api_error' => $lists->get_error_message()]);
        }

        return true;
    }
}
