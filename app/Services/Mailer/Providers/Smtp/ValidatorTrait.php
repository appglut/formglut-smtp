<?php

namespace FormglutMail\App\Services\Mailer\Providers\Smtp;

use FormglutMail\Includes\Support\Arr;
use FormglutMail\App\Services\Mailer\ValidatorTrait as BaseValidatorTrait;

trait ValidatorTrait
{
    use BaseValidatorTrait;

    public function validateProviderInformation($connection)
    {
        $errors = [];

        $keyStoreType = Arr::get($connection, 'key_store', 'db');

        if (!Arr::get($connection, 'host')) {
            $errors['host']['required'] = __('SMTP host is required.', 'formglut-smtp');
        }

        if (!Arr::get($connection, 'port')) {
            $errors['port']['required'] = __('SMTP port is required.', 'formglut-smtp');
        }

        if (Arr::get($connection, 'auth') == 'yes') {
            if ($keyStoreType == 'wp_config') {
                if (!defined('FORMGLUTMAIL_SMTP_USERNAME') || !FORMGLUTMAIL_SMTP_USERNAME) {
                    $errors['username']['required'] = __('Please define FORMGLUTMAIL_SMTP_USERNAME in wp-config.php file.', 'formglut-smtp');
                }

                if (!defined('FORMGLUTMAIL_SMTP_PASSWORD') || !FORMGLUTMAIL_SMTP_PASSWORD) {
                    $errors['password']['required'] = __('Please define FORMGLUTMAIL_SMTP_PASSWORD in wp-config.php file.', 'formglut-smtp');
                }
            } else {
                if (!Arr::get($connection, 'username')) {
                    $errors['username']['required'] = __('SMTP username is required.', 'formglut-smtp');
                }

                if (!Arr::get($connection, 'password')) {
                    $errors['password']['required'] = __('SMTP password is required.', 'formglut-smtp');
                }
            }
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }
    }
}
