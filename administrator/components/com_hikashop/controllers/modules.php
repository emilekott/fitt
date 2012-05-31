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
class ModulesController extends hikashopController{
	var $toggle = array();
	var $type='modules';
	function __construct(){
		parent::__construct();
		$this->display[]='selectmodules';
		$this->display[]='savemodules';
	}
	function selectmodules(){
		JRequest::setVar( 'layout', 'selectmodules'  );
		return parent::display();
	}
	function savemodules(){
		JRequest::setVar( 'layout', 'savemodules'  );
		return parent::display();
	}
}