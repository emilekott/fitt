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
class hikashopEditorHelper{
	var $width = '100%';
	var $height = '500';
	var $cols = 100;
	var $rows = 20;
	var $editor = null;
	var $name = '';
	var $content = '';
	function hikashopEditorHelper(){
		$this->setEditor();
		$this->options = array('pagebreak');
		$config =& hikashop_config();
		$readmore = $config->get('readmore',0);
		if(!$readmore){
			$this->options[]='readmore';
		}
	}
	function setDescription(){
		$this->width = 700;
		$this->height = 200;
		$this->cols = 80;
		$this->rows = 10;
	}
	function setContent($var){
		$name = $this->myEditor->get('_name');
		if(!empty($name)){
			if($name == 'jce'){
				return " try{JContentEditor.setContent('".$this->name."', $var ); }catch(err){".$this->myEditor->setContent($this->name,$var)."} ";
			}
			if($name == 'fckeditor'){
				return " try{FCKeditorAPI.GetInstance('".$this->name."').SetHTML( $var ); }catch(err){".$this->myEditor->setContent($this->name,$var)."} ";
			}
		}
		return $this->myEditor->setContent($this->name,$var);
	}
	function getContent(){
		return $this->myEditor->getContent($this->name);
	}
	function display(){
		if(version_compare(JVERSION,'1.6','<')){
			return $this->myEditor->display( $this->name,  $this->content ,$this->width, $this->height, $this->cols, $this->rows,$this->options ) ;
		}else{
			return $this->myEditor->display( $this->name,  $this->content ,$this->width, $this->height, $this->cols, $this->rows,$this->options,'jform_articletext' ) ;
		}
	}
	function jsCode(){
		return $this->myEditor->save( $this->name );
	}
	function displayCode($name,$content){
		if($this->hasCodeMirror()){
			$this->setEditor('codemirror');
		}else{
			$this->setEditor('none');
		}
		$this->myEditor->setContent($name,$content);
		if(version_compare(JVERSION,'1.6','<')){
			return $this->myEditor->display( $name,  $content ,$this->width, $this->height, $this->cols, $this->rows,false);
		}else{
			return $this->myEditor->display( $name,  $content ,$this->width, $this->height, $this->cols, $this->rows,false,'jform_articletext') ;
		}
	}
	function setEditor($editor=''){
		if(empty($editor)){
			$config =& hikashop_config();
			$this->editor = $config->get('editor',null);
			if(empty($this->editor)) $this->editor = null;
		}else{
			$this->editor = $editor;
		}
		$this->myEditor =& JFactory::getEditor($this->editor);
		$this->myEditor->initialise();
	}
	function hasCodeMirror(){
		static $has = null;
		if(!isset($has)){
			if(version_compare(JVERSION,'1.6','<')){
				$query = 'SELECT element FROM '.hikashop_table('plugins',false).' WHERE element=\'codemirror\' AND folder=\'editors\' AND published=1';
			}else{
				$query = 'SELECT element FROM '.hikashop_table('extensions',false).' WHERE element=\'codemirror\' AND folder=\'editors\' AND enabled=1 AND type=\'plugin\'';
			}
			$db =& JFactory::getDBO();
			$db->setQuery($query);
			$editor = $db->loadResult();
			$has = !empty($editor);
		}
		return $has;
	}
}