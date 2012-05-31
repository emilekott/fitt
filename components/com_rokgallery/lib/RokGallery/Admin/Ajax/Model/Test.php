<?php
/**
  * @version   $Id: Test.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGalleryAdminAjaxModelTest extends RokCommon_Ajax_AbstractModel
{
    function foo($params)
    {
        try
        {
            $result = new RokCommon_Ajax_Result();
            //
            //            $tags = array();
            //
            //            $query = Doctrine_Query::create()
            //                    ->select('ft.tag as tag')
            //                    ->from('RokGallery_Model_FileTags ft')
            //                    ->groupBy('tag')
            //                    ->orderBy('tag');
            //
            //            $file_tags = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            //            foreach($file_tags as $tag){
            //                $tags[$tag['tag']]=$tag['tag'];
            //            }
            //
            //            $query = Doctrine_Query::create()
            //                    ->select('st.tag as tag')
            //                    ->from('RokGallery_Model_SliceTags st')
            //                    ->groupBy('tag')
            //                    ->orderBy('tag');
            //
            //            $slice_tags = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            //            foreach($slice_tags as $tag){
            //                $tags[$tag['tag']]=$tag['tag'];
            //            }
            //
            //            $tags = array_keys($tags);
            //            sort($tags);
            //            $result->setPayload($tags);
            //
            //        }
            //        catch (Exception $e)
            //        {
            //            throw $e;
            //        }


            //            $original_h = $image->getHeight();
            //            $original_w = $image->getWidth();
            //            $target_h = 180;
            //            $target_w = 300;
            //
            //            $new_h = ($original_h/$original_w)*$target_w;
            //            $new_w = $target_h/($original_h/$original_w);


            //            $image = WideImage::loadFromFile(JPATH_SITE . "/media/rokgallery/7/332d3a82dc6227309a120493476f0fc/7332d3a82dc6227309a120493476f0fc-1.png");
            //            $outimage = $image->resize(300,180);
            //            $outimage->saveToFile(JPATH_SITE . "/media/rokgallery/7/332d3a82dc6227309a120493476f0fc/7332d3a82dc6227309a120493476f0fc-1-thumb.png");
            //
            //            $result->setPayload($outimage->getHeight());

            $result = new RokCommon_Ajax_Result();
            try
            {
            $result->setMessage('Starting');
            $this->sendDisconnectingReturn($result);
            sleep(30);
            die();
            }
            catch (Exception $e)
            {
                throw $e;
            }

        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * create a new Job and return the Job Info
     *
     * @param $params
     * @return RokCommon_Ajax_Result
     */
    public function createJob($params)
    {
        $result = new RokCommon_Ajax_Result();
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            $tx->beginTransaction();

            $job = new RokGallery_Model_Job();
            $job->id = RokCommon_UUID::generate();
            $job->type = RokGallery_Model_Job::TYPE_UPLOAD;
            $job->state = RokGallery_Model_Job::STATE_PENDING;
            $job->percent = 0;
            $job->save();
            $result->setPayload(array('job' => $job->id));
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
