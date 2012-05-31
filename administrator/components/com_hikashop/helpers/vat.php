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
class hikashopVatHelper{
	function isValid(&$vat){
		$class = hikashop_get('class.zone');
		$zone = $class->get(@$vat->address_country);
		if(empty($zone->zone_code_2) || !in_array($zone->zone_code_2,array('AT','BE','BG','CY','CZ','DK','EE','DE','PT','EL','ES','FI','HU','LU','MT','SI',
		'FR','GB','IE','IT','LV','LT','NL','PL','SK','RO','SE'))){
			return true;
		}
		$config = hikashop_config();
		$vat_check = (int)$config->get('vat_check',2);
		switch($vat_check){
			case 1:
			case 2:
				if(is_object($vat)){
					$vat_number =& $vat->address_vat;
				}else{
					$vat_number =& $vat;
				}
				$regex = $this->getRegex($vat_number);
				if($regex===false){
					if(is_object($vat) && !empty($vat->address_country)){
						if(!empty($zone->zone_code_2)){
							$vat_number = $zone->zone_code_2.$vat_number;
							$regex = $this->getRegex($vat_number);
						}
					}
					if($regex===false){
						$app =& JFactory::getApplication();
						$app->enqueueMessage(JText::_('VAT_NOT_FOR_YOUR_COUNTRY'));
						return false;
					}
				}
				if(!$this->regexCheck($vat_number,$regex)){
					return false;
				}
				if($vat_check==2){
					return $this->onlineCheck($vat_number);
				}
			case 0:
			default:
		}
		return true;
	}
	function regexCheck(  $vat , $regex) {
	    if(!preg_match($regex, $vat)){
	    	$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('VAT_NUMBER_NOT_VALID'));
	    	return false;
	    }
	    return true;
	}
	function getRegex($vat){
		$regex = false;
		switch(str_replace(array(' ','.'),array('',''),strtoupper(substr($vat,0, 2)))) {
	        case 'AT':
	            $regex = '/^(AT){0,1}U[0-9]{8}$/i';
	            break;
	        case 'BE':
	            $regex = '/^(BE){0,1}[0]{0,1}[0-9]{9}$/i';
	            break;
	        case 'BG':
	            $regex = '/^(BG){0,1}[0-9]{9,10}$/i';
	            break;
	        case 'CY':
	            $regex = '/^(CY){0,1}[0-9]{8}[A-Z]$/i';
	            break;
	        case 'CZ':
	            $regex = '/^(CZ){0,1}[0-9]{8,10}$/i';
	            break;
	        case 'DK':
	            $regex = '/^(DK){0,1}([0-9]{2}[\ ]{0,1}){3}[0-9]{2}$/i';
	            break;
	        case 'EE':
	        case 'DE':
	        case 'PT':
	        case 'EL':
	            $regex = '/^(EE|EL|DE|PT){0,1}[0-9]{9}$/i';
	            break;
	        case 'ES':
	            $regex = '/^(ES){0,1}([0-9A-Z][0-9]{7}[A-Z])|([A-Z][0-9]{7}[0-9A-Z])$/i';
	            break;
	        case 'FI':
	        case 'HU':
	        case 'LU':
	        case 'MT':
	        case 'SI':
	            $regex = '/^(FI|HU|LU|MT|SI){0,1}[0-9]{8}$/i';
	            break;
	        case 'FR':
	            $regex = '/^(FR){0,1}[0-9A-Z]{2}[\ ]{0,1}[0-9]{9}$/i';
	            break;
	        case 'GB':
	            $regex = '/^(GB){0,1}([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2})|([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i';
	            break;
	        case 'IE':
	            $regex = '/^(IE){0,1}[0-9][0-9A-Z\+\*][0-9]{5}[A-Z]$/i';
	            break;
	        case 'IT':
	        case 'LV':
	            $regex = '/^(IT|LV){0,1}[0-9]{11}$/i';
	            break;
	        case 'LT':
	            $regex = '/^(LT){0,1}([0-9]{9}|[0-9]{12})$/i';
	            break;
	        case 'NL':
	            $regex = '/^(NL){0,1}[0-9]{9}B[0-9]{2}$/i';
	            break;
	        case 'PL':
	        case 'SK':
	            $regex = '/^(PL|SK){0,1}[0-9]{10}$/i';
	            break;
	        case 'RO':
	            $regex = '/^(RO){0,1}[0-9]{2,10}$/i';
	            break;
	        case 'SE':
	            $regex = '/^(SE){0,1}[0-9]{12}$/i';
	            break;
	        default:
	            break;
	    }
	    return $regex;
	}
	function onlineCheck($vat){
		if (extension_loaded('soap')) {
			try{
				$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
				$result = $client->checkVat(array('countryCode' => substr($vat, 0, 2), 'vatNumber' => substr($vat, 2)));
				if ( !$result->valid ) {
					$app =& JFactory::getApplication();
					$app->enqueueMessage(JText::_('VAT_NUMBER_NOT_VALID'));
		            return false;
				}
			}catch (Exception $e) {
		       	$app =& JFactory::getApplication();
				$app->enqueueMessage($e->__toString());
		        return false;
			}
		}else{
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('SOAP_EXTENSION_NOT_FOUND'));
			return false;
		}
		return true;
	}
}