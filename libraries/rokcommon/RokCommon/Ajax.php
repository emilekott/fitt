<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_Ajax
{
    const DEFAULT_MODEL_PREFIX = 'RokCommon_Ajax_Model_';

    /**
     * @param $path
     * @param string $prefix
     * @param int $priority
     */
    public static function addModelPath($path, $prefix = self::DEFAULT_MODEL_PREFIX, $priority = 10)
    {
        try
        {
            if (!RokCommon_ClassLoader::isLoaderRegistered('AjaxModels'))
            {
                $ajaxModelLoader = new RokCommon_Ajax_ModelLoader();
                RokCommon_ClassLoader::registerLoader('AjaxModels', $ajaxModelLoader);
            }
            else
            {
                $ajaxModelLoader = RokCommon_ClassLoader::getLoader('AjaxModels');
            }
        }
        catch (Exception $le)
        {
            throw $le;
        }

        try
        {
            $ajaxModelLoader->addModelPath($path, $prefix, $priority);
        }
        catch (RokCommon_Ajax_Exception $e)
        {
            throw $e;
        }
    }


    /**
     * @param $model
     * @param $action
     * @param $params
     * @return string
     */
    public static function run($model, $action, $params)
    {
        // Set up an independent AJAX error handler
        set_error_handler(array('RokCommon_Ajax', 'error_handler'));
        set_exception_handler(array('RokCommon_Ajax','exception_handler'));

        $result = null;
        try
        {
            /** @var RokCommon_Ajax_ModelLoader $ajaxModelLoader  */
            $ajaxModelLoader = RokCommon_ClassLoader::getLoader('AjaxModels');

            // get a model class instance
            $modelInstance = $ajaxModelLoader->getModel($model);

            $decoded_params = json_decode($params);
            if (null == $decoded_params && strlen($params)> 0)
            {
                throw new RokCommon_Ajax_Exception('Invalid JSON for params');
            }
            // set the result to the run
            $result = $modelInstance->run($action, $decoded_params);
        }
        catch (Exception $ae)
        {
            $result = new RokCommon_Ajax_Result();
            $result->setAsError();
            $result->setMessage($ae->getMessage());
        }

        $encoded_result = json_encode($result);

        // restore normal error handling;
        restore_error_handler();
        restore_exception_handler();

        return $encoded_result;
    }

    public static function exception_handler(Exception $exception)
    {
        echo "Uncaught Exception: ". $exception->getMessage(). "\n";
        echo '['.$exception->getCode().'] File: '. $exception->getFile() . ' Line: '.$exception->getLine();
    }

    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        switch ($errno) {
            case E_USER_ERROR:
                echo "ERROR [$errno] $errstr\n";
                echo "  Fatal error on line $errline in file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
                echo "Aborting...\n";
                exit(1);
                break;
            case E_USER_WARNING:
                echo "WARNING [$errno] $errstr\n";
                break;
            case E_USER_NOTICE:
                echo "NOTICE [$errno] $errstr\n";
                break;
            case E_STRICT:
                return false;
                break;
            default:
                throw new RokCommon_Ajax_Exception("UNHANDLED ERROR [$errno] $errstr $errfile:$errline");
                break;
        }

        /* Don't execute PHP internal error handler */
        return true;
    }

}
