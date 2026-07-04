<?php

namespace FormglutMail\App\Services\Mailer\Providers\PepiPost;

use FormglutMail\Includes\Support\Arr;
use FormglutMail\App\Services\Mailer\ValidatorTrait as BaseValidatorTrait;

trait ValidatorTrait
{
    use BaseValidatorTrait;

    public function validateProviderInformation($connection)
    {
        $errors = [];

        $keyStoreType = $connection['key_store'];

        if($keyStoreType == 'db') {
            if (! Arr::get($connection, 'api_key')) {
                $errors['api_key']['required'] = __('Api key is required.', 'formglut-smtp');
            }
        } else if($keyStoreType == 'wp_config') {
            if(!defined('FORMGLUTMAIL_PEPIPOST_API_KEY') || !FORMGLUTMAIL_PEPIPOST_API_KEY) {
                $errors['api_key']['required'] = __('Please define FORMGLUTMAIL_PEPIPOST_API_KEY in wp-config.php file.', 'formglut-smtp');
            }
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }
    }
}
