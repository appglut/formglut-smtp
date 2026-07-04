<?php

$singletons = [
    'manager'     => 'FormglutMail\App\Services\Mailer\Manager',
    'smtp'        => 'FormglutMail\App\Services\Mailer\Providers\Smtp\Handler',
    'ses'         => 'FormglutMail\App\Services\Mailer\Providers\AmazonSes\Handler',
    'mailgun'     => 'FormglutMail\App\Services\Mailer\Providers\Mailgun\Handler',
    'sendgrid'    => 'FormglutMail\App\Services\Mailer\Providers\SendGrid\Handler',
    'pepipost'    => 'FormglutMail\App\Services\Mailer\Providers\PepiPost\Handler',
    'sparkpost'   => 'FormglutMail\App\Services\Mailer\Providers\SparkPost\Handler',
    'default'     => 'FormglutMail\App\Services\Mailer\Providers\DefaultMail\Handler',
    'sendinblue'  => 'FormglutMail\App\Services\Mailer\Providers\SendInBlue\Handler',
    'gmail'       => 'FormglutMail\App\Services\Mailer\Providers\Gmail\Handler',
    'outlook'     => 'FormglutMail\App\Services\Mailer\Providers\Outlook\Handler',
    'postmark'    => 'FormglutMail\App\Services\Mailer\Providers\Postmark\Handler',
    'elasticmail' => 'FormglutMail\App\Services\Mailer\Providers\ElasticMail\Handler'
];

foreach ($singletons as $key => $className) {
    $app->alias($className, $key);
    $app->singleton($className, function($app) use ($className) {
        return new $className();
    });
}
