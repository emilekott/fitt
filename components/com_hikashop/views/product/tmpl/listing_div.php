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
$mainDivName=$this->params->get('main_div_name');
$carouselEffect=$this->params->get('carousel_effect');
$enableCarousel=$this->params->get('enable_carousel');
$textCenterd=$this->params->get('text_center');
$this->align="left";
if($textCenterd){
	$this->align="center";
}
$height=$this->params->get('image_height');
$width=$this->params->get('image_width');
$this->borderClass="";
if($this->params->get('border_visible',1)){
	$this->borderClass="hikashop_subcontainer_border";
}
if(empty($width) && empty($height)){
 	$width=$this->image->main_thumbnail_x;
  	$height=$this->image->main_thumbnail_y;
}
$exists=false;
if(!empty($this->rows)){
	$row=reset($this->rows);
	if(!empty($row->file_path)){
		jimport('joomla.filesystem.file');
		if(JFile::exists($row->file_path)){
			$exists=true;
		}else{
			$exists=false;
		}
	}
}
if(!$exists){
	$config =& hikashop::config();
	$path = $config->get('default_image');
	if($path == 'barcode.png'){
		$file_path=HIKASHOP_MEDIA.'images'.DS.'barcode.png';
	}
	if(!empty($path)){
		jimport('joomla.filesystem.file');
		if(JFile::exists($this->image->main_uploadFolder.$path)){
			$exists=true;
		}
	}else{
		$exists=false;
	}
	if($exists){
		$file_path=$this->image->main_uploadFolder.$path;
	}
}else{
	$file_path=$this->image->main_uploadFolder.$row->file_path;
}
if(!empty($file_path)){
	if(empty($width)){
	 	$imageHelper=hikashop_get('helper.image');
	  	list($theImage->width, $theImage->height) = getimagesize($file_path);
	  	list($width, $height) = $this->scaleImage($theImage->width, $theImage->height, 0, $height);
	}
	if(empty($height)){
	 	$imageHelper=hikashop_get('helper.image');
	  	list($theImage->width, $theImage->height) = getimagesize($file_path);
		list($width, $height) = $this->scaleImage($theImage->width, $theImage->height, $width, 0);
	}
}
$this->newSizes->height=$height;
$this->newSizes->width=$width;
$this->image->main_thumbnail_y=$height;
$this->image->main_thumbnail_x=$width;
if(!empty($this->rows)){
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_top';?>
	<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').$this->itemid.'&cid='.reset($this->pageInfo->filter->cid)); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_products_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php } ?>
	<div class="hikashop_products">
	<?php
	if($enableCarousel){
		$this->setLayout('carousel');
		echo $this->loadTemplate();
	}
	else{
		$columns = (int)$this->params->get('columns');
		if(empty($columns)) $columns = 1;
		$width = (int)(100/$columns)-1;
		$current_column = 1;
		foreach($this->rows as $row){
			?>
			<div class="hikashop_product" style="width:<?php echo $width;?>%;">
				<div class="hikashop_container">
					<div class="hikashop_subcontainer <?php echo $this->borderClass; ?>">
					<?php
						$this->row =& $row;
						$this->setLayout('listing_'.$this->params->get('div_item_layout_type'));
						echo $this->loadTemplate();
					?>
					</div>
				</div>
			</div>
			<?php if($current_column>=$columns){ ?>
				<div style="clear:both"></div>
			<?php
				$current_column=0;
			}
			$current_column++;
		}
	}
	?><div style="clear:both"></div>
	</div>
	<?php if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_bottom'; ?>
	<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').$this->itemid.'&cid='.reset($this->pageInfo->filter->cid)); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_products_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php }
}
?>