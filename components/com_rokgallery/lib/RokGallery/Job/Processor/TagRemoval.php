<?php
/**
  * @version   $Id: TagRemoval.php 39577 2011-07-06 10:25:27Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Processor_TagRemoval extends RokGallery_Job_AbstractProcessor
{
    /**
     */
    public function process()
    {
        try
        {
            /** @var $properties RokGallery_Job_Property_FileTags[] */
            $properties = $this->_job->getProperties();

            $total_files = count($properties);

            foreach ($properties as $key => &$filetags)
            {
                // keep bumping the time as log as a file doesnt take 30 seconds or more
                if (!$this->_checkState($properties, rc__('ROKGALLERY_CREATE_UPDATE'))) {
                    return;
                }
                if ($filetags->isCompleted()) {
                    continue;
                }

                RokGallery_Doctrine::getConnection()->beginTransaction();
                $file = RokGallery_Model_FileTable::getSingle($filetags->getFileId());
                if (!$file)
                {
                    $filetags->setStatus(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE'));
                    $filetags->setError(true);
                    RokGallery_Doctrine::getConnection()->commit();
                    continue;
                }
                RokGallery_Model_FileTable::removeTagsFromFile($file, $filetags->getTags());
                $filetags->setCompleted();

                $percent = (int)((($key + 1) / $total_files) * 100);
                $this->_job->setProperties($properties);
                $this->_job->save(rc__('ROKGALLERY_REMOVE_TAGS_FROM_FILE_N', $file->title), $percent);

                RokGallery_Doctrine::getConnection()->commit();
            }
            $this->_job->Complete(rc__('ROKGALLERY_TAG_REMOVAL_COMPLETE'));
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
