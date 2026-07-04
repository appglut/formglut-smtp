<?php


add_filter('formglut_crm/quick_links', function ($links) {
    $links[] = [
        'title' => __('SMTP/Mail Settings', 'formglut-smtp'),
        'url'   => admin_url('admin.php?page=formglut_forms_smtp')
    ];

    return $links;
});

add_filter( 'plugin_action_links_' . plugin_basename( FORMGLUTMAIL_PLUGIN_FILE ), function ($links) {
    $links['settings'] = sprintf(
        '<a href="%s" aria-label="%s">%s</a>',
        admin_url('admin.php?page=formglut_forms_smtp/connections'),
        esc_attr__( 'Go to Formglut SMTP Settings page', 'formglut-smtp' ),
        esc_html__( 'Settings', 'formglut-smtp' )
    );
    return $links;
}, 10, 1 );
