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
class hikashopViewClass extends hikashopClass{
	function saveForm(){
		$id = JRequest::getString('id');
		$element = $this->get($id);
		if(!$element) return false;
		$element->content = JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$result = $this->save($element);
		return $result;
	}
	function save(&$element){
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		jimport('joomla.filesystem.file');
		$result = JFile::write($element->override, $element->content);
		if (!$result){
			if(!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('FILE_NOT_WRITABLE',$element->override));
			}
			$result = JFile::write($element->override, $element->content);
			if (!$ftp['enabled']) {
				JPath::setPermissions($element->override, '0555');
			}
		}
		return $result;
	}
	function delete(&$id){
		$element = $this->get(reset($id));
		if(!$element){
			return false;
		}
		jimport('joomla.filesystem.file');
		if(!JFile::exists($element->override)){
			return true;
		}
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		if (!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('FILE_NOT_WRITABLE',$element->override));
		}
		$result = JFile::delete($element->override);
		return $result;
	}
	function get($id){
		$parts = explode('|',$id);
		if(count($parts)!=6){
			return false;
		}
		$obj = null;
		$obj->id = $id;
		$obj->client_id = (int)$parts[0];
		$obj->template = $parts[1];
		$obj->type = $parts[2];
		$obj->type_name = $parts[3];
		$obj->view = $parts[4];
		$obj->filename = $parts[5];
		if($obj->type=='plugin'){
			$obj->folder = rtrim(JPATH_PLUGINS,DS).DS.$obj->type_name.DS;
		}else{
			switch($obj->client_id){
				case 0:
					$view = HIKASHOP_FRONT.'views'.DS;
					break;
				case 1:
					$view = HIKASHOP_BACK.'views'.DS;
					break;
				default:
					return false;
			}
			$obj->folder = $view.$obj->view.DS.'tmpl'.DS;
		}
		$obj->path = $obj->folder.$obj->filename;
		$obj->file = substr($obj->filename,0,strlen($obj->filename)-4);
		$client	=& JApplicationHelper::getClientInfo($obj->client_id);
		$tBaseDir = $client->path.DS.'templates';
		$templateFolder = $tBaseDir.DS.$obj->template.DS;
		$obj->override = $templateFolder.'html'.DS.$obj->type_name.DS;
		if($obj->type=='component'){
			$obj->override .= $obj->view.DS;
		}
		$obj->override .= $obj->filename;
		$obj->overriden=false;
		if(file_exists($obj->override)){
			$obj->overriden=true;
			$obj->edit = $obj->override;
		}else{
			$obj->edit = $obj->path;
		}
		return $obj;
	}
}