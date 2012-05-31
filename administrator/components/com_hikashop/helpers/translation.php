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
class hikashopTranslationHelper{
	var $languages = array();
	function hikashopTranslationHelper(){
		$this->database =& JFactory::getDBO();
		$app =& JFactory::getApplication();
		if(version_compare(JVERSION,'1.6','<')){
			$this->flagPath = 'components/com_joomfish/images/flags/';
		}else{
			$this->flagPath = 'media/mod_languages/images/';
		}
		if($app->isAdmin()){
			$this->flagPath = '../'.$this->flagPath;
		}
	}
	function isMulti($inConfig=false,$level=true){
		static $multi=array();
		$key = (int)$inConfig.'_'.(int)$level;
		if(!isset($multi[$key])){
			$multi[$key] = false;
			$config=&hikashop_config();
			if((hikashop_level(1) || !$level) && ($config->get('multi_language_edit',1) || $inConfig)){
				$query='SHOW TABLES LIKE '.$this->database->Quote($this->database->getPrefix().substr(hikashop_table('jf_content',false),3));
				$this->database->setQuery($query);
				$table = $this->database->loadResult();
				if(!empty($table)) $multi[$key] = true;
			}
		}
		return $multi[$key];
	}
	function getFlag($id=0){
		$this->loadLanguages();
		if(isset($this->languages[$id])){
			return '<span style="background: url('.$this->flagPath.$this->languages[$id]->shortcode.'.gif) no-repeat;padding-left:20px">'.$this->languages[$id]->code.'</span>';
		}
		return $this->languages[$id]->code;
	}
	function loadLanguages($active = true){
		if(empty($this->languages)){
			if(version_compare(JVERSION,'1.6','<')){
				$query = 'SELECT * FROM '.hikashop_table('languages',false).($active?' WHERE active=1':'');
			}else{
				$query = 'SELECT lang_id as id, lang_code as code, image as shortcode, published as active FROM '.hikashop_table('languages',false).($active?' WHERE published=1':'');
			}
			$this->database->setQuery($query);
			$this->languages = $this->database->loadObjectList('id');
		}
		return $this->languages;
	}
	function loadLanguage($id){
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT * FROM '.hikashop_table('languages',false).' WHERE id='.(int)$id;
		}else{
			$query = 'SELECT lang_id as id, lang_code as code, image as shortcode, published as active FROM '.hikashop_table('languages',false).' WHERE lang_id='.(int)$id;
		}
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function getId($code){
		$this->loadLanguages();
		foreach($this->languages as $lg){
			if($lg->code==$code) return $lg->id;
		}
		return 0;
	}
	function load($table,$id,&$element,$language_id=0){
		$where="";
		if(empty($language_id)){
			$this->loadLanguages();
			$languages =& $this->languages;
		}else{
			$where=' AND language_id='.(int)$language_id;
			$languages=array((int)$language_id=>$this->loadLanguage($language_id));
		}
		$query = 'SELECT * FROM '.hikashop_table('jf_content',false).' WHERE reference_id='.(int)$id.' AND reference_table='.$this->database->Quote($table).$where;
		$this->database->setQuery($query);
		$data = $this->database->loadObjectList();
		$element->translations=array();
		if(!empty($data)){
			foreach($data as $entry){
				$field = $entry->reference_field;
				$lg = (int)$entry->language_id;
				if(!isset($element->translations[$lg])){
					$obj = null;
					$obj->$field = $entry;
					$element->translations[$lg] = $obj;
				}else{
					$element->translations[$lg]->$field=$entry;
				}
			}
		}
		foreach($languages as $lg){
			$lgid = (int)$lg->id;
			if(!isset($element->translations[$lgid])){
				$element->translations[$lgid] = array();
			}
		}
		ksort($element->translations);
	}
	function getTranslations(&$element){
		$transArray = JRequest::getVar('translation',array(),'','array',JREQUEST_ALLOWRAW);
		foreach($transArray as $field => $trans){
			foreach($trans as $lg => $value){
				if(!empty($value)){
					$obj = null;
					$obj->reference_field = $field;
					$obj->language_id=(int)$lg;
					$obj->value = $value;
					$element->translations[(int)$lg]->$field = $obj;
				}
			}
		}
		foreach($_POST as $name => $value){
			if(preg_match('#^translation_([a-z_]+)_([0-9]+)$#i',$name,$match)){
				$html_element = JRequest::getVar($name,'','','string',JREQUEST_ALLOWRAW);
				if(!empty($html_element)){
					$obj = null;
					$type = $match[1];
					$obj->reference_field = $type;
					$obj->language_id=$match[2];
					$obj->value = $html_element;
					$element->translations[$match[2]]->$type = $obj;
				}
			}
		}
	}
	function handleTranslations($table,$id,&$element){
		$table = 'hikashop_'.$table;
		$transArray = JRequest::getVar('translation',array(),'','array',JREQUEST_ALLOWRAW);
		$arrayToSearch = array();
		$conditions = array();
		foreach($transArray as $field => $trans){
			foreach($trans as $lg => $value){
				if(!empty($value)){
					$lg = (int)$lg;
					$field = hikashop_secureField($field);
					$arrayToSearch[]=array('value'=>$value,'language_id'=>$lg,'reference_field'=>$field);
					$conditions[] = ' language_id = '.$lg.' AND reference_field = '.$this->database->Quote($field).' AND reference_table = '.$this->database->Quote($table).' AND reference_id='.$id;
				}
			}
		}
		foreach($_POST as $name => $value){
			if(preg_match('#^translation_([a-z_]+)_([0-9]+)$#i',$name,$match)){
				$html_element = JRequest::getVar($name,'','','string',JREQUEST_ALLOWRAW);
				if(!empty($html_element)){
					$lg = (int)$match[2];
					$field = hikashop_secureField($match[1]);
					$value = $html_element;
					$arrayToSearch[]=array('value'=>$value,'language_id'=>$lg,'reference_field'=>$field);
					$conditions[] = ' language_id = '.$lg.' AND reference_field = '.$this->database->Quote($field).' AND reference_table = '.$this->database->Quote($table).' AND reference_id='.$id;
				}
			}
		}
		if(!empty($arrayToSearch)){
			$query='SELECT * FROM '.hikashop_table('jf_content',false).' WHERE ('.implode(') OR (',$conditions).');';
			$this->database->setQuery($query);
			$entries = $this->database->loadObjectList('id');
			$user =& JFactory::getUser();
			$userId = $user->get( 'id' );
			$toInsert=array();
			foreach($arrayToSearch as $item){
				$already=false;
				if(!empty($entries)){
					foreach($entries as $entry_id => $entry){
						if($item['language_id']==$entry->language_id &&$item['reference_field']==$entry->reference_field){
							$query='UPDATE '.hikashop_table('jf_content',false).' SET value='.$this->database->Quote($item['value']).', modified_by='.$userId.', modified=NOW() WHERE id='.$entry_id.';';
							$this->database->setQuery($query);
							$this->database->query();
							$already=true;
							break;
						}
					}
				}
				if(!$already){
					$toInsert[]=$item;
				}
			}
			if(!empty($toInsert)){
				$conf =& hikashop_config();
				$default_translation_publish = (int)$conf->get('default_translation_publish','0');
				$rows = array();
				foreach($toInsert as $item){
					$field = $item['reference_field'];
					$rows[]=$id.','.$item['language_id'].','.$this->database->Quote($table).','.$this->database->Quote($item['value']).','.$this->database->Quote($field).','.$this->database->Quote(md5($element->$field)).','.$default_translation_publish.','.$userId.',\'\',NOW()';
				}
				$query = 'INSERT IGNORE INTO '.hikashop_table('jf_content',false).' (reference_id,language_id,reference_table,value,reference_field,original_value,published,modified_by,original_text,modified) VALUES ('.implode('),(',$rows).');';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
	}
	function deleteTranslations($table,$ids){
		if($this->isMulti()){
			if(!is_array($ids))$ids = array($ids);
			$query = 'DELETE FROM '.hikashop_table('jf_content',false).' WHERE reference_table = '.$this->database->Quote('hikashop_'.$table).' AND reference_id IN ('.implode(',',$ids).')';
			$this->database->setQuery($query);
			$this->database->query();
		}
	}
	function getStatusTrans(){
		$config =& JFactory::getConfig();
		$locale = $config->getValue('config.language');	
		$user =& JFactory::getUser();
		$current_locale = $user->getParam('language');
		if(empty($current_locale)){
			$current_locale=$locale;
		}
		$database =& JFactory::getDBO();
		$query = 'SELECT a.category_name,a.category_id FROM '.hikashop_table('category'). ' AS a WHERE a.category_type=\'status\'';
		$database->setQuery($query);
		if(class_exists('JFDatabase')){
			$statuses = $database->loadObjectList('category_id',false);
		}else{
			$statuses = $database->loadObjectList('category_id');
		}
		if($this->isMulti(true, false)){
			$lgid = $this->getId($current_locale);
			$query = 'SELECT value,reference_id FROM '.hikashop_table('jf_content',false).' WHERE reference_table=\'hikashop_category\' AND reference_field=\'category_name\' AND published=1 AND language_id='.$lgid.' AND reference_id IN('.implode(',',array_keys($statuses)).')';
			$database->setQuery($query);
			$trans = $database->loadObjectList('reference_id');
			foreach($statuses as $k => $stat){
				if(isset($trans[$k])){
					$statuses[$k]->status = $trans[$k]->value;
				}else{
					$val = str_replace(' ','_',strtoupper($statuses[$k]->category_name));
					$new = JText::_($val);
					if($val!=$new){
						$statuses[$k]->status=$new;
					}else{
						$statuses[$k]->status=$statuses[$k]->category_name;
					}
				}
			}
		}else{
			foreach($statuses as $k => $stat){
				$val = str_replace(' ','_',strtoupper($statuses[$k]->category_name));
				$new = JText::_($val);
				if($val!=$new){
					$statuses[$k]->status=$new;
				}else{
					$statuses[$k]->status=$statuses[$k]->category_name;
				}
			}
		}
		$cleaned_statuses = array();
		foreach($statuses as $status){
			$cleaned_statuses[$status->category_name]=$status->status;
		}
		return $cleaned_statuses;
	}
}
