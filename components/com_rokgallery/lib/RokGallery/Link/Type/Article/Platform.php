<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
interface RokGallery_Link_Type_Article_Platform
{
    /**
     * @abstract
     * @param $id
     * @return RokGallery_Link_Type_Article_Info
     */
    public function &getArticleInfo($id);
}
