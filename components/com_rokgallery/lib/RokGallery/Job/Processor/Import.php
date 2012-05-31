<?php
/**
  * @version   $Id: Import.php 39577 2011-07-06 10:25:27Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_Processor_Import extends RokGallery_Job_AbstractProcessor
{
    /**
     */
    public function process()
    {
        try
        {
            /** @var RokGallery_Job_Property_ImportFile[] $import_files  */
            $import_files = $this->_job->getProperties();
            $total_files = count($import_files);
            foreach ($import_files as $key => &$import_file)
            {
                $this->_job->refreshState();
                if (!$this->_checkState($properties, 'Import')) {
                    return;
                }
                if ($import_file->isCompleted()) {
                    continue;
                }

                RokGallery_Doctrine::getConnection()->beginTransaction();
                $file = RokGallery_Model_File::createNew($import_file->getFilename(), $import_file->getPath());

                // If we need to check to make sure it is not a duplicated file
                if (RokGallery_Config::getOption(RokGallery_Config::OPTION_ALLOW_DUPLICATE_FILES, true) == false
                    && RokGallery_Model_FileTable::getMD5($file->md5) !== false
                    && RokGallery_Model_FileTable::getMD5($file->md5)->count() > 0
                ) {
                    throw new RokGallery_Job_Exception(rc__('ROKGALLERY_A_MATCHING_FILE_FOR_N_IN_SYSTEM', $file->filename));
                }

                // Copy file to fine directory
                $basepath = dirname($file->getFullPath());
                if (!file_exists($basepath)) {
                    @mkdir($basepath, 0777, true);
                    RokGallery_Queue_DirectoryCreate::add($basepath);
                }

                if (!(file_exists($basepath) && is_dir($basepath) && is_writable($basepath))) {
                    throw new RokGallery_Job_Exception(rc__('ROKGALLERY_UNABLE_TO_CREATE_OR_WRITE_TO_THE_DIR_N', $basepath));
                }

                // Move the file to its final location
                $endpath = $file->getFullPath();
                rename($import_file->getPath(), $endpath);

                // update the image file info
                $file_image_info = @getimagesize($endpath);
                $file->xsize = $file_image_info[0]; /// x size
                $file->ysize = $file_image_info[1]; /// y size


                // Create the initial admin slice
                $this->createInitialAdminSlice($file);

                // Save the file to the db;
                $file->save();


                $import_file->setCompleted();

                $percent = (int)((($key + 1) / $total_files) * 100);
                $this->_job->setProperties($import_files);
                $this->_job->save(rc__('ROKGALLERY_IMPORTED_FILE_N', $import_file->getFilename()), $percent);
                RokGallery_Doctrine::getConnection()->commit();
                $file->free(true);
            }
            $this->_job->Complete('Importing Complete');
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


    /**
     * @param RokGallery_Model_File $file
     * @throws RokGallery_Job_Exception
     */
    protected function createInitialAdminSlice(RokGallery_Model_File &$file)
    {

        $default_x = RokGallery_Config::DEFAULT_ADMIN_THUMB_XSIZE;
        $default_y = RokGallery_Config::DEFAULT_ADMIN_THUMB_YSIZE;


        $admin_slice = RokGallery_Model_Slice::createNew($file, 'Admin Thumbnail', 'Admin Thumbnail', false, true);

        $modifications = array();

        // Create the Admin Slice
        $source_aspect_ratio = $file->xsize / $file->ysize;
        $desired_aspect_ratio = $default_x / $default_y;

        if (!($file->xsize < $default_x && $file->ysize < $default_y)) {
            $resize_width = $default_x;
            $resize_height = $default_y;
            $crop_left = 0;
            $crop_top = 0;

            if ($source_aspect_ratio > $desired_aspect_ratio) // wider image
            {
                $resize_height = $default_y;
                $resize_width = (int)round($default_y * $source_aspect_ratio);
                $crop_left = (int)round(($resize_width - $default_x) / 2);

            }
            elseif ($source_aspect_ratio < $desired_aspect_ratio) // taller image
            {
                $resize_width = $default_x;
                $resize_height = (int)round($default_x / $source_aspect_ratio);
                $crop_top = (int)round(($resize_height - $default_y) / 2);
            }

            $modifications[] = new RokGallery_Manipulation_Action_Resize(array('width' => $resize_width, 'height' => $resize_height));
            $modifications[] = new RokGallery_Manipulation_Action_Crop(array('left' => $crop_left, 'top' => $crop_top, 'width' => $default_x, 'height' => $default_y));
        }
        $admin_slice->manipulations = $modifications;
    }
}
