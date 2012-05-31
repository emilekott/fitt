<?php
/**
  * @version   $Id: Upload.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelUpload extends RokCommon_Ajax_AbstractModel
{
    /**
     * Delete the file and all associated rows (done by foreign keys) and files
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
    public function file($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('READ UNCOMMITTED');
        try
        {

            if (count($_FILES) == 0)
            {
                throw new RokGallery_Job_Exception(rc__('ROKGALLERY_NO_FILES_SENT'));
            }

            $job = RokGallery_Job::get($params->id);
            if ($job === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_JOB', $params->id));
            }
            if ($job->getStateName() != RokGallery_Job::STATE_PREPPING)
            {
                throw new RokGallery_Job_Exception(rc__('ROKGALLERY_NOT_IN_PREPPING_STATUS'));
            }

            if ($job->getType() != RokGallery_Job::TYPE_IMPORT)
            {
                throw new RokGallery_Job_Exception(rc__('ROKGALLERY_NOT_AN_IMPORT_JOB'));
            }

            $job_properties = $job->getProperties();

            if (empty($job_properties))
            {
                $job_properties = array();
            }

            $basepath = RokGallery_Config::getOption(RokGallery_Config::OPTION_JOB_QUEUE_PATH) . DS . $job->getId();

            if (!file_exists($basepath))
            {
                @mkdir($basepath);
                RokGallery_Queue_DirectoryCreate::add($basepath);
            }

            if (!(file_exists($basepath) && is_dir($basepath) && is_writable($basepath)))
            {
                throw new RokGallery_Job_Exception(rc__('ROKGALLERY_UNABLE_TO_CREATE_OR_WRITE_TO_TEMP_DIR', $basepath));
            }

            $tx->beginTransaction();
            foreach ($_FILES as $uploaded_file)
            {
                if ($uploaded_file['error'] == UPLOAD_ERR_OK)
                {
                    $file = new RokGallery_Job_Property_ImportFile();
                    $file->setFilename($uploaded_file['name']);
                    $file->setPath($basepath .DS.$file->getId());
                    move_uploaded_file($uploaded_file['tmp_name'], $file->getPath());
                    $job_properties[] = $file;
                }
            }
            $job->setProperties($job_properties);
            $job->save();
            $tx->commit();

        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
        return $result;
    }
}
