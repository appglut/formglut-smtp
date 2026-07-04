<?php

namespace FormglutMail\Includes;

class Deactivator
{
    public static function handle($network_wide = false)
    {
        wp_clear_scheduled_hook('formglutmail_do_daily_scheduled_tasks');
    }
}
