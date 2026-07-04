<?php

namespace FormglutMail\App\Http\Controllers;

use FormglutMail\App\App;

abstract class Controller
{
    /**
     * @var \FormglutMail\App\Plugin
     */
    protected $app = null;

    /**
     * @var \FormglutMail\Includes\Request\Request
     */
    protected $request = null;

    /**
     * @var \FormglutMail\Includes\Response\Response
     */
    protected $response = null;

    public function __construct()
    {
        $this->app = App::getInstance();
        $this->request = $this->app['request'];
        $this->response = $this->app['response'];
    }

    public function send($data = null, $code = 200)
    {
        return $this->response->send($data, $code);
    }

    public function sendSuccess($data = null, $code = 200)
    {
        return $this->response->sendSuccess($data, $code);
    }

    public function sendError($data = null, $code = 422)
    {
        return $this->response->sendError($data, $code);
    }

    public function verify()
    {
        $permission = 'manage_options';
        if(!current_user_can($permission)) {
            wp_send_json_error([
                'message' => __('You do not have permission to do this action', 'formglut-smtp')
            ]);
            die();
        }

        $nonce = $this->request->get('nonce');
        if(!wp_verify_nonce($nonce, FORMGLUTMAIL)) {
            wp_send_json_error([
                'message' => __('Security Failed. Please reload the page', 'formglut-smtp')
            ]);
            die();
        }

        return true;
    }
}
