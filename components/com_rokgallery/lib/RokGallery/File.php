<?php
 /**
  * @version   $Id: File.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_File
{
    /**
     * @param  $id
     * @return RokGallery_Model_File
     */
    public function &getSingle($id)
    {
        $query = Doctrine_Query::create()
                ->from('RokGallery_Model_File f')
                ->where('f.id = ?', $id);

        /** @var RokGallery_Model_File $file  */
        $file = $query->fetchOne(array(), Doctrine_Core::HYDRATE_RECORD);
        return $file;
    }



}
