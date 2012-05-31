<?php
/**
  * @version   $Id: Gallery.php 39629 2011-07-07 01:42:09Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelGallery extends RokCommon_Ajax_AbstractModel
{

    /**
     * Get the basic gallery info and supporting slices/tags
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function get($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $gallery = RokGallery_Model_GalleryTable::getSingle($params->id);
            foreach ($gallery->Slices as &$slice)
            {
                $slice->populateFilterInfo();
                $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
                $slice->clearRelated('File');
                $slice->Tags;
                $slice->FileTags;
            }
            $sortable_slices = $gallery->Slices->getData();
            usort($sortable_slices,array('RokGalleryAdminAjaxModelGallery', 'slice_ordering_sort'));
            $html = RokCommon_Composite::get('com_rokgallery.galleryorder')->load('default.php', array('slices' => $sortable_slices));
            $result->setPayload(array('gallery' => $gallery->toJsonableArray(), 'html' => $html));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    protected static function slice_ordering_sort(RokGallery_Model_Slice $a, RokGallery_Model_Slice $b)
    {
        if ($a->ordering == $b->ordering) {
            return 0;
        }
        return ($a->ordering < $b->ordering) ? -1 : 1;
    }

    /**
     * Get the basic file info and supporting slices/tags
     * $params object should be a json like
     * <code>
     * {
     *
     *  "gallery": {
     *      "name": "Gallery name",
     *      "width": 100,
     *      "height": 100,
     *      "thumb_xsize": 50,
     *      "thumb_ysize": 50,
     *       "filetags": ["foo","foo2"]
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function create($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();
            $gallery = new RokGallery_Model_Gallery();
            if (RokGallery_Model_GalleryTable::getByName($params->gallery->name) !== false)
            {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_A_GALLERY_ALREADY_EXISTS_WITH_THE_NAME_N',$params->gallery->name));
            }
            foreach ($params->gallery as $field => $value)
            {
                if (isset($gallery->$field)) {
                    if ($value == '') $value = null;
                    $gallery->$field = $value;
                }
            }
            $gallery->save();
            $result->setPayload(array('gallery'=>$gallery->toJsonableArray()));

            if (null == $gallery->filetags)
            {
                $tx->commit();
                return $result;
            }

            $fileids = RokGallery_Model_FileTable::getIdsByTags($gallery->filetags);

            // just return if there are no files to process
            if (empty($fileids) || $fileids === false) {
                $tx->commit();
                return $result;
            }

            $files = array();
            foreach ($fileids as $fileid)
            {
                $files[] = new RokGallery_Job_Property_GalleryFile($fileid);
            }

            $properties = array("galleryId" => $gallery->id, "files" => $files);
            $job = RokGallery_Job::create(RokGallery_Job::TYPE_CREATEGALLERY);
            $job->propertires = serialize($properties);
            $job->setProperties($properties);
            $job->save();
            $tx->commit();

            $this->sendDisconnectingReturn($result);
            $job->Ready();
            $job->Run('Starting Gallery Creation');
            $job->process();
            die();

        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
    }

    /**
     * Get the basic file info and supporting slices/tags
     * $params object should be a json like
     * <code>
     * {
     *  "id": 1
     *  "gallery": {
     *      "name": "Gallery name",
     *      "width": 100
     *      "height": 100
     *      "thumb_xsize": 50
     *      "thumb_ysize": 50
     *   }
     *  "order": [1, 2, 10, 3, 8]
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function update($params)
    {
        $result = new RokCommon_Ajax_Result();
        /** @var Doctrine_Transaction $tx  */
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();

            $gallery = RokGallery_Model_GalleryTable::getSingle($params->id);
            if($gallery === false)
            {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_GALLERY_WITH_ID_N_TO_UPDATE', $params->id));
            }
            $oldfiletags = $gallery->filetags;

            $update_slices = false;
            foreach ($params->gallery as $field => $value)
            {
                if (isset($gallery->$field)) {

                    // see if we need to update the slices
                    if (
                        $field == 'width'
                        || $field == 'height'
                        || $field == 'keep_aspect'
                        || $field == 'thumb_xsize'
                        || $field == 'thumb_ysize'
                        || $field == 'thumb_background'
                        || $field == 'thumb_keep_aspect'
                    )
                        $update_slices = true;
                    if ($value == '')$value= null;
                    $gallery->$field = $value;
                }
            }

            $gallery->save();
            $result->setPayload(array('gallery'=>$gallery->toJsonableArray()));

            // get list of files to process
            $fileids = RokGallery_Model_FileTable::getIdsByTags($gallery->filetags);

            // get list of files to remove
            $original_fileids = RokGallery_Model_FileTable::getIdsByTags($oldfiletags);

            // get the list of file ids to remove or add
            $remove_fileids = array_diff($original_fileids, $fileids);
            $new_fileids = array_diff($fileids, $original_fileids);

            // just return if there are no files to process
            if ((empty($fileids) || $fileids === false) && (empty($remove_fileids) || $remove_fileids === false)) {
                $tx->commit();
                return $result;
            }

            /** @var RokGallery_Job_Property_GalleryFile[] $files  */
            $files = array();

            // Add any new files to the job
            if (!empty($new_fileids)) {
                foreach ($new_fileids as $new_fileid)
                {
                    $files[] = new RokGallery_Job_Property_GalleryFile($new_fileid);
                }
            }

            // if we need to update all slices populate them
            if ($update_slices) {
                foreach ($fileids as $fileid)
                {
                    $files[] = new RokGallery_Job_Property_GalleryFile($fileid);
                }
            }

            $remove_slices = array();
            // remove all non linked slices
            foreach ($gallery->Slices as $key => &$slice)
            {
                /** @var RokGallery_Model_Slice $slice */
                if (in_array($slice->File->id, $remove_fileids)) {
                    $remove_slices[] = $slice->id;
                }
            }


            foreach ($remove_slices as $remove_slice_id)
            {
                $remove_slice = RokGallery_Model_SliceTable::getSingle($remove_slice_id);
                if (RokGallery_Config::getOption(RokGallery_Config::OPTION_GALLERY_REMOVE_SLICES, false)) {
                    $remove_slice->delete();
                }
                else {
                   $remove_slice->unlink('Gallery');
                   $remove_slice->save();
                }
            }

            // if there are no files to process just return
            if (empty($files))
            {
                $tx->commit();
                return;
            }

            $properties = array("galleryId" => $gallery->id, "files" => $files);
            $job = RokGallery_Job::create(RokGallery_Job::TYPE_UPDATEGALLERY);
            $job->propertires = serialize($properties);
            $job->setProperties($properties);
            $job->save();

            $tx->commit();

            // Disconnect and process job
            $this->sendDisconnectingReturn($result);

            $job->Ready();
            $job->Run('Starting Gallery Update');
            $job->process();
            die();

        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
    }

    /**
     * Get the basic file info and supporting slices/tags
     * $params object should be a json like
     * <code>
     * {
     *  "id": 1
     * "
     * }
     * </code>
     * @param $params
     */
    public function delete($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();
            $gallery = RokGallery_Model_GalleryTable::getSingle($params->id);
            if ($gallery === false)
            {
                throw new RokCommon_Ajax_Exception('No gallery with id ' . $params->id);
            }
            $delete_slices = (isset($params->delete_slices)) ? $params->delete_slices: false ;
            if ($delete_slices) {
                foreach ($gallery->Slices as $slice)
                {
                    $slice->delete();
                }
            }
            $gallery->delete();
            $result->setPayload(array('id'=>$params->id));
            $tx->commit();
        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
        return $result;

    }

    /**
     * Publish all slices for the gallery
     * $params object should be a json like
     * <code>
     * {
     *  "id": 1
     * }
     * </code>
     * @param $params
     */
    public function publish($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();
            $gallery = RokGallery_Model_GalleryTable::getSingle($params->id);
            if ($gallery === false)
            {
                throw new RokCommon_Ajax_Exception('No gallery with id ' . $params->id);
            }
            $ret= RokGallery_Model_GalleryTable::publishSlices($params->id);
            $result->setPayload(array('id'=>$params->id));
            $tx->commit();
        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
        return $result;
    }

    /**
     * Save the order of images in a gallery
     * $params object should be a json like
     * <code>
     * {
     *   "id" : 1,
     *   "order": [1, 2, 10, 3, 8]
     * }
     * </code>
     * @param $params
     */
    public function order($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();
            $gallery = RokGallery_Model_GalleryTable::getSingle($params->id);
            if ($gallery === false)
            {
                throw new RokCommon_Ajax_Exception('No gallery with id ' . $params->id);
            }
            $gallery->setSliceOrder($params->order);
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
