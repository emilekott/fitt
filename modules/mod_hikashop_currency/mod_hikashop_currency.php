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
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};
$currency = hikashop_get('type.currency');
$config =& hikashop_config();
if(isset($_SERVER["REQUEST_URI"])){
  	$requestUri = $_SERVER["REQUEST_URI"];
  }else{
	$requestUri = $_SERVER['PHP_SELF'];
	if (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
  }
$redirectUrl = (hikashop_isSSL() ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$requestUri;
require(JModuleHelper::getLayoutPath('mod_hikashop_currency'));