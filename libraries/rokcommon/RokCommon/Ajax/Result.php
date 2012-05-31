<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokCommon_Ajax_Result {

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    /**
     * @var string error|success
     */
    public $status = self::STATUS_SUCCESS;

    /**
     * @var string
     */
    public $message = '';

    /**
     * The model specific payload
     * @var varies
     */
    public $payload;


    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \varies $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return \varies
     */
    public function getPayload()
    {
        return $this->payload;
    }


    /**
     * Sets the result to be an error
     */
    public function setAsError(){
        $this->status = self::STATUS_ERROR;
    }

    /**
     * Sets the result to be a success
     */
    public function setAsSuccess(){
        $this->status = self::STATUS_SUCCESS;
    }
}
