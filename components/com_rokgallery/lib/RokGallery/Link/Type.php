<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
interface RokGallery_Link_Type {
    /**
     * @abstract
     * @param array $vars
     */
    public function __construct(array $vars);

    /**
     * @abstract
     * @return string
     */
    public function getUrl();

    /**
     * @abstract
     * @return RokGallery_Model_Info
     */
    public function getJSONable();

    /**
     * @abstract
     * @return string
     */
    public function getType();
}
