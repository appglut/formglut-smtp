<?php

namespace FormglutSmtpLib;
return;
if (\class_exists('FormglutSmtpLib\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['FormglutSmtpLib\\Google\\Client' => 'Google_Client', 'FormglutSmtpLib\\Google\\Service' => 'Google_Service', 'FormglutSmtpLib\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', 'FormglutSmtpLib\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', 'FormglutSmtpLib\\Google\\Model' => 'Google_Model', 'FormglutSmtpLib\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', 'FormglutSmtpLib\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', 'FormglutSmtpLib\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', 'FormglutSmtpLib\\Google\\AuthHandler\\Guzzle5AuthHandler' => 'Google_AuthHandler_Guzzle5AuthHandler', 'FormglutSmtpLib\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', 'FormglutSmtpLib\\Google\\Http\\Batch' => 'Google_Http_Batch', 'FormglutSmtpLib\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', 'FormglutSmtpLib\\Google\\Http\\REST' => 'Google_Http_REST', 'FormglutSmtpLib\\Google\\Task\\Retryable' => 'Google_Task_Retryable', 'FormglutSmtpLib\\Google\\Task\\Exception' => 'Google_Task_Exception', 'FormglutSmtpLib\\Google\\Task\\Runner' => 'Google_Task_Runner', 'FormglutSmtpLib\\Google\\Collection' => 'Google_Collection', 'FormglutSmtpLib\\Google\\Service\\Exception' => 'Google_Service_Exception', 'FormglutSmtpLib\\Google\\Service\\Resource' => 'Google_Service_Resource', 'FormglutSmtpLib\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
    \class_alias($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \FormglutSmtpLib\Google\Task\Composer
{
}
/** @phpstan-ignore-next-line */
if (\false) {
    class Google_AccessToken_Revoke extends \FormglutSmtpLib\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \FormglutSmtpLib\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \FormglutSmtpLib\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle5AuthHandler extends \FormglutSmtpLib\Google\AuthHandler\Guzzle5AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \FormglutSmtpLib\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \FormglutSmtpLib\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \FormglutSmtpLib\Google\Client
    {
    }
    class Google_Collection extends \FormglutSmtpLib\Google\Collection
    {
    }
    class Google_Exception extends \FormglutSmtpLib\Google\Exception
    {
    }
    class Google_Http_Batch extends \FormglutSmtpLib\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \FormglutSmtpLib\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \FormglutSmtpLib\Google\Http\REST
    {
    }
    class Google_Model extends \FormglutSmtpLib\Google\Model
    {
    }
    class Google_Service extends \FormglutSmtpLib\Google\Service
    {
    }
    class Google_Service_Exception extends \FormglutSmtpLib\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \FormglutSmtpLib\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \FormglutSmtpLib\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \FormglutSmtpLib\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \FormglutSmtpLib\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \FormglutSmtpLib\Google\Utils\UriTemplate
    {
    }
}
