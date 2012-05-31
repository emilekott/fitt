<?php
 /**
  * @version   $Id: Action.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

interface RokGallery_Manipulation_Action {

    /**
     *
     */
    public function __construct($array = array());

    /**
     * Takes an array of options and applies them to be properties of the Manipulation.
     * The key of the array is the name of the property to apply the value to.
     *
     * @param array $options
     */
    public function setup($options = array());

    /**
     * Apply the manipulation with the setup options to the passed in image.
     * This does not do any memory manipulation
     *
     * @param WideImage_Image $image
     * @return WideImage_Image
     */
    public function &apply(WideImage_Image &$image);

    public function getType();
}




