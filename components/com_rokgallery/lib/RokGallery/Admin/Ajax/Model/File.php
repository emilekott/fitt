<?php
/**
  * @version   $Id: File.php 39425 2011-07-04 00:32:54Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelFile extends RokCommon_Ajax_AbstractModel
{
    /**
     * Get the basic file info and supporting slices/tags
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
            $q = Doctrine_Query::create()
                    ->select('f.*')
                    ->from('RokGallery_Model_File f')
                    ->leftJoin('f.Slices s')
                    ->where('f.id = ?', $params->id)
                    ->orderBy('s.admin_thumb, s.title');

            $file = $q->fetchOne();
            $q->free();
            $file->imageurl;
            $file->adminthumburl;
            $file->miniadminthumburl;
            $file->Tags;
            foreach ($file->Slices as &$slice)
            {
                $slice->populateFilterInfo();
                $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
                $slice->clearRelated('File');
                $slice->Tags;
                $slice->FileTags;
                $slice->Gallery;
            }
            $result->setPayload(array('file' => $file->toJsonableArray(),'defaults'=>$this->getDefaults()));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Delete the file and all associated rows (done by foreign keys) and files
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
    public function delete($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();

            $file = RokGallery_Model_FileTable::getSingle($params->id);
            if ($file === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE_N_TO_DELETE', $params->id));
            }
            $realdirpath = realpath($file->basepath);
            if ($realdirpath == false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_DIR_N_FOR_FILE_N_TO_REMOVE', $realdirpath, $file->title));
            }

            $delret = RokGallery_Helper::delete_folder($realdirpath);
            if ($delret === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_DELETE_DIR_N_FOR_FILE_N_TO_REMOVE', $realdirpath, $file->title));
            }
            $file->delete();
            RokGallery_Doctrine::getConnection()->commit();
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;
    }

    /**
     * Delete the file (done by foreign keys)
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1,
     *  'file':{'title':'new title','description':'new description'}
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function update($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $file = RokGallery_Model_FileTable::getSingle($params->id);
            if ($file === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE_N_TO_UPDATE', $params->id));
            }
            foreach ($params->file as $field => $value) {
                if (isset($file->$field)) {
                    $file->$field = $value;
                }
            }
            $file->save();
            RokGallery_Doctrine::getConnection()->commit();
            $file->refresh();

            $file->imageurl;
            $file->adminthumburl;
            $file->miniadminthumburl;
            $file->Tags;
            foreach ($file->Slices as &$slice)
            {
                $slice->populateFilterInfo();
                $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
                $slice->clearRelated('File');
                $slice->Tags;
                $slice->FileTags;
                $slice->Gallery;
            }
            $result->setPayload(array('file' => $file->toJsonableArray(),'defaults'=>$this->getDefaults()));
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;
    }

    /**
     * Add an array of tags to a {@link RokGallery_Model_File} object
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1,
     *  'tags':['tag1','tag2']
     * }
     * </code>
     *
     * @throws RokCommon_Ajax_Exception
     * @param  $params
     * @return RokCommon_Ajax_Result
     */
    public function addTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $file = RokGallery_Model_FileTable::getSingle($params->id);
            if ($file === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE_TO_ADD_TAG_TO'));
            }
            RokGallery_Model_FileTable::addTagsToFile($file, $params->tags);
            RokGallery_Doctrine::getConnection()->commit();

            $file->imageurl;
            $file->Tags;
            foreach ($file->Slices as &$slice)
            {
                $slice->populateFilterInfo();
                $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
                $slice->clearRelated('File');
                $slice->Tags;
                $slice->FileTags;
                $slice->Gallery;
            }
            $result->setPayload(array('file' => $file->toJsonableArray(),'defaults'=>$this->getDefaults()));
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }

        return $result;
    }

    /**
     * Removes an array of tags to a {@link RokGallery_Model_File} object
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1,
     *  'tags':['tag1','tag2']
     * }
     * </code>
     *
     * @param  $params
     * @return RokCommon_Ajax_Result
     */
    public function removeTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $file = RokGallery_Model_FileTable::getSingle($params->id);
            if ($file === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_FILE'));
            }
            RokGallery_Model_FileTable::removeTagsFromFile($file, $params->tags);
            RokGallery_Doctrine::getConnection()->commit();

            $file->imageurl;
            $file->Tags;
            foreach ($file->Slices as &$slice)
            {
                $slice->populateFilterInfo();
                $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
                $slice->clearRelated('File');
                $slice->Tags;
                $slice->FileTags;
                $slice->Gallery;
            }
            $result->setPayload(array('file' => $file->toJsonableArray()));
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;

    }

    /**
     * Wipes all tags from a  {@link RokGallery_Model_File} object
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
     * }
     * </code>
     *
     * @param  $params
     * @return RokCommon_Ajax_Result
     */
    public function wipeTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            RokGallery_Doctrine::getConnection()->beginTransaction();
            $query = Doctrine_Query::create()
                    ->delete('RokGallery_Model_FileTags ft')
                    ->where('ft.file_id = ?', $params->id);
            $query->execute();
            RokGallery_Doctrine::getConnection()->commit();
        }
        catch (Exception $e)
        {
            RokGallery_Doctrine::getConnection()->rollback();
            throw $e;
        }
        return $result;
    }

    /**
     * Gets all tags for a {@link RokGallery_Model_File} object
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
     * }
     * </code>
     *
     * @param  $params
     * @return RokCommon_Ajax_Result
     */
    public function getTags($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $id = $params->id;
            $query = Doctrine_Query::create()
                    ->from('RokGallery_Model_FileTags ft')
                    ->where('ft.file_id = ?', $id)
                    ->orderby('ft.tag');


            $tags = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            if ($tags === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_TAGS_FOR_FILE'));
            }

            $payload = array();

            foreach ($tags as $tag)
            {
                $payload[] = $tag['tag'];
            }

            $result->setPayload(array('tags' => $payload));
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getDefaults()
    {
         return array(
                'thumb_xsize' => RokGallery_Config::getOption(RokGallery_Config::OPTION_DEFAULT_THUMB_XSIZE,RokGallery_Config::DEFAULT_DEFAULT_THUMB_XSIZE),
                'thumb_ysize' => RokGallery_Config::getOption(RokGallery_Config::OPTION_DEFAULT_THUMB_YSIZE,RokGallery_Config::DEFAULT_DEFAULT_THUMB_YSIZE),
                'thumb_keep_aspect' => (int)RokGallery_Config::getOption(RokGallery_Config::OPTION_DEFAULT_THUMB_KEEP_ASPECT,RokGallery_Config::DEFAULT_DEFAULT_THUMB_KEEP_ASPECT),
                'thumb_background' => RokGallery_Config::getOption(RokGallery_Config::OPTION_DEFAULT_THUMB_BACKGROUND,RokGallery_Config::DEFAULT_DEFAULT_THUMB_BACKGROUND),
            );
    }
}
