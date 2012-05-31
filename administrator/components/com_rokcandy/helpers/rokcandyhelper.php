<?php
/**
 * RokCandy Macros RokCandy Macro Helper
 *
 * @package		Joomla
 * @subpackage	RokCandy Macros
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author RocketTheme, LLC
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class RokCandyHelper {
    
    function getMacros() {
        $params =& JComponentHelper::getParams('com_rokcandy');
        $cache = & JFactory::getCache('com_rokcandy');
        
        if ($params->get('forcecache',0)==1) $cache->setCaching(true);
        $usermacros = $cache->call(array('RokCandyHelper','getUserMacros'));
        $overrides = RokCandyHelper::getTemplateOverrides();
    
        return array_merge($usermacros,$overrides);
    }
    
    function getUserMacros() {
        $db	=& JFactory::getDBO();
        $sql = 'SELECT * FROM #__rokcandy WHERE published=1';
        $db->setQuery($sql);
        $macros = $db->loadObjectList(); 
        
        $library = array();
        if (!empty($macros)) {
            foreach ($macros as $macro) {
                $library[trim($macro->macro)] = trim($macro->html);
            }
        }
        return $library;
    }
    
    
    function getTemplateOverrides() {
        
        $params =& JComponentHelper::getParams('com_rokcandy');
		$cache = & JFactory::getCache('com_rokcandy');
		if ($params->get('forcecache',0)==1) $cache->setCaching(true);
	    $library = $cache->call(array('RokCandyHelper','readIniFile'));

	    return $library;
    }

    function readIniFile() {
        
        $app	= JFactory::getApplication();
        
        $template = $app->isAdmin() ? RokCandyHelper::getCurrentTemplate() : $app->getTemplate();
		$path = JPATH_SITE.DS."templates".DS.$template.DS."html".DS."com_rokcandy".DS."default.ini";
		
        $library = array();
        
        if (file_exists($path)) {
            jimport( 'joomla.filesystem.file' );
            $content = JFile::read($path);
            $data = explode("\n",$content);
            
            if (!empty($data)){
                foreach ($data as $line) {
                    //skip comments
                    if (strpos($line,"#")!==0 and trim($line)!="" ) {
                       $div = strpos($line,"]=");
                       $library[substr($line,0,$div+1)] = substr($line,$div+2);
                    }
                }
            }
    	}
		return $library;
    }

    function getCurrentTemplate()
    {
        $cache = JFactory::getCache('com_rokcandy', '');
        if (!$templates = $cache->get('templates'))
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id, home, template, params');
            $query->from('#__template_styles');
            $query->where('client_id = 0');

            $db->setQuery($query);
            $templates = $db->loadObjectList('id');
            foreach ($templates as &$template)
            {
                $registry = new JRegistry;
                $registry->loadJSON($template->params);
                $template->params = $registry;

                // Create home element
                if ($template->home == '1' && !isset($templates[0]))
                {
                    $templates[0] = clone $template;
                }
            }
            $cache->store($templates, 'templates');
        }

        $template = $templates[0];
        return $template->template;
    }

    /**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('MACROS'),
			'index.php?option=com_rokcandy',
			$vName == 'macros'
		);

		JSubMenuHelper::addEntry(
			JText::_('CATEGORIES'),
			'index.php?option=com_categories&extension=com_rokcandy',
			$vName == 'categories'
		);
	}
}