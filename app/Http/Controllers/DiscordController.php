<?php

namespace FormglutMail\App\Http\Controllers;

use FormglutMail\App\Models\Settings;
use FormglutMail\App\Services\NotificationHelper;
use FormglutMail\Includes\Request\Request;
use FormglutMail\Includes\Support\Arr;

class DiscordController extends Controller
{
    public function registerSite(Request $request)
    {
        $this->verify();

        $formData = $request->get('settings', []);

        if (empty($formData['webhook_url'])) {
            return $this->sendError([
                'message' => __('Webhook URL is required', 'formglut-smtp')
            ], 422);
        }

        if (empty($formData['channel_name'])) {
            return $this->sendError([
                'message' => __('Channel Name required', 'formglut-smtp')
            ], 422);
        }


        $prevSettings = (new Settings())->notificationSettings();

        $prevSettings['discord'] = [
            'status'       => 'yes',
            'channel_name' => Arr::get($formData, 'channel_name'),
            'webhook_url'  => Arr::get($formData, 'webhook_url'),
        ];

        $prevSettings['active_channel'] = 'discord';

        update_option('_formglut_smtp_notify_settings', $prevSettings);

        return $this->sendSuccess([
            'message' => __('Your settings has been saved', 'formglut-smtp'),
        ]);
    }

    public function sendTestMessage(Request $request)
    {
        // Let's update the notification status
        $settings = (new Settings())->notificationSettings();

        if (Arr::get($settings, 'discord.status') != 'yes') {
            return $this->sendError([
                'message' => __('Slack notification is not enabled', 'formglut-smtp')
            ], 422);
        }

        $message = 'This is a test message for ' . site_url() . '. If you get this message, then your site is connected successfully.';

        $result = NotificationHelper::sendDiscordMessage($message, Arr::get($settings, 'discord.webhook_url'));

        if (is_wp_error($result)) {
            return $this->sendError([
                'message' => $result->get_error_message(),
                'errors'  => $result->get_error_data(),
            ], 422);
        }

        return $this->sendSuccess([
            'message'         => __('Test message sent successfully', 'formglut-smtp'),
            'server_response' => $result
        ]);
    }

    public function disconnect()
    {
        $settings = (new Settings())->notificationSettings();

        $settings['discord'] = [
            'status'       => 'no',
            'webhook_url'  => '',
            'channel_name' => ''
        ];

        $settings['active_channel'] = '';

        update_option('_formglut_smtp_notify_settings', $settings);

        return $this->sendSuccess([
            'message' => __('Discord connection has been disconnected successfully', 'formglut-smtp')
        ]);
    }

}
