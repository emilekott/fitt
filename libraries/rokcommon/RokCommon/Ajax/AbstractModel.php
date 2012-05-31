<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_Ajax_AbstractModel implements RokCommon_Ajax_Model
{
    /**
     * @param  $params
     * @return RokCommon_Ajax_Result
     * @throws RokCommon_Ajax_Model
     */
    public function run($action, $params)
    {

        try
        {
            $action = (empty($action)) ? 'default' : $action;
            if (!method_exists($this, $action))
            {
                throw new RokCommon_Ajax_Exception('The ' . $action . ' action does not exist for this model');
            }
            return $this->$action($params);
        }
        catch (Exception $e)
        {
            throw $e;
        }

    }

    /**
     * @param RokCommon_Ajax_Result $result
     */
    protected function sendDisconnectingReturn(RokCommon_Ajax_Result $result)
    {
        // clean outside buffers;
        while (@ob_end_clean());
        header("Connection: close\r\n");
        header('Content-type: text/plain');
        session_write_close();
        ignore_user_abort(true);
        ob_start();
        echo json_encode($result);
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush(); // Strange behaviour, will not work
        flush(); // Unless both are called !
        while (@ob_end_clean());
        if( !ini_get('safe_mode') && strpos(ini_get('disable_functions'), 'set_time_limit') === false){
            @set_time_limit(0);
        }
        else{
            error_log('RokGallery: PHP safe_mode is on or the set_time_limit function is disabled.  This can cause timeouts while processing a job if your max_execution_time is not set high enough');
        }
    }
}
