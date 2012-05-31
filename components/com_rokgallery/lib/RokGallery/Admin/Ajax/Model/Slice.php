<?php
/**
  * @version   $Id: Slice.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelSlice extends RokCommon_Ajax_AbstractModel
{
    /**
     * Update the slice
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1,
     *  'slice':{'title':'new title','description':'new description','manipulations':}
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
            /** @var $slice RokGallery_Model_Slice */
            $slice = RokGallery_Model_SliceTable::getSingle($params->id);
            if ($slice === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_SLICE_N', $params->id));
            }
            foreach ($params->slice as $field => $value)
            {
                // change the manipulation format if passed
                if ($field == 'manipulations') {
                    $value = RokGallery_Manipulation_Helper::unserializeFromJson($value);
                }

                // Add any tags if they are passed
                if ($field == 'Tags') {
                    $slice->Tags->delete();
                    if (is_object($value)) $value = get_object_vars($value);
                    if (!empty($value)) {
                        foreach ($value as $tag)
                        {
                            $slice->addTag($tag);
                        }
                    }
                    continue;
                }

                if ($field == 'published' && $value == true && $slice->File->published == false)
                {
                    $slice->File->published = true;

                    $slice->File->save();
                }
                $slice->$field = $value;
            }
            $slice->File->clearRelated('Slices');
            $slice->save();

            // Prep output values
            $slice->populateFilterInfo();
            $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
            $slice->Tags;
            $slice->FileTags;
            $slice->File;

            // Set output payload
            $result->setPayload(array('slice' => $slice->toJsonableArray()));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Update the slice
     * $params object should be a json like
     * <code>
     * {
     *  'id': 1
     *  'slice':{'title':'new title','description':'new description'}
     * }
     * </code>
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function create($params)
    {
        $result = new RokCommon_Ajax_Result();
        try
        {
            $file = RokGallery_Model_FileTable::getSingle($params->fileId);

            /** @var $slice RokGallery_Model_Slice */
            $slice =& RokGallery_Model_Slice::createNew($file);
            if ($slice === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_SLICE_N', $params->id));
            }
            foreach ($params->slice as $field => $value)
            {
                // change the manipulation format if passed
                if ($field == 'manipulations') {
                    $value = RokGallery_Manipulation_Helper::unserializeFromJson($value);
                }

                // Add any tags if they are passed
                if ($field == 'Tags') {
                    if (!empty($value) && is_array($value)) {
                        foreach ($value as $tag)
                        {
                            $slice->addTag($tag);
                        }
                    }
                    continue;
                }
                $slice->$field = $value;
            }
            $slice->save();

            $slice->populateFilterInfo();
            $slice->manipulations = RokGallery_Manipulation_Helper::prepSerializedForJson($slice->manipulations);
            $slice->Tags;
            $slice->FileTags;
            $slice->File;
            $result->setPayload(array('slice' => $slice->toJsonableArray()));
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * Delete the slice and all associated rows (done by foreign keys) and files
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
            /** @var $slice RokGallery_Model_Slice */
            $slice = RokGallery_Model_SliceTable::getSingle($params->id);
            if ($slice === false) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_SLICE_N', $params->id));
            }
            if ($slice->admin_thumb) {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_DELETE_ADMIN_SLICE'));
            }
            $slice->delete();
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
     * Add an array of tags to a {@link RokGallery_Model_Slice} object
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
            $slice = RokGallery_Model_SliceTable::getSingle($params->id);
            if ($slice === false)
            {
                throw new RokCommon_Ajax_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_SLICE_N',$params->id));
            }

            foreach ($params->tags as $tag)
            {
                $found = false;
                foreach ($slice->Tags as $current_tag)
                {
                    if (strtolower($tag) == $current_tag['tag']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $next = $slice->Tags->count();
                    $slice->Tags[$next]['tag'] = $tag;
                }
            }
            $slice->save();
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $result;
    }

    /**
     * Removes an array of tags to a {@link RokGallery_Model_Slice} object
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
            $id = $params->id;
            $query = Doctrine_Query::create()
                    ->delete('RokGallery_Model_SliceTags st')
                    ->where('st.slice_id = ?', $id)
                    ->andWhereIn('st.tag', $params->tags);
            $query->execute();
            $query->free();
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;

    }

    /**
     * Wipes all tags from a  {@link RokGallery_Model_Slice} object
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
            $id = $params->id;
            $query = Doctrine_Query::create()
                    ->delete('RokGallery_Model_SliceTags st')
                    ->where('st.slice_id = ?', $id);
            $query->execute();
            $query->free();
        }
        catch (Exception $e)
        {
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
                    ->from('RokGallery_Model_SliceTags st')
                    ->where('st.slice_id = ?', $id)
                    ->orderby('st.tag');


            $tags = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            $query->free();
            if ($tags === false) {
                throw new RokCommon_Ajax_Exception('Unable to find tags for slice');
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
}
