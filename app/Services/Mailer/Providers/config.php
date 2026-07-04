<?php

return [
    'connections' => [],
    'mappings'    => [],
    'providers'   => [
        'smtp'        => [
            'key'      => 'smtp',
            'title'    => __('SMTP server', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-smtp.svg'),
            'provider' => 'Smtp',
            'need_pro' => 'no',
            'is_smtp'  => true,
            'options'  => [
                'sender_name'      => '',
                'sender_email'     => '',
                'force_from_name'  => 'no',
                'force_from_email' => 'yes',
                'return_path'      => 'yes',
                'host'             => '',
                'port'             => '',
                'auth'             => 'yes',
                'username'         => '',
                'password'         => '',
                'auto_tls'         => 'yes',
                'encryption'       => 'none',
                'key_store'        => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/set-up-formglut-smtp-with-any-host-or-mailer/" target="_blank" rel="noopener">Read the documentation</a> for how to configure any SMTP with FormglutSMTP.'
        ],
        'ses'         => [
            'key'      => 'ses',
            'title'    => __('Amazon SES', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-aws-ses.svg'),
            'provider' => 'AmazonSes',
            'options'  => [
                'sender_name'      => '',
                'sender_email'     => '',
                'force_from_name'  => 'no',
                'force_from_email' => 'yes',
                'return_path'      => 'yes',
                'access_key'       => '',
                'secret_key'       => '',
                'region'           => 'us-east-1',
                'key_store'        => 'db'
            ],
            'regions'  => [
                'us-east-1'      => __('US East (N. Virginia)', 'formglut-smtp'),
                'us-east-2'      => __('US East (Ohio)', 'formglut-smtp'),
                'us-west-1'      => __('US West (N. California)', 'formglut-smtp'),
                'us-west-2'      => __('US West (Oregon)', 'formglut-smtp'),
                'ca-central-1'   => __('Canada (Central)', 'formglut-smtp'),
                'eu-west-1'      => __('EU (Ireland)', 'formglut-smtp'),
                'eu-west-2'      => __('EU (London)', 'formglut-smtp'),
                'eu-west-3'      => __('Europe (Paris)', 'formglut-smtp'),
                'eu-central-1'   => __('EU (Frankfurt)', 'formglut-smtp'),
                'eu-south-1'     => __('Europe (Milan)', 'formglut-smtp'),
                'eu-north-1'     => __('Europe (Stockholm)', 'formglut-smtp'),
                'ap-south-1'     => __('Asia Pacific (Mumbai)', 'formglut-smtp'),
                'ap-northeast-2' => __('Asia Pacific (Seoul)', 'formglut-smtp'),
                'ap-southeast-1' => __('Asia Pacific (Singapore)', 'formglut-smtp'),
                'ap-southeast-2' => __('Asia Pacific (Sydney)', 'formglut-smtp'),
                'ap-northeast-1' => __('Asia Pacific (Tokyo)', 'formglut-smtp'),
                'sa-east-1'      => __('South America (São Paulo)', 'formglut-smtp'),
                'me-south-1'     => __('Middle East (Bahrain)', 'formglut-smtp'),
                'us-gov-west-1'  => __('AWS GovCloud (US)', 'formglut-smtp'),
                'af-south-1'     => __('Africa (Cape Town)', 'formglut-smtp'),
                'cn-northwest-1' => __('China (Ningxia)', 'formglut-smtp')
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/set-up-amazon-ses-in-formglut-smtp/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Amazon SES with FormglutSMTP.'
        ],
        'mailgun'     => [
            'key'      => 'mailgun',
            'title'    => __('Mailgun', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-mailgun.svg'),
            'provider' => 'Mailgun',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'return_path'     => 'yes',
                'api_key'         => '',
                'domain_name'     => '',
                'key_store'       => 'db',
                'region'          => 'us'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/configure-mailgun-in-formglut-smtp-to-send-emails/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Mailgun with FormglutSMTP.'
        ],
        'sendgrid'    => [
            'key'      => 'sendgrid',
            'title'    => __('SendGrid', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-sendgrid.svg'),
            'provider' => 'SendGrid',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'api_key'         => '',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/set-up-the-sendgrid-driver-in-formglut-smtp/" target="_blank" rel="noopener">Read the documentation</a> for how to configure SendGrid with FormglutSMTP.'
        ],
        'sendinblue'  => [
            'key'      => 'sendinblue',
            'title'    => __('Sendinblue', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-sendinblue.svg'),
            'provider' => 'SendInBlue',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'api_key'         => '',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/setting-up-sendinblue-mailer-in-formglut-smtp/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Sendinblue with FormglutSMTP.'
        ],
        'sparkpost'   => [
            'key'      => 'sparkpost',
            'title'    => __('SparkPost', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-sparkpost.svg'),
            'provider' => 'SparkPost',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'api_key'         => '',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/configure-sparkpost-in-formglut-smtp-to-send-emails/" target="_blank" rel="noopener">Read the documentation</a> for how to configure SparkPost with FormglutSMTP.'
        ],
        'pepipost'    => [
            'key'      => 'pepipost',
            'title'    => __('Netcore Email API, formerly Pepipost', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-netcore.svg'),
            'provider' => 'PepiPost',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'api_key'         => '',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/set-up-the-pepipost-mailer-in-formglut-smtp/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Pepipost with FormglutSMTP.'
        ],
        'postmark'    => [
            'key'      => 'postmark',
            'title'    => __('Postmark', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-postmark.svg'),
            'provider' => 'Postmark',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'track_opens'     => 'no',
                'track_links'     => 'no',
                'api_key'         => '',
                'message_stream'  => 'outbound',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/configure-postmark-in-formglut-smtp-to-send-emails/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Postmark with FormglutSMTP.'
        ],
        'elasticmail' => [
            'key'      => 'elasticmail',
            'title'    => __('Elastic Email', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-elastic-email.svg'),
            'provider' => 'ElasticMail',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'api_key'         => '',
                'mail_type'       => 'transactional',
                'key_store'       => 'db'
            ],
            'note'     => '<a href="https://formglutsmtp.com/docs/configure-elastic-email-in-formglut-smtp/" target="_blank" rel="noopener">Read the documentation</a> for how to configure Elastic Email with FormglutSMTP.'
        ],
        'gmail'       => [
            'key'      => 'gmail',
            'title'    => __('Gmail or Google Workspace', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-gmail-google-workspace.svg'),
            'provider' => 'Gmail',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'return_path'     => 'yes',
                'key_store'       => 'db',
                'client_id'       => '',
                'client_secret'   => '',
                'auth_token'      => '',
                'access_token'    => '',
                'refresh_token'   => ''
            ],
            'note'     => __('Gmail/Google Workspace is not recommended for sending mass marketing emails.', 'formglut-smtp')
        ],
        'outlook'     => [
            'key'      => 'outlook',
            'title'    => __('Outlook or Office 365', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-microsoft.svg'),
            'provider' => 'Outlook',
            'options'  => [
                'sender_name'     => '',
                'sender_email'    => '',
                'force_from_name' => 'no',
                'return_path'     => 'yes',
                'key_store'       => 'db',
                'client_id'       => '',
                'client_secret'   => '',
                'auth_token'      => '',
                'access_token'    => '',
                'refresh_token'   => ''
            ],
            'note'     => __('Outlook/Office365 is not recommended for sending mass marketing emails.', 'formglut-smtp')
        ],
        'default'     => [
            'key'      => 'default',
            'title'    => __('PHP mail()', 'formglut-smtp'),
            'image'    => formglutMailAssetUrl('images/provider-php.svg'),
            'provider' => 'DefaultMail',
            'options'  => [
                'sender_name'      => '',
                'sender_email'     => '',
                'force_from_name'  => 'no',
                'force_from_email' => 'yes',
                'return_path'      => 'yes',
                'key_store'        => 'db'
            ],
            'note'     => __('The Default option does not use SMTP or any Email Service Providers so it will not improve email delivery on your site.', 'formglut-smtp')
        ],
    ],
    'misc'        => [
        'log_emails'              => 'yes',
        'log_saved_interval_days' => '14',
        'disable_formglutcrm_logs'  => 'no',
        'default_connection'      => '',
        'fallback_connection'     => ''
    ]
];
