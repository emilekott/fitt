<?php
/**
  * @version   $Id: view.html.php 39662 2011-07-07 06:50:52Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.view');
class RokgalleryViewDefault extends JView
{
    public function __construct($config = array())
    {
       	$option = JRequest::getCmd('option');
        parent::__construct($config);
    }

    function display($tpl = null)
    {
		JHTML::_('behavior.mootools');
        JHTML::_('behavior.keepalive');
        $option = JRequest::getCmd('option');
        $document =& JFactory::getDocument();
        $session =& JFactory::getSession();

        $model = new RokGallery_Admin_MainPage();
        $current_page = 1;
        $items_per_page = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_PAGE,6);
        $items_per_row = RokGallery_Config::getOption(RokGallery_Config::OPTION_ADMIN_ITEMS_PER_ROW,3);



        $files = $model->getFiles($current_page, $items_per_page*2);
        $pager = $model->getPager($current_page, $items_per_page*2);


        $next_page = ($current_page == 1) ? 3 : $current_page + 1;
        $next_page = ($current_page == $pager->getLastPage()) ? false : $next_page;

        $more_pages = ($next_page == false) ? "false" : "true";

        $application = JURI::root(true) . '/administrator/components/' . $option . '/assets/application/';
        $images = JURI::root(true) . '/administrator/components/' . $option . '/assets/images/';
        $url = JURI::root(true) . '/administrator/index.php?option=com_rokgallery&task=ajax&format=raw'; // debug: &XDEBUG_SESSION_START=default


        $document->addScriptDeclaration('var RokGallerySettings = {
			application: "' . $application . '", 
			images: "' . $images . '", 
			next_page: "' . $next_page .'",
            last_page: "' . $pager->getLastPage() . '",
			more_pages: ' . $more_pages. ', 
			items_per_page: "' . $items_per_page .'",
            total_items: ' . $pager->getNumResults() . ',
			url: "' . $url . '", 
			token: "' . JUtility::getToken() . '", 
			session: {
				name: "' . $session->getName() . '", 
				id: "' . $session->getId() . '"
			},
			order: ["order-created_at", "order-desc"]
		};');

		$document->addStyleSheet('components/'.$option.'/assets/styles/master.css');
		if (RokCommon_Browser::getShortName() == 'ie8'){
		    $document->addStyleSheet('components/'.$option.'/assets/styles/internet-explorer-8.css');
		}
		$document->addScript('components/'.$option.'/assets/application/Common.js');
		$document->addScript('components/'.$option.'/assets/application/RokGallery.js');
		$document->addScript('components/'.$option.'/assets/application/RokGallery.Filters.js');
		$document->addScript('components/'.$option.'/assets/application/RokGallery.Blocks.js');
		$document->addScript('components/'.$option.'/assets/application/RokGallery.FileSettings.js');
		$document->addScript('components/'.$option.'/assets/application/RokGallery.Edit.js');
		$document->addScript('components/'.$option.'/assets/application/MainPage.js');
		$document->addScript('components/'.$option.'/assets/application/Tags.js');
		$document->addScript('components/'.$option.'/assets/application/Tags.Slice.js');
		$document->addScript('components/'.$option.'/assets/application/Tags.Ajax.js');
		$document->addScript('components/'.$option.'/assets/application/Scrollbar.js');
		$document->addScript('components/'.$option.'/assets/application/Popup.js');
		$document->addScript('components/'.$option.'/assets/application/Progress.js');
		$document->addScript('components/'.$option.'/assets/application/Job.js');
		$document->addScript('components/'.$option.'/assets/application/JobsManager.js');
		$document->addScript('components/'.$option.'/assets/application/MassTags.js');
		$document->addScript('components/'.$option.'/assets/application/GalleriesManager.js');
		$document->addScript('components/'.$option.'/assets/application/Swiff.Uploader.js');
		$document->addScript('components/'.$option.'/assets/application/Uploader.js');
		$document->addScript('components/'.$option.'/assets/application/Rubberband.js');
		$document->addScript('components/'.$option.'/assets/application/Marquee.js');
		$document->addScript('components/'.$option.'/assets/application/Marquee.Crop.js');



        $galleries = RokGallery_Model_GalleryTable::getAll();
        if ($galleries === false) {
            $galleries = array();
        }

        $this->assign('total_items_in_filter', $pager->getNumResults());
        $this->assign('items_to_be_rendered', $pager->getResultsInPage());
        $this->assign('next_page', $next_page);
        $this->assign('items_per_page', $items_per_page);
        $this->assign('items_per_row', $items_per_row);
        $this->assign('currently_shown_items', $pager->getLastIndice());
        $this->assign('totalFilesCount', $pager->getNumResults());

        $this->assignRef('files', $files);
        $this->assignRef('galleries', $galleries);

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
