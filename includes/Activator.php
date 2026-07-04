<?php

namespace FormglutMail\Includes;

class Activator
{
    public static function handle($network_wide = false)
    {
        require_once(FORMGLUTMAIL_PLUGIN_PATH . 'database/FormglutMailDBMigrator.php');

        $emailReportHookName = 'formglutmail_do_daily_scheduled_tasks';
        if (!wp_next_scheduled($emailReportHookName)) {
            wp_schedule_event(time(), 'daily', $emailReportHookName);
        }
        
        add_filter('pre_update_option_active_plugins', function ($plugins) {
            $index = array_search('formglut-smtp/formglut-smtp.php', $plugins);
            if ($index !== false) {
                if ($index === 0) {
                    return $plugins;
                }
                unset($plugins[$index]);
                array_unshift($plugins, 'formglut-smtp/formglut-smtp.php');
            }
            return $plugins;
        });
    }
}
