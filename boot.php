<?php

!defined('WPINC') && die;

define('FORMGLUTMAIL', 'formglutmail');
define('FORMGLUTMAIL_PLUGIN_VERSION', '2.2.73');
define('FORMGLUTMAIL_UPLOAD_DIR', '/formglutmail');
define('FORMGLUT_MAIL_DB_PREFIX', 'fsmpt_');
define('FORMGLUTMAIL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMGLUTMAIL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

spl_autoload_register(function($class) {
    $match = 'FormglutMail';

    if (!preg_match("/\b{$match}\b/", $class)) {
        return;
    }

    $path = plugin_dir_path(__FILE__);
    
    $file = str_replace(
        ['FormglutMail', '\\', '/App/', '/Includes/'],
        ['', DIRECTORY_SEPARATOR, 'app/', 'includes/'],
        $class
    );

    require(trailingslashit($path) . trim($file, '/') . '.php');
});

