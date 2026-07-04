<?php
/*
Plugin Name:  FormGlut SMTP
Plugin URI:   https://appglut.com/formglut
Description:  The Ultimate SMTP Connection Plugin for WordPress.
Version:      1.0.0
Author:       Appglut
Author URI:   https://appglut.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  formglut-smtp
Domain Path:  /language
*/

!defined('WPINC') && die;

define('FORMGLUT_SMTP_VERSION', '1.0.0');
define('FORMGLUTMAIL_PLUGIN_FILE', __FILE__);

require_once(plugin_dir_path(__FILE__) . 'boot.php');

register_activation_hook(
    __FILE__, array('\FormglutMail\Includes\Activator', 'handle')
);

register_deactivation_hook(
    __FILE__, array('\FormglutMail\Includes\Deactivator', 'handle')
);

function formglutSmtpInit()
{
    $application = new FormglutMail\Includes\Core\Application;
    add_action('plugins_loaded', function () use ($application) {
        do_action('formglutMail_loaded', $application);
    });
}

formglutSmtpInit();

if (!function_exists('wp_mail')) :
    function formglut_wp_mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        return formglutMailSend($to, $subject, $message, $headers, $attachments);
    }
else:
    if (!(defined('DOING_AJAX') && DOING_AJAX)):
        add_action('init', 'formglutMailFuncCouldNotBeLoadedRecheckPluginsLoad');
    endif;
endif;

/*
 * Thanks for checking the source code
 * Please check the full source here: https://github.com/Appglut/formglut-smtp
 * Would love to welcome your pull request
*/
