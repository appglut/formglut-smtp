<?php

namespace FormglutMail\App\Hooks\Handlers;

use FormglutMail\App\Models\Logger;
use FormglutMail\App\Models\Settings;
use FormglutMail\App\Services\Converter;
use FormglutMail\Includes\Core\Application;
use FormglutMail\App\Services\Mailer\Manager;
use FormglutMail\Includes\Support\Arr;

class AdminMenuHandler
{
    protected $app = null;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function addFormglutMailMenu()
    {
        add_action('admin_menu', array($this, 'addMenu'), 11 );

        if (isset($_GET['page']) && $_GET['page'] == 'formglut_forms_smtp' && is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));

            if (isset($_REQUEST['sub_action']) && $_REQUEST['sub_action'] == 'slack_success') {
                add_action('admin_init', function () {
                    $settings = (new Settings())->notificationSettings();
                    $token = Arr::get($_REQUEST, 'site_token');

                    if ($token == Arr::get($settings, 'slack.token')) {
                        $settings['slack'] = [
                            'status'      => 'yes',
                            'token'       => sanitize_text_field($token),
                            'slack_team'  => sanitize_text_field(Arr::get($_REQUEST, 'slack_team')),
                            'webhook_url' => sanitize_url(Arr::get($_REQUEST, 'slack_webhook'))
                        ];

                        $settings['active_channel'] = 'slack';

                        update_option('_formglut_smtp_notify_settings', $settings);
                    }

                    wp_redirect(admin_url('admin.php?page=formglut_forms_smtp/notification-settings'));
                    die();
                });
            }

        }

        add_action('admin_bar_menu', array($this, 'addSimulationBar'), 999);

        add_action('admin_init', array($this, 'initAdminWidget'));

        add_action('install_plugins_table_header', function () {
            if (!isset($_REQUEST['s']) || empty($_REQUEST['s']) || empty($_REQUEST['tab']) || $_REQUEST['tab'] != 'search') {
                return;
            }

            $search = str_replace(['%20', '_', '-'], ' ', $_REQUEST['s']);
            $search = trim(strtolower(sanitize_text_field($search)));

            $searchTerms = ['wp-mail-smtp', 'wp mail', 'wp mail smtp', 'post mailer', 'wp smtp', 'smtp mail', 'smtp', 'post smtp', 'easy smtp', 'easy wp smtp', 'smtp mailer', 'gmail smtp', 'offload ses'];

            if (!strpos($search, 'smtp')) {
                if (!in_array($search, $searchTerms)) {
                    return;
                }
            }
            ?>
            <div
                style="background-color: #fff;border: 1px solid #dcdcde;box-sizing: border-box;padding: 20px;margin: 15px 0;"
                class="formglut_smtp_box">
                <h3 style="margin: 0;">For SMTP, you already have FormglutSMTP Installed</h3>
                <p>You seem to be looking for an SMTP plugin, but there's no need for another one — FormglutSMTP is
                    already installed on your site. FormglutSMTP is a comprehensive, free, and open-source plugin with
                    full features available without any upsell (<a
                        href="https://formglutsmtp.com/why-we-built-formglutsmtp-plugin/">learn why it's free</a>). It's
                    compatible with various SMTP services, including Amazon SES, SendGrid, MailGun, ElasticEmail,
                    SendInBlue, Google, Microsoft, and others, providing you with a wide range of options for your email
                    needs.</p>
                <a href="<?php echo admin_url('admin.php?page=formglut_forms_smtp'); ?>"
                   class="wp-core-ui button button-primary">Go To FormglutSMTP Settings</a>
                <p style="font-size: 80%; margin: 15px 0 0;">This notice is from FormglutSMTP plugin to prevent plugin
                    conflict.</p>
            </div>
            <?php
        }, 1);

        add_action('wp_ajax_formglut_smtp_get_dashboard_html', function () {
            // This widget should be displayed for certain high-level users only.
            if (!current_user_can('manage_options') || apply_filters('formglut_mail_disable_dashboard_widget', false)) {
                wp_send_json([
                    'html' => 'You do not have permission to see this data'
                ]);
            }

            wp_send_json([
                'html' => $this->getDashboardWidgetHtml()
            ]);
        });

    }

    public function addMenu()
    {
        $title = $this->app->applyCustomFilters('admin-menu-title', __('SMTP', 'formglut-smtp'));

        add_submenu_page(
            'formglut_forms',
            $title,
            $title,
            'manage_options',
            'formglut_forms_smtp',
            [$this, 'renderApp'],
            6
        );


    }


    public function renderApp()
    {
        $dailyTaskHookName = 'formglutmail_do_daily_scheduled_tasks';

        if (!wp_next_scheduled($dailyTaskHookName)) {
            wp_schedule_event(time(), 'daily', $dailyTaskHookName);
        }

        $this->app->view->render('admin.menu');
    }

    public function enqueueAssets()
    {
        add_action('wp_print_scripts', function () {
            $isSkip = apply_filters('formglutsmtp_skip_no_conflict', false);

            if ($isSkip) {
                return;
            }

            global $wp_scripts;
            if (!$wp_scripts) {
                return;
            }

            $themeUrl = content_url('themes');
            $pluginUrl = plugins_url();
            foreach ($wp_scripts->queue as $script) {
                if (empty($wp_scripts->registered[$script]) || empty($wp_scripts->registered[$script]->src)) {
                    continue;
                }

                $src = $wp_scripts->registered[$script]->src;
                $isMatched = strpos($src, $pluginUrl) !== false && !strpos($src, 'formglut-smtp') !== false;
                if (!$isMatched) {
                    continue;
                }

                $isMatched = strpos($src, $themeUrl) !== false;

                if ($isMatched) {
                    wp_dequeue_script($wp_scripts->registered[$script]->handle);
                }
            }

        }, 1);

        wp_enqueue_script(
            'formglut_mail_admin_app_boot',
            formglutMailMix('admin/js/boot.js'),
            ['jquery'],
            FORMGLUTMAIL_PLUGIN_VERSION
        );

        wp_enqueue_script('formglutmail-chartjs', formglutMailMix('libs/chartjs/Chart.min.js'), [], FORMGLUTMAIL_PLUGIN_VERSION);
        wp_enqueue_script('formglutmail-vue-chartjs', formglutMailMix('libs/chartjs/vue-chartjs.min.js'), [], FORMGLUTMAIL_PLUGIN_VERSION);
        wp_enqueue_script('dompurify', formglutMailMix('libs/purify/purify.min.js'), [], '2.4.3');

        wp_enqueue_style(
            'formglut_mail_admin_app', formglutMailMix('admin/css/formglut-mail-admin.css'), [], FORMGLUTMAIL_PLUGIN_VERSION
        );

        $user = get_user_by('ID', get_current_user_id());

        $disable_recommendation = defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS;

        $settings = $this->getMailerSettings();

        $recommendedSettings = false;
        if (empty($settings['connections'])) {
            $recommendedSettings = (new Converter())->getSuggestedConnection();
        }

        $displayName = trim($user->first_name . ' ' . $user->last_name);
        if (!$displayName) {
            $displayName = $user->display_name;
        }

        wp_localize_script('formglut_mail_admin_app_boot', 'FormglutMailAdmin', [
            'adminUrl'               => admin_url('admin.php?page=formglut_forms_smtp'),
            'slug'                   => FORMGLUTMAIL,
            'brand_logo'             => esc_url(formglutMailMix('images/logo.svg')),
            'nonce'                  => wp_create_nonce(FORMGLUTMAIL),
            'settings'               => $settings,
            'images_url'             => esc_url(formglutMailMix('images/')),
            'has_formglutcrm'          => defined('FORMGLUTCRM'),
            'has_formglutform'         => defined('FORMGLUTFORM'),
            'user_email'             => $user->user_email,
            'user_display_name'      => $displayName,
            'require_optin'          => $this->isRequireOptin(),
            'has_ninja_tables'       => defined('NINJA_TABLES_VERSION'),
            'disable_recommendation' => apply_filters('formglutmail_disable_recommendation', false),
            'disable_installation'   => $disable_recommendation,
            'plugin_url'             => 'https://formglutsmtp.com/?utm_source=wp&utm_medium=install&utm_campaign=dashboard',
            'trans'                  => $this->getTrans(),
            'recommended'            => $recommendedSettings,
            'is_disabled_defined'    => defined('FORMGLUTMAIL_SIMULATE_EMAILS') && FORMGLUTMAIL_SIMULATE_EMAILS
        ]);

        do_action('formglut_mail_loading_app');

        wp_enqueue_script(
            'formglut_mail_admin_app',
            formglutMailMix('admin/js/formglut-mail-admin-app.js'),
            ['formglut_mail_admin_app_boot'],
            FORMGLUTMAIL_PLUGIN_VERSION,
            true
        );

        add_filter('admin_footer_text', function ($text) {
            return sprintf(
                __('<b>FormglutSMTP</b> is a free plugin & it will be always free %s. %s', 'formglut-smtp'),
                '<a href="https://formglutsmtp.com/why-we-built-formglutsmtp-plugin/" target="_blank" rel="noopener noreferrer">(Learn why it\'s free)</a>',
                '<a href="https://wordpress.org/support/plugin/formglut-smtp/reviews/?filter=5" target="_blank" rel="noopener noreferrer">Write a review ★★★★★</a>'
            );
        });
    }

    protected function getMailerSettings()
    {
        $settings = $this->app->make(Manager::class)->getMailerConfigAndSettings(true);

        if ($settings['mappings'] && $settings['connections']) {
            $validMappings = array_keys(Arr::get($settings, 'connections', []));

            $settings['mappings'] = array_filter($settings['mappings'], function ($key) use ($validMappings) {
                return in_array($key, $validMappings);
            });
        }

        $settings['providers']['outlook']['callback_url'] = rest_url('formglut-smtp/outlook_callback');

        $settings = array_merge(
            $settings,
            [
                'user_email' => wp_get_current_user()->user_email
            ]
        );

        return $settings;
    }

    public function maybeAdminNotice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $connections = $this->app->make(Manager::class)->getConfig('connections');

        global $wp_version;

        $requireUpdate = version_compare($wp_version, '5.5', '<');

        if ($requireUpdate) { ?>
            <div class="notice notice-warning">
                <p>
                    <?php echo sprintf(__('WordPress version 5.5 or greater is required for FormglutSMTP. You are using version %s currently. Please update your WordPress Core to use FormglutSMTP Plugin.', 'formglut-smtp'), $wp_version); ?>
                </p>
            </div>
        <?php } else if (empty($connections)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e('FormglutSMTP needs to be configured for it to work.', 'formglut-smtp'); ?>
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=formglut_forms_smtp'); ?>"
                       class="button button-primary">
                        <?php _e('Configure FormglutSMTP', 'formglut-smtp'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    public function addSimulationBar($adminBar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $misc = $this->app->make(Manager::class)->getConfig('misc');

        if ((!empty($misc['simulate_emails']) && $misc['simulate_emails'] == 'yes') || (defined('FORMGLUTMAIL_SIMULATE_EMAILS') && FORMGLUTMAIL_SIMULATE_EMAILS)) {
            $args = [
                'parent' => 'top-secondary',
                'id'     => 'formglutsmtp_simulated',
                'title'  => __('Email Disabled', 'formglut-smtp'),
                'href'   => admin_url('admin.php?page=formglut_forms_smtp/connections'),
                'meta'   => false
            ];

            echo '<style>li#wp-admin-bar-formglutsmtp_simulated a {background: red; color: white;}</style>';

            $adminBar->add_node($args);
        }
    }

    public function isRequireOptin()
    {
        $opted = get_option('_formglutsmtp_sub_update');
        if ($opted) {
            return 'no';
        }
        // check if dismissed
        $dismissedStamp = get_option('_formglutsmtp_dismissed_timestamp');
        if ($dismissedStamp && (time() - $dismissedStamp) < 30 * 24 * 60 * 60) {
            return 'no';
        }

        return 'yes';
    }

    public function initAdminWidget()
    {
        // This widget should be displayed for certain high-level users only.
        if (!current_user_can('manage_options') || apply_filters('formglut_mail_disable_dashboard_widget', false)) {
            return;
        }

        add_action('wp_dashboard_setup', function () {
            $widget_key = 'formglutsmtp_reports_widget';

            wp_add_dashboard_widget(
                $widget_key,
                esc_html__('Formglut SMTP', 'formglut-smtp'),
                [$this, 'dashWidgetContent']
            );

        });


    }

    public function dashWidgetContent()
    {
        ?>
        <style type="text/css">
            td.fstmp_failed {
                color: red;
                font-weight: bold;
            }
        </style>
        <div id="fsmtp_dashboard_widget_html" class="fsmtp_dash_wrapper">
            <h3 style="min-height: 170px;">Loading data....</h3>
        </div>
        <?php
        add_action('admin_footer', function () {
            ?>
            <script type="application/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    // send an ajax request to ajax url with raw javascript
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php?action=formglut_smtp_get_dashboard_html'); ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response && response.html) {
                                document.getElementById('fsmtp_dashboard_widget_html').innerHTML = response.html;
                            } else {
                                document.getElementById('fsmtp_dashboard_widget_html').innerHTML = '<h3>Failed to load FormglutSMTP Reports</h3>';
                            }
                        }
                    };

                    xhr.send();
                });
            </script>
            <?php
        });
    }

    protected function getDashboardWidgetHtml()
    {
        $stats = [];
        $logModel = new Logger();
        $currentTimeStamp = current_time('timestamp');
        $startToday = date('Y-m-d 00:00:01', $currentTimeStamp);

        $allTime = $logModel->getStats();

        $stats['today'] = [
            'title'  => __('Today', 'formglut-smtp'),
            'sent'   => ($allTime['sent']) ? $logModel->getTotalCountStat('sent', $startToday) : 0,
            'failed' => ($allTime['failed']) ? $logModel->getTotalCountStat('failed', $startToday) : 0
        ];

        $lastWeek = date('Y-m-d 00:00:01', strtotime('-7 days'));
        $stats['week'] = [
            'title'  => __('Last 7 days', 'formglut-smtp'),
            'sent'   => ($allTime['sent']) ? $logModel->getTotalCountStat('sent', $lastWeek) : 0,
            'failed' => ($allTime['failed']) ? $logModel->getTotalCountStat('failed', $lastWeek) : 0,
        ];

        $stats['all_time'] = [
            'title'  => __('All', 'formglut-smtp'),
            'sent'   => $allTime['sent'],
            'failed' => $allTime['failed'],
        ];
        ob_start();
        ?>
        <table class="fsmtp_dash_table wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th><?php _e('Date', 'formglut-smtp'); ?></th>
                <th><?php _e('Sent', 'formglut-smtp'); ?></th>
                <th><?php _e('Failed', 'formglut-smtp'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stats as $stat): ?>
                <tr>
                    <td><?php echo $stat['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                    <td><?php echo $stat['sent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                    <td class="<?php echo ($stat['failed']) ? 'fstmp_failed' : ''; ?>"><?php echo $stat['failed']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a style="text-decoration: none; padding-top: 10px; display: block"
           href="<?php echo admin_url('admin.php?page=formglut_forms_smtp'); ?>"
           class=""><?php _e('View All', 'formglut-smtp'); ?></a>
        <?php

        return ob_get_clean();
    }

    public function getTrans()
    {
        return [
            'Settings'                                              => __('Settings', 'formglut-smtp'),
            'Email Test'                                            => __('Email Test', 'formglut-smtp'),
            'Email Logs'                                            => __('Email Logs', 'formglut-smtp'),
            'Support'                                               => __('Support', 'formglut-smtp'),
            'Docs'                                                  => __('Docs', 'formglut-smtp'),
            'cancel'                                                => __('cancel', 'formglut-smtp'),
            'confirm'                                               => __('confirm', 'formglut-smtp'),
            'confirm_msg'                                           => __('Are you sure to delete this?', 'formglut-smtp'),
            'wizard_title'                                          => __('Welcome to FormGlut SMTP', 'formglut-smtp'),
            'wizard_sub'                                            => __('Thank you for installing FormglutSMTP - The ultimate SMTP & Email Service Connection Plugin for WordPress', 'formglut-smtp'),
            'wizard_instruction'                                    => __('Please configure your first email service provider connection', 'formglut-smtp'),
            'Sending Stats'                                         => __('Sending Stats', 'formglut-smtp'),
            'Quick Overview'                                        => __('Quick Overview', 'formglut-smtp'),
            'Total Email Sent (Logged):'                            => __('Total Email Sent (Logged):', 'formglut-smtp'),
            'Email Failed:'                                         => __('Email Failed:', 'formglut-smtp'),
            'Active Connections:'                                   => __('Active Connections:', 'formglut-smtp'),
            'Active Senders:'                                       => __('Active Senders:', 'formglut-smtp'),
            'Save Email Logs:'                                      => __('Save Email Logs:', 'formglut-smtp'),
            'Delete Logs:'                                          => __('Delete Logs:', 'formglut-smtp'),
            'Days'                                                  => __('Days', 'formglut-smtp'),
            'Subscribe To Updates'                                  => __('Subscribe To Updates', 'formglut-smtp'),
            'Last week'                                             => __('Last week', 'formglut-smtp'),
            'Last month'                                            => __('Last month', 'formglut-smtp'),
            'Last 3 months'                                         => __('Last 3 months', 'formglut-smtp'),
            'By Date'                                               => __('By Date', 'formglut-smtp'),
            'Apply'                                                 => __('Apply', 'formglut-smtp'),
            'Resend Selected Emails'                                => __('Resend Selected Emails', 'formglut-smtp'),
            'Bulk Action'                                           => __('Bulk Action', 'formglut-smtp'),
            'Delete All'                                            => __('Delete All', 'formglut-smtp'),
            'Enter Full Screen'                                     => __('Enter Full Screen', 'formglut-smtp'),
            'Filter By'                                             => __('Filter By', 'formglut-smtp'),
            'Status'                                                => __('Status', 'formglut-smtp'),
            'Date'                                                  => __('Date', 'formglut-smtp'),
            'Date Range'                                            => __('Date Range', 'formglut-smtp'),
            'Select'                                                => __('Select', 'formglut-smtp'),
            'Successful'                                            => __('Successful', 'formglut-smtp'),
            'Failed'                                                => __('Failed', 'formglut-smtp'),
            'Select date'                                           => __('Select date', 'formglut-smtp'),
            'Select date and time'                                  => __('Select date and time', 'formglut-smtp'),
            'Start date'                                            => __('Start date', 'formglut-smtp'),
            'End date'                                              => __('End date', 'formglut-smtp'),
            'Filter'                                                => __('Filter', 'formglut-smtp'),
            'Type & press enter...'                                 => __('Type & press enter...', 'formglut-smtp'),
            'Subject'                                               => __('Subject', 'formglut-smtp'),
            'To'                                                    => __('To', 'formglut-smtp'),
            'Date-Time'                                             => __('Date-Time', 'formglut-smtp'),
            'Actions'                                               => __('Actions', 'formglut-smtp'),
            'Retry'                                                 => __('Retry', 'formglut-smtp'),
            'Resend'                                                => __('Resend', 'formglut-smtp'),
            'Turn On'                                               => __('Turn On', 'formglut-smtp'),
            'Resent Count'                                          => __('Resent Count', 'formglut-smtp'),
            'Email Body'                                            => __('Email Body', 'formglut-smtp'),
            'Attachments'                                           => __('Attachments', 'formglut-smtp'),
            'Next'                                                  => __('Next', 'formglut-smtp'),
            'Prev'                                                  => __('Prev', 'formglut-smtp'),
            'Search Results for'                                    => __('Search Results for', 'formglut-smtp'),
            'Sender Settings'                                       => __('Sender Settings', 'formglut-smtp'),
            'From Email'                                            => __('From Email', 'formglut-smtp'),
            'Force From Email (Recommended Settings: Enable)'       => __('Force From Email (Recommended Settings: Enable)', 'formglut-smtp'),
            'from_email_tooltip'                                    => __('If checked, the From Email setting above will be used for all emails (It will check if the from email is listed to available connections).', 'formglut-smtp'),
            'Set the return-path to match the From Email'           => __('Set the return-path to match the From Email', 'formglut-smtp'),
            'From Name'                                             => __('From Name', 'formglut-smtp'),
            'Force Sender Name'                                     => __('Force Sender Name', 'formglut-smtp'),
            'Save Connection Settings'                              => __('Save Connection Settings', 'formglut-smtp'),
            'save_connection_error_1'                               => __('Please select your email service provider', 'formglut-smtp'),
            'save_connection_error_2'                               => __('Credential Verification Failed. Please check your inputs', 'formglut-smtp'),
            'force_sender_tooltip'                                  => __('When checked, the From Name setting above will be used for all emails, ignoring values set by other plugins.', 'formglut-smtp'),
            'Validating Data. Please wait'                          => __('Validating Data. Please wait', 'formglut-smtp'),
            'Active Email Connections'                              => __('Active Email Connections', 'formglut-smtp'),
            'Add Another Connection'                                => __('Add Another Connection', 'formglut-smtp'),
            'Provider'                                              => __('Provider', 'formglut-smtp'),
            'Connection Details'                                    => __('Connection Details', 'formglut-smtp'),
            'Close'                                                 => __('Close', 'formglut-smtp'),
            'General Settings'                                      => __('General Settings', 'formglut-smtp'),
            'Alerts'                                                => __('Alerts', 'formglut-smtp'),
            'Add Connection'                                        => __('Add Connection', 'formglut-smtp'),
            'Edit Connection'                                       => __('Edit Connection', 'formglut-smtp'),
            'routing_info'                                          => __('Your emails will be routed automatically based on From email address. No additional configuration is required.', 'formglut-smtp'),
            'Enable Email Summary'                                  => __('Enable Email Summary', 'formglut-smtp'),
            'Enable Email Summary Notification'                     => __('Enable Email Summary Notification', 'formglut-smtp'),
            'Notification Email Addresses'                          => __('Notification Email Addresses', 'formglut-smtp'),
            'Email Address'                                         => __('Email Address', 'formglut-smtp'),
            'Notification Days'                                     => __('Notification Days', 'formglut-smtp'),
            'Save Settings'                                         => __('Save Settings', 'formglut-smtp'),
            'Log All Emails for Reporting'                          => __('Log All Emails for Reporting', 'formglut-smtp'),
            'Disable Logging for FormglutCRM Emails'                  => __('Disable Logging for FormglutCRM Emails', 'formglut-smtp'),
            'FormglutCRM Email Logging'                               => __('FormglutCRM Email Logging', 'formglut-smtp'),
            'Delete Logs'                                           => __('Delete Logs', 'formglut-smtp'),
            'delete_logs_info'                                      => __('Select how many days, the logs will be saved. If you select 7 days, then logs older than 7 days will be deleted automatically.', 'formglut-smtp'),
            'Default Connection'                                    => __('Default Connection', 'formglut-smtp'),
            'Fallback Connection'                                   => __('Fallback Connection', 'formglut-smtp'),
            'default_connection_popover'                            => __('Select which connection will be used for sending transactional emails from your WordPress. If you use multiple connection then email will be routed based on source from email address', 'formglut-smtp'),
            'fallback_connection_popover'                           => __('Fallback Connection will be used if an email is failed to send in one connection. Please select a different connection than the default connection', 'formglut-smtp'),
            'Please add another connection to use fallback feature' => __('Please add another connection to use fallback feature', 'formglut-smtp'),
            'Email Simulation'                                      => __('Email Simulation', 'formglut-smtp'),
            'Email_Simulation_Label'                                => __('Disable sending all emails. If you enable this, no email will be sent.', 'formglut-smtp'),
            'Email_Simulation_Yes'                                  => __('No Emails will be sent from your WordPress.', 'formglut-smtp'),
            'Sending by time of day'                                => __('Sending by time of day', 'formglut-smtp'),
            'More'                                                  => __('More', 'formglut-smtp'),
            'Less'                                                  => __('Less', 'formglut-smtp'),
            'Last 7 Days'                                           => __('Last 7 Days', 'formglut-smtp'),
            'Last 30 Days'                                          => __('Last 30 Days', 'formglut-smtp'),
            'All Time'                                              => __('All Time', 'formglut-smtp')
        ];
    }
}
