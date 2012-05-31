<?php
/**
  * @version   $Id: Jobs.php 39596 2011-07-06 18:47:12Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelJobs extends RokCommon_Ajax_AbstractModel
{
    /**
     * Get the full list of jobs
     * <code>
     * {
     *  'orberby': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function get($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $q = Doctrine_Query::create()
                    ->select('j.*')
                    ->from('RokGallery_Model_Job j')
                    ->orderBy('j.created_at DESC');

            $jobs = $q->execute(array(), Doctrine_Core::HYDRATE_RECORD);
            $outjobs = array();
            foreach ($jobs as $job)
            {
                unset($job->properties);
                unset($job->sm);
                $outjobs[] = $job->toJsonableArray();
            }
            $html = RokCommon_Composite::get('com_rokgallery.jobs')->load('default.php',array('jobs'=>$jobs));
            $result->setPayload(array('jobs' => $outjobs,'html'=>$html));

        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Get the full list of jobs
     * <code>
     * {
     *  'orberby': 'xxxx-x-x-x-x-x-x'
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function clean($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $q = Doctrine_Query::create()
                    ->select('j.id')
                    ->from('RokGallery_Model_Job j')
                    ->where('j.state = ?' , RokGallery_Job::STATE_COMPLETE)
                    ->orWhere('j.state = ?' , RokGallery_Job::STATE_ERRORED)
                    ->orWhere('j.state = ?' , RokGallery_Job::STATE_CANCELED);

            $jobs = $q->fetchArray();
            $q->free();
            foreach ($jobs as $job)
            {
                $real_job = RokGallery_Job::get($job['id']);
                $real_job->Delete();
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * wipe all jobs no matter what state
     * <code>
     * {
     * }
     * </code>
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function wipe($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $q = Doctrine_Query::create()
                    ->delete()
                    ->from('RokGallery_Model_Job j');

            $jobs = $q->execute();
            $q->free();
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

}
