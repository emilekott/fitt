<?php
/**
 * @package		HikaShop for Joomla!
 * @version		1.5.5
 * @author		hikashop.com
 * @copyright	(C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
function com_uninstall(){
	$uninstallClass = new hikashopUninstall();
	$uninstallClass->unpublishModules();
	$uninstallClass->unpublishPlugins();
}
class hikashopUninstall{
	var $db;
	function hikashopUninstall(){
		$this->db =& JFactory::getDBO();
		$this->db->setQuery("DELETE FROM `#__hikashop_config` WHERE `config_namekey` = 'li' LIMIT 1");
		$this->db->query();
	 	if(version_compare(JVERSION,'1.6.0','>=')){
			$this->db->setQuery("DELETE FROM `#__menu` WHERE link LIKE '%com_hikashop%'");
			$this->db->query();
		}
	}
	function unpublishModules(){
		$this->db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%hikashop%'");
		$this->db->query();
	}
	function unpublishPlugins(){
		$this->db->setQuery("UPDATE `#__plugins` SET `published` = 0 WHERE `element` LIKE '%hikashop%' AND `folder` NOT LIKE '%hikashop%'");
		$this->db->query();
	}
}