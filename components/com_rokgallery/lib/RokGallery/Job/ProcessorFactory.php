<?php
/**
  * @version   $Id: ProcessorFactory.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_ProcessorFactory
{
    /**
     * @param RokGallery_Model_Job $job
     * @return RokGallery_Job_Processor
     * @throws RokGallery_Job_Exception
     */
    public static function &factory(RokGallery_Job &$job)
    {
        $classname = 'RokGallery_Job_Processor_'.ucfirst(str_replace(' ','', $job->getType()));
        if (!class_exists($classname, true)){
            throw new RokGallery_Job_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_PROCESS_FOR_JOB_TYPE_N', $job->getType()));
        }
        $ret = new $classname($job);
        return $ret;
    }
}
