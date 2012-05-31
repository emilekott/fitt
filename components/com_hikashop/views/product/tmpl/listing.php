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
if(hikashop_level(2) && JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {?>
<script type="text/javascript">
<!--
var compare_list = {length: 0};
function setToCompareList(product_id,name,elem) {
	var compareBtn = document.getElementById('hikashop_compare_button');
	if( compare_list[product_id] ) {
		var old = compare_list[product_id];
		compare_list[product_id] = null;
		compare_list.length--;
		if( elem == null ) elem = old.elem;
		var nn = elem.nodeName.toLowerCase();
		if( nn == 'a' )
			elem.innerHTML = "<?php echo JText::_('ADD_TO_COMPARE_LIST');?>";
		else if( nn == 'input' )
			elem.checked = false;
	} else {
		if(compare_list.length < <?php echo $this->params->get('compare_limit',5); ?> ) {
			compare_list[product_id] = {name: name, elem: elem};
			compare_list.length++;
			var nn = elem.nodeName.toLowerCase();
			if( nn == 'a' )
				elem.innerHTML = "<?php echo JText::_('REMOVE_FROM_COMPARE_LIST');?>";
			else if( nn == 'input' )
				elem.checked = true;
		} else {
			alert("<?php echo JText::_('COMPARE_LIMIT_REACHED');?>");
		}
	}
	if(compare_list.length == 0 ) {
		compareBtn.style.display = 'none';
	} else {
		compareBtn.style.display = '';
	}
	return false;
}
function compareProducts() {
	var url = '';
	for(var k in compare_list) {
		if( compare_list[k] != null && k != 'length' ) {
			if( url == '' )
				url = 'cid[]=' + k;
			else
				url += '&cid[]=' + k;
		}
	}
	window.location = "<?php
		$u = hikashop_completeLink('product&task=compare'.$this->itemid,false,true);
		if( strpos($u,'?')  === false ) {
			echo $u.'?';
		} else {
			echo $u.'&';
		}
	?>" + url;
	return false;
}
window.addEvent('domready', function() {
    $$('input.hikashop_compare_checkbox').each(function(el){
		el.checked = false;
	});
});
//-->
</script>
<?php }
ob_start();
if(version_compare(JVERSION,'1.6','<')){
	$title = 'show_page_title';
}else{
	$title = 'menu_text';
}
$titleType = 'h1';
if($this->module){
	$title = 'showtitle';
	$titleType = 'h2';
}
if($this->params->get($title) && JRequest::getVar('hikashop_front_end_main',0) && (!$this->module || $this->pageInfo->elements->total)){
	$name = $this->params->get('page_title');
	if(($this->module)){
		$name = $this->params->get('title');
	}
	?>
	<<?php echo $titleType; ?>>
	<?php echo $name; ?>
	</<?php echo $titleType; ?>>
	<?php
}
	if(($this->params->get('show_image') && !empty($this->element->file_path))|| ($this->params->get('show_description')&&!empty($this->element->category_description))){
		?>
		<div class="hikashop_category_description">
		<?php
		if($this->params->get('show_image') && !empty($this->element->file_path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($this->image->getPath($this->element->file_path,false))){
			?>
			<img src="<?php echo $this->image->getPath($this->element->file_path); ?>" class="hikashop_category_image"/>
			<?php
			}
		}
		if($this->params->get('show_description')&&!empty($this->element->category_description)){
			?>
			<div class="hikashop_category_description_content">
			<?php echo JHTML::_('content.prepare',$this->element->category_description); ?>
			</div>
			<?php
		}
	?></div><?php
	}
	if(!empty($this->fields)){?>
		<div id="hikashop_category_custom_info_main" class="hikashop_category_custom_info_main">
			<h4><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION');?></h4>
			<table width="100%">
			<?php
			$this->fieldsClass->prefix = '';
			foreach($this->fields as $fieldName => $oneExtraField) {
				if(!empty($this->element->$fieldName)){ ?>
				<tr class="hikashop_category_custom_<?php echo $oneExtraField->field_namekey;?>_line">
					<td class="key">
						<span id="hikashop_category_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_name">
							<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
						</span>
					</td>
					<td>
						<span id="hikashop_category_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_value">
							<?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
						</span>
					</td>
				</tr>
			<?php }
				}?>
			</table>
		</div>
<?php }
static $done = false;
if(hikashop_level(2) && !$done && JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing'){
 	$this->setLayout('filter');
 	echo $this->loadTemplate();
}
$this->setLayout('listing');
$layout_type = $this->params->get('layout_type');
if(empty($layout_type)) $layout_type = 'div';
$html = $this->loadTemplate($layout_type);
if(!empty($html)){ ?>
	<div class="hikashop_products_listing">
		<?php if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) {?>
			<div id="hikashop_compare_zone" class="hikashop_compare_zone">
				<?php
				$empty='';
				$params = new JParameter($empty);
				echo $this->cart->displayButton(JText::_('COMPARE_PRODUCTS'),'compare_button',$params,'#','compareProducts();return false;','style="display:none;" id="hikashop_compare_button"',0,1,' hikashop_compare_button'); ?>
			</div>
		<?php }
		echo $html; ?>
	</div>
<?php }
$html = ob_get_clean();
if(!empty($html)){ ?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="hikashop_category_information hikashop_products_listing_main">
		<?php echo $html; ?>
	</div>
<?php }
if(!$this->module){ ?>
<div class="hikashop_submodules" style="clear:both">
<?php if(!empty($this->modules)){
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module){
			echo JModuleHelper::renderModule($module);
		}
	} ?>
</div>
<?php } ?>
