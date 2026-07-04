<?php

namespace FormglutMail\App\Hooks\Handlers;

class InitializeSiteHandler
{
    public function addHandler()
    {
        add_action('wp_initialize_site', array($this, 'handle'));
    }

    public static function handle( $new_site )
    {
        require_once(FORMGLUTMAIL_PLUGIN_PATH . 'database/migrations/EmailLogs.php');
        
        $blog_id = $new_site->blog_id;
        switch_to_blog((int)$blog_id);
        \FormglutMailMigrations\EmailLogs::migrate();
        restore_current_blog();
    }
}
