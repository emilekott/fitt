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
class RokgalleryViewGalleryPicker extends JView
{

    protected $rtmodel;

    public function __construct($config = array())
    {
        $option = JRequest::getCmd('option');
        parent::__construct($config);

        $document =& JFactory::getDocument();
        $session =& JFactory::getSession();
    }

    function display($tpl = null)
    {
        JHTML::_('behavior.mootools');
        $option = JRequest::getCmd('option');
        $document =& JFactory::getDocument();
        $session =& JFactory::getSession();

        $model = new RokGallery_Admin_MainPage();
        $current_page = 1;
        $items_per_page = 45;

        $gallery_id = (int) JRequest::getVar('gallery_id', 0);
        $file_id = (int) JRequest::getVar('file_id', 0);
        $load_page = (int) JRequest::getVar('page', 1);
        $textarea = JRequest::getVar('textarea', false);
        $inputfield = JRequest::getVar('inputfield', false);

        $show_menuitems = JRequest::getVar('show_menuitems', 1);

        $items_per_page = ($load_page * $items_per_page);

        if (!$gallery_id) $galleries = RokGallery_Model_GalleryTable::getAll();
        else $galleries = RokGallery_Model_GalleryTable::getSingle($gallery_id);
        if (!$file_id) {
            $files = $model->getFiles($current_page, $items_per_page);
        }
        else {
            $filter = json_decode('{"type":"id", "operator":"is", "query":'.$file_id.'}');
            $files = $model->getFiles($current_page, $items_per_page, array($filter))->getFirst();
        }
        
        $pager = $model->getPager($current_page, $items_per_page);

        $next_page = $current_page + 1;
        $next_page = ($current_page == $pager->getLastPage()) ? false : $next_page;
        $more_pages = ($next_page == false) ? "false" : "true";


        $application = JURI::root(true) . '/administrator/components/' . $option . '/assets/application/';
        $images = JURI::root(true) . '/administrator/components/' . $option . '/assets/images/';
        $url = JURI::root(true) . '/administrator/index.php?option=com_rokgallery&task=ajax&format=raw'; // debug: &XDEBUG_SESSION_START=default
        $modal_url = JURI::root(true) . '/administrator/index.php?option=com_rokgallery&view=gallerypicker&tmpl=component';
        if ($textarea !== false) $modal_url .= "&textarea=" . $textarea;
        if ($inputfield !== false) $modal_url .= '&inputfield=' . $inputfield;

        $document->addScriptDeclaration('var RokGallerySettings = {
            application: "' . $application . '", 
            images: "' . $images . '",  
            next_page: "' . $next_page .'", 
            more_pages: ' . $more_pages. ', 
            items_per_page: "' . $items_per_page .'",
            total_items: ' . $pager->getNumResults() . ',
            url: "' . $url . '", 
            modal_url: "'.$modal_url.'",
            textarea: "'.$textarea.'",
            inputfield: "'.$inputfield.'",
            token: "' . JUtility::getToken() . '", 
            session: {
                name: "' . $session->getName() . '", 
                id: "' . $session->getId() . '"
            },
            order: ["order-created_at", "order-desc"]
        };');

        $db		= JFactory::getDBO();
        $query	= $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            'm.id, m.title AS link_name, m.alias, m.published, m.access, m.language, l.title AS lang_title, ag.title AS access_group, mt.title AS menu_name'.
            ', CONCAT(m.link, "&Itemid=", m.id) AS menu_link'
        );
        $query->from('#__menu AS m');
        $query->join('LEFT', '#__languages AS l ON l.lang_code = m.language');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = m.access');
        $query->join('LEFT', '#__menu_types AS mt ON mt.menutype = m.menutype');
        $query->join('LEFT', '#__extensions AS ex ON ex.extension_id = m.component_id');
        $query->where('ex.element = "com_rokgallery"');
        $query->where('m.menutype != ""');
        $query->where('m.published != "-2"');
        $query->order('m.menutype ASC, m.title ASC');

        $db->setQuery($query);

		$menuitems = $db->loadObjectList();

        if ($galleries === false) $galleries = array();
        if ($files === false) $files = array();

        $this->assign('total_items_in_filter', $pager->getNumResults());
        $this->assign('items_to_be_rendered', $pager->getResultsInPage());
        $this->assign('next_page', $next_page);
        $this->assign('items_per_page', $items_per_page);
        $this->assign('items_per_row', $items_per_row);
        $this->assign('currently_shown_items', $pager->getLastIndice());
        $this->assign('totalFilesCount', $pager->getNumResults());

        $this->assign('show_menuitems', $show_menuitems);

        $this->assignRef('files', $files);
        $this->assignRef('galleries', $galleries);
        $this->assignRef('menuitems', $menuitems);

        $this->assign('gallery_id', $gallery_id);
        $this->assign('file_id', $file_id);
        $this->assign('textarea', $textarea);
        $this->assign('inputfield', $inputfield);
        $this->assign('context', 'com_rokgallery.gallerypicker');
        
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
