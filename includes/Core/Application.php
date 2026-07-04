<?php

namespace FormglutMail\Includes\Core;

use ArrayAccess;
use FormglutMail\Includes\View\View;
use FormglutMail\Includes\Core\CoreTrait;
use FormglutMail\Includes\Core\Container;
use FormglutMail\Includes\Request\Request;
use FormglutMail\Includes\Response\Response;

final class Application extends Container
{
    use CoreTrait;

    private $policyNamespace = 'FormglutMail\App\Http\Policies';

    private $handlerNamespace = 'FormglutMail\App\Hooks\Handlers';

    private $controllerNamespace = 'FormglutMail\App\Http\Controllers';

    public function __construct()
    {
        $this->setApplicationInstance();
        $this->registerPluginPathsAndUrls();
        $this->registerFrameworkComponents();
        $this->requireCommonFilesForRequest($this);

        load_plugin_textdomain('formglut-smtp', false, 'formglut-smtp/language/');

        /*
         * We are adding formglut-smtp/formglut-smtp.php at the top to load the wp_mail at the very first
         * There has no other way to load a specific plugin at the first.
         */
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

        add_action('admin_notices', function () {
            if (!current_user_can('manage_options')) {
                return;
            }

            $settings = get_option('formglutmail-settings');

            if (!$settings || empty($settings['use_encrypt']) || empty($settings['test'])) {
                return;
            }

            $testData = formglutMailEncryptDecrypt($settings['test'], 'd');

            if ($testData == 'test') {
                return;
            }

            ?>
            <div class="notice notice-warning formglutsmtp_urgent is-dismissible">
                <p>
                    <?php
                    echo sprintf(
                        __('FormglutSMTP Plugin may not work properly. Looks like your Authentication unique keys and salts are changed.
                                <a href="%1s"><b>Reconfigure SMTP Settings</b></a>',
                            'formglut-smtp'), admin_url('admin.php?page=formglut_forms_smtp/connections')
                    );
                    ?>
                </p>
            </div>
            <?php
        });
    }

    private function setApplicationInstance()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(__CLASS__, $this);
    }
    private function registerPluginPathsAndUrls()
    {
        // Paths
        $this['path'] = FORMGLUTMAIL_PLUGIN_PATH;
        $this['path.app'] = FORMGLUTMAIL_PLUGIN_PATH . 'app/';
        $this['path.hooks'] = FORMGLUTMAIL_PLUGIN_PATH . 'app/Hooks/';
        $this['path.models'] = FORMGLUTMAIL_PLUGIN_PATH . 'app/models/';
        $this['path.includes'] = FORMGLUTMAIL_PLUGIN_PATH . 'includes/';
        $this['path.controllers'] = FORMGLUTMAIL_PLUGIN_PATH . 'app/Http/controllers/';
        $this['path.views'] = FORMGLUTMAIL_PLUGIN_PATH . 'app/views/';
        $this['path.admin.css'] = FORMGLUTMAIL_PLUGIN_PATH . 'assets/admin/css/';
        $this['path.admin.js'] = FORMGLUTMAIL_PLUGIN_PATH . 'assets/admin/js/';
        $this['path.public.css'] = FORMGLUTMAIL_PLUGIN_PATH . 'assets/public/css/';
        $this['path.public.js'] = FORMGLUTMAIL_PLUGIN_PATH . 'assets/public/js/';
        $this['path.assets'] = FORMGLUTMAIL_PLUGIN_PATH . 'assets/';

        // Urls
        $this['url'] = FORMGLUTMAIL_PLUGIN_URL;
        $this['url.app'] = FORMGLUTMAIL_PLUGIN_URL . 'app/';
        $this['url.assets'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/';
        $this['url.public.css'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/public/css/';
        $this['url.admin.css'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/admin/css/';
        $this['url.public.js'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/public/js/';
        $this['url.admin.js'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/admin/js/';
        $this['url.assets.images'] = FORMGLUTMAIL_PLUGIN_URL . 'assets/images/';

    }

    private function registerFrameworkComponents()
    {
        $this->bind('FormglutMail\Includes\View\View', function ($app) {
            return new View($app);
        });

        $this->alias('FormglutMail\Includes\View\View', 'view');

        $this->singleton('FormglutMail\Includes\Request\Request', function ($app) {
            return new Request($app, $_GET, $_POST, $_FILES);
        });

        $this->alias('FormglutMail\Includes\Request\Request', 'request');

        $this->singleton('FormglutMail\Includes\Response\Response', function ($app) {
            return new Response($app);
        });

        $this->alias('FormglutMail\Includes\Response\Response', 'response');
    }

    /**
     * Require all the common files that needs to be loaded on each request
     *
     * @param Application $app [$app is being used inside required files]
     * @return void
     */
    private function requireCommonFilesForRequest($app)
    {
        // Require Application Bindings
        require_once($app['path.app'] . '/Bindings.php');

        // Require Global Functions
        require_once($app['path.app'] . '/Functions/helpers.php');

        // Require Action Hooks
        require_once($app['path.app'] . '/Hooks/actions.php');

        // Require Filter Hooks
        require_once($app['path.app'] . '/Hooks/filters.php');

        // Require Routes
        if (is_admin()) {
            require_once($app['path.app'] . '/Http/routes.php');
        }
    }
}
