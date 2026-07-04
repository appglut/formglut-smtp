<?php

namespace FormglutMail\App\Services\Mailer;

use FormglutMail\App\Models\Settings;
use FormglutMail\Includes\Support\Arr;
use FormglutMail\Includes\Support\ValidationException;

trait ValidatorTrait
{
    public function validateBasicInformation($connection)
    {
        $errors = [];

        if (!($email = Arr::get($connection, 'sender_email'))) {
            $errors['sender_email']['required'] = __('Sender email is required.', 'formglut-smtp');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['sender_email']['email'] = __('Invalid email address.', 'formglut-smtp');
        }

        if ($errors) {
            $this->throwValidationException($errors);
        }
    }

    public function validateProviderInformation($inputs)
    {
        // Required Method
    }

    public function throwValidationException($errors)
    {
        throw new ValidationException(
            'Unprocessable Entity', 422, null, $errors
        );
    }
}
