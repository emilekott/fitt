<?php
/**
  * @version   $Id: Job.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelJob extends RokCommon_Ajax_AbstractModel
{

    /**
     * create a new Job and return the Job Info
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function create($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::create(RokGallery_Job::TYPE_IMPORT);
            $result->setPayload(array('job' => $job->getId()));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * create a new Job and return the Job Info
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function ready($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            $job->Ready("Job is ready to process.");
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Run a job
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function process($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_WITH_ID_N_TO_PROCESS', $params->id));
            }
            $result->setMessage(rc__('ROKGALLERY_STARTING_N_JOB', ucfirst($job->getType())));
            $this->sendDisconnectingReturn($result);
            $job->Run('Starting Import');
            $job->process();
            die();
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Get the status of a Job
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function status($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_ID_N', $params->id));
            }
            $status = $job->getStatus(RokCommon_Composite::get('com_rokgallery.jobs')->load('default_single.php', array('job' => $job->getJob())));
            $result->setPayload($status);
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Send a job a pause command
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function pause($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_ID_N', $params->id));
            }
            $job->Pause();
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * resume a paused job
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function resume($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_ID_N', $params->id));
            }
            $result->setMessage(rc__('ROKGALLERY_STARTING_N_JOB', ucfirst($job->getType())));
            $this->sendDisconnectingReturn($result);
            $job->Resume('Resuming Processing');
            $job->process();
            die();
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Send a job a cancel command
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function cancel($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_ID_N', $params->id));
            }
            if ($job->getStateName() == RokGallery_Job::STATE_PAUSED || $job->getStateName() == RokGallery_Job::STATE_READY) {
                $job->Cancel();
                $job->Canceled(rc__('ROKGALLERY_N_CANCELED', $job->getJob()->type), $job->getJob()->percent);
            }
            else {
                $job->Cancel();
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Send a job a cancel command
     * $params object should be a json like
     * <code>
     * {
     *  'id': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function delete($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB_ID_N', $params->id));
            }
            $job->Delete();
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }
}
