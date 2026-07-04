<?php

namespace FormglutMail\App\Http\Controllers;

use FormglutMail\App\Models\Settings;
use FormglutMail\App\Services\NotificationHelper;
use FormglutMail\Includes\Request\Request;
use FormglutMail\Includes\Support\Arr;

class SlackController extends Controller
{
    public function registerSite(Request $request)
    {
        $this->verify();

        $formData = $request->get('settings', []);

        $userEmail = sanitize_email(Arr::get($formData, 'user_email'));

        if (!is_email($userEmail)) {
            return $this->sendError([
                'message' => __('Please provide a valid email address', 'formglut-mail')
            ], 422);
        }

        $payload = [
            'admin_email' => $userEmail,
            'smtp_url'    => admin_url('admin.php?page=formglut_forms_smtp'),
            'site_url'    => site_url(),
            'site_title'  => get_bloginfo('name'),
            'site_lang'   => get_bloginfo('language'),
        ];


        $activationData = NotificationHelper::registerSlackSite($payload);

        if (is_wp_error($activationData)) {
            return $this->sendError([
                'message' => $activationData->get_error_message(),
                'errors'  => $activationData->get_error_data(),
            ], 422);
        }

        $prevSettings = (new Settings())->notificationSettings();


        $prevSettings['slack'] = [
            'status'       => 'pending',
            'token'        => Arr::get($activationData, 'site_token'),
            'redirect_url' => ''
        ];

        update_option('_formglut_smtp_notify_settings', $prevSettings);

        return $this->sendSuccess([
            'message'      => __('Awesome! You are redirecting to slack', 'formglut-smtp'),
            'redirect_url' => Arr::get($activationData, 'redirect_url')
        ]);
    }

    public function sendTestMessage(Request $request)
    {
        // Let's update the notification status
        $settings = (new Settings())->notificationSettings();

        if (Arr::get($settings, 'slack.status') != 'yes') {
            return $this->sendError([
                'message' => __('Slack notification is not enabled', 'formglut-smtp')
            ], 422);
        }

        $message = 'This is a test message for ' . site_url() . '. If you get this message, then your site is connected successfully.';

        $result = NotificationHelper::sendSlackMessage($message, Arr::get($settings, 'slack.webhook_url'));

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message(),
                'errors'  => $result->get_error_data(),
            ], 422);
        }

        return $this->sendSuccess([
            'message' => __('Test message sent successfully', 'formglut-smtp')
        ]);
    }

    public function disconnect()
    {
        $settings = (new Settings())->notificationSettings();

        $settings['slack'] = [
            'status'      => 'no',
            'webhook_url' => '',
            'token'       => ''
        ];

        update_option('_formglut_smtp_notify_settings', $settings);

        return $this->sendSuccess([
            'message' => __('Slack connection has been disconnected successfully', 'formglut-smtp')
        ]);
    }

}
