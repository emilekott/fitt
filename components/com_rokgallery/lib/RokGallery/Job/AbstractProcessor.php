<?php
/**
  * @version   $Id: AbstractProcessor.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

abstract class RokGallery_Job_AbstractProcessor implements RokGallery_Job_Processor
{
    protected $_job;

    /**
     * @param $jobid the
     * @return RokGallery_Job_Type
     *
     */
    public function __construct(RokGallery_Job &$job)
    {
        $this->_job =& $job;
        //register_shutdown_function (array($this->_job,'shutdown'));
    }

    /**
     * @param $properties
     * @param $action_desc
     * @return bool
     */
    protected function _checkState(&$properties, $action_desc)
    {
        $this->_job->refreshState();
        if ($this->_job->getStateName() == RokGallery_Job::STATE_PAUSING)
        {
            $this->_job->setProperties($properties);
            $this->_job->Paused(rc__('ROKGALLERY_N_PAUSED',$action_desc), $this->_job->getJob()->percent);
            return false;
        }
        if ($this->_job->getStateName() == RokGallery_Job::STATE_CANCELING)
        {
            $this->_job->setProperties($properties);
            $this->_job->Canceled(rc__('ROKGALLERY_N_CANCELED',$action_desc), $this->_job->getJob()->percent);
            return false;
        }
        return true;
    }
}
