<?php defined('ABSPATH') or die;


if (! function_exists('FormglutSmtpDb')) {
    /**
     * @return \FormglutMail\App\Services\DB\QueryBuilder\QueryBuilderHandler
     */
    function FormglutSmtpDb()
    {
        static $FormglutSmtpDb;

        if (! $FormglutSmtpDb) {
            global $wpdb;

            $connection = new \FormglutMail\App\Services\DB\Connection($wpdb, ['prefix' => $wpdb->prefix]);

            $FormglutSmtpDb = new \FormglutMail\App\Services\DB\QueryBuilder\QueryBuilderHandler($connection);
        }

        return $FormglutSmtpDb;
    }
}
