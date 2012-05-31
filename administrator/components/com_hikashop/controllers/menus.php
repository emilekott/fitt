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
class MenusController extends hikashopController{
	var $toggle = array();
	var $type='menus';
	function __construct(){
		parent::__construct();
		$this->modify[]='add_module';
	}
	function add_module(){
		$id = hikashop_getCID('id');
		$menu = hikashop_get('class.menus');
		$menu->attachAssocModule($id);
		$this->edit();
	}
}