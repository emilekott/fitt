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
class hikashopTaxationClass extends hikashopClass{
	var $tables = array('taxation');
	var $pkeys = array('taxation_id');
	var $toggle = array('taxation_published'=>'taxation_id');
	function get($id){
		$query='SELECT b.*,c.*,d.*,a.* FROM '.hikashop_table('taxation').' AS a LEFT JOIN '.hikashop_table('tax').' AS b ON a.tax_namekey=b.tax_namekey LEFT JOIN '.hikashop_table('category').' AS c ON a.category_namekey=c.category_namekey LEFT JOIN '.hikashop_table('zone').' AS d ON a.zone_namekey=d.zone_namekey WHERE a.taxation_id='.(int)$id.' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function saveForm(){
		$taxation = null;
		$taxation->taxation_id = hikashop_getCID('taxation_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['taxation'] as $column => $value){
			hikashop_secureField($column);
			$taxation->$column = strip_tags($value);
		}
		return $this->save($taxation);		
	}
}