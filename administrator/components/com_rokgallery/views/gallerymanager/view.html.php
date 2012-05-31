<?php
 /**
 * @version   $Id: view.html.php 39496 2011-07-05 08:12:22Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the rokgallery component
 */
class RokgalleryViewGalleryManager extends JView
{

    protected $rtmodel;

    public function __construct($config = array())
    {
        $option = JRequest::getCmd('option');
        parent::__construct($config);

        $document =& JFactory::getDocument();
        $session =& JFactory::getSession();

        $this->rtmodel = new RokGallery_Site_DetailModel();
    }

    function display($tpl = null)
    {
        JHTML::_('behavior.mootools');
        JHTML::_('behavior.keepalive');

        $app =& JFactory::getApplication();
        $document =& JFactory::getDocument();


        $id = (int) JRequest::getVar('id');
        $force_fixed_size = JRequest::getVar('fixed',0);
        $name = JRequest::getVar('name');



        $galleries = RokGallery_Model_GalleryTable::getAll();
        $current_gallery = false;
        if (null != $id){
            $current_gallery = RokGallery_Model_GalleryTable::getSingle($id);
        }

        if (null != $name) {
            $default_name = $name . rc__('ROKGALLERY_GALLERY_CREATE_DEFAULT_EXTENSION');
        }
		
        $this->assign('default_name', $default_name);
        $this->assign('current_gallery_id', $id);
        $this->assign('force_fixed_size', $force_fixed_size);
        $this->assignRef('galleries', $galleries);
        $this->assignRef('current_gallery', $current_gallery);
        $this->assign('context', 'com_rokgallery.gallerymanager');

        $this->setLayout('default');
        parent::display($tpl);
    }

    protected function _replaceMooTools(){
        $option = JRequest::getCmd('option');
        $document =& JFactory::getDocument();


		// mootools
        $mootools11 = JURI::root(true) .'/media/system/js/mootools-core.js';
        $mootools12 = JURI::root(true) .'/media/system/js/mootools-more.js';
        $mootools13 ='components/'.$option.'/assets/js/mootools.js';
		
		// modal
        $modal = JURI::root(true) .'/media/system/js/modal.js';
        $modal13 ='components/'.$option.'/assets/js/modal-1.3.js';

        $scripts = array();
        foreach ($document->_scripts as $key => $value) {
			if ($key == $mootools11 || $key == $mootools12) $scripts[$mootools13] = $value;
			else if ($key == $modal) $scripts[$modal13] = $value;
			else { $scripts[$key] = $value; }
        }

        $document->_scripts = $scripts;
    }
}
