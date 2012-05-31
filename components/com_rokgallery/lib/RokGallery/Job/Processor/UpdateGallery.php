<?php
/**
  * @version   $Id: UpdateGallery.php 39577 2011-07-06 10:25:27Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Processor_UpdateGallery extends RokGallery_Job_AbstractProcessor
{
    /**
     */
    public function process()
    {
        try
        {
            /** @var $properties RokGallery_Job_Property_ImportFile[] */
            $properties = $this->_job->getProperties();

            $total_files = count($properties['files']);


            foreach ($properties['files'] as $key => &$file)
            {
                // keep bumping the time as log as a file doesnt take 30 seconds or more
                /** @var RokGallery_Job_Property_ImportFile $file  */
                if (!$this->_checkState($properties, rc__('ROKGALLERY_CREATE_UPDATE'))) {
                    return;
                }
                if ($file->isCompleted()) {
                    continue;
                }

                RokGallery_Doctrine::getConnection()->beginTransaction();

                $gallery = RokGallery_Model_GalleryTable::getSingle($properties['galleryId']);
                if ($gallery === false)
                {
                    throw new RokGallery_Job_Exception(rc__('ROKGALLERY_NOT_A_VALID_GALLERY'));
                }
                $full_file = RokGallery_Model_FileTable::getSingle($file->getId());
                if ($full_file === false)
                {
                    $file->setStatus(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE'));
                    $file->setError(true);
                    RokGallery_Doctrine::getConnection()->commit();
                    continue;
                }

                if ($gallery && $full_file){
                    $full_file->updateSlicesForGallery($gallery);
                }

                $file->setCompleted();
                $percent = (int)((($key + 1) / $total_files) * 100);
                $this->_job->setProperties($properties);
                $this->_job->save(rc__('ROKGALLERY_UPDATED_GALLERY_SLICE_FOR_FILE_N', $full_file->title), $percent);

                RokGallery_Doctrine::getConnection()->commit();

            }
            $this->_job->Complete(rc__('ROKGALLERY_GALLERY_UPDATE_COMPLETE'));
            if (RokGallery_Config::getOption(RokGallery_Config::OPTION_AUTO_CLEAR_SUCCESSFUL_JOBS, false))
            {
                sleep(5);
                $this->_job->Delete();
            }
            return;
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            $this->_job->Error($e->getMessage());
            return;
        }
    }
}
