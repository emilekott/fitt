<?php
 /**
  * @version   $Id: Crop.php 39564 2011-07-06 06:45:56Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Manipulation_Action_Crop extends RokGallery_Manipulation_AbstractAction {

    protected $type = 'crop';

    public $left;
    public $top;
    public $width;
    public $height;

    /**
     * Apply the manipulation with the setup options to the passed in image.
     * This does not do any memory manipulation
     *
     * @param WideImage_Image $image
     * @return WideImage_Image
     */
    public function &apply(WideImage_Image &$image)
    {
        if (!$this->isSetup())
            throw new RokGallery_Manipulation_Exception(rc__('ROKGALLERY_MANIPULATION_WAS_NOT_SETUP_PRIOR_TO_APPLYING'));
        if ($this->left ==0 && $this->top == 0 && $this->width == 0 && $this->height == 0)
        {
            $this->width = $image->getWidth();
            $this->height = $image->getHeight();
        }
        $return_image = $image->crop($this->left,$this->top,$this->width,$this->height);
        return $return_image;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
