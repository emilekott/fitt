<?php
 /**
 * @version   $Id: GalleryFilter.php 39261 2011-07-01 06:43:23Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Site_DetailFilter extends RokGallery_Site_GalleryFilter
{
    /**
     * @return \RokGallery_Site_GalleryFilter
     */
    public function &reset()
    {
        $this->_query = Doctrine_Query::create()
                ->select('s.id')
                ->from('RokGallery_Model_Slice s')
                ->where('s.published = ?', true);
        $this->_orderby_run = false;
        return $this;
    }
}

