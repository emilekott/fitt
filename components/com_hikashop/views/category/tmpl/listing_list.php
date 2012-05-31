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
if(!empty($this->rows)){
	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_top'; ?>
	<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').'&cid='.$this->pageInfo->filter->cid);?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_subcategories_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php } ?>
	<div class="hikashop_subcategories">
	<?php
	switch($this->params->get('child_display_type')){
		case 'nochild':
		default:
			if(!empty($this->rows)){
			?>
				<ul class="hikashop_category_list<?php echo $this->params->get('ul_class_name'); ?>">
				<?php
					$width = (int)(100/$this->params->get('columns'));
					if(empty($width)){
						$width='';
					}else{
						$width='style="width:'.$width.'%;"';
					}
					$app =& JFactory::getApplication();
					$found = '';
					if(JRequest::getString('option')==HIKASHOP_COMPONENT && in_array(JRequest::getString('ctrl','category'),array('category','product'))){
						$found = $app->getUserState(HIKASHOP_COMPONENT.'.last_category_selected');
						foreach($this->rows as $row){
							if(JRequest::getInt('cid',0) == $row->category_id){
								$found=$row->category_id;
								$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected',$row->category_id);
								break;
							}
						}
					}
					foreach($this->rows as $row){
						$link = hikashop_completeLink('category&task=listing&cid='.$row->category_id.'&name='.$row->alias.$this->menu_id);
						$class = '';
						if($found == $row->category_id){
								$class=' current active';
						}
						?>
						<li class="hikashop_category_list_item<?php echo $class; ?>" <?php echo $width; ?>>
							<a href="<?php echo $link; ?>" >
							<?php echo $row->category_name; ?>
							</a>
						</li>
						<?php
					}
				?>
				</ul>
		<?php
			}
			break;
		case 'allchildsexpand':
			?>
			<div id="category_panel_<?php echo $this->params->get('id');?>" class="pane-sliders">
			<?php
			if(!empty($this->rows)){
				foreach($this->rows as $k => $row){
					$link = hikashop_completeLink('category&task=listing&cid='.$row->category_id.'&name='.$row->alias.$this->menu_id);
					?>
					<div class="panel">
						<h3 class="jpane-toggler title" id="category_pane_<?php echo $k;?>" style="cursor:default;">
							<span>
								<a href="<?php echo $link;?>"><?php echo $row->category_name;?></a>
							</span>
						</h3>
						<div class="jpane-slider content">
							<ul class="hikashop_category_list<?php echo $this->params->get('ul_class_name'); ?>"><?php
							if(!empty($row->childs)){
								$app =& JFactory::getApplication();
								$found='';
								if(JRequest::getString('option')==HIKASHOP_COMPONENT && in_array(JRequest::getString('ctrl','category'),array('category','product'))){
									$found = $app->getUserState(HIKASHOP_COMPONENT.'.last_category_selected');
									foreach($row->childs as $child){
										if(JRequest::getInt('cid',0) == $child->category_id){
											$found=$child->category_id;
											$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected',$child->category_id);
											break;
										}
									}
								}
								foreach($row->childs as $child){
									$link = hikashop_completeLink('category&task=listing&cid='.$child->category_id.'&name='.$child->alias.$this->menu_id);
									$class = '';
									if($found==$child->category_id){
										$class=' current active';
									}
									?>
									<li class="hikashop_category_list_item<?php echo $class; ?>">
										<a href="<?php echo $link; ?>">
										<?php echo $child->category_name; ?>
										</a>
									</li>
									<?php
								}
							}
							?></ul>
						</div>
					</div><?php
				}
			}
			?></div><?php
			break;
		case 'allchilds':
			jimport('joomla.html.pane');
			$found = -1;
			if(JRequest::getString('option')==HIKASHOP_COMPONENT && in_array(JRequest::getString('ctrl','category'),array('category','product')) && $cid = JRequest::getInt('cid',0)){
				$i=0;
				if(!empty($this->rows)){
					foreach($this->rows as $k => $row){
						if($row->category_id==$cid){
							$found = $i;
							break;
						}
						if(!empty($row->childs)){
							foreach($row->childs as $child){
								if($child->category_id==$cid){
									$found = $i;
									break 2;
								}
							}
						}
						$i++;
					}
					$app =& JFactory::getApplication();
					if($found>=0){
						$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected',$found);
					}else{
						$found = (int)$app->getUserState(HIKASHOP_COMPONENT.'.last_category_selected');
					}
				}
			}
			$this->tabs	=& JPane::getInstance('sliders', array('startOffset'=>$found,'startTransition'=>0));
			echo $this->tabs->startPane( 'category_panel_'.$this->params->get('id'));
			if(!empty($this->rows)){
				foreach($this->rows as $k => $row){
					if( !$this->module || $this->params->get('links_on_main_categories')){
						$link = hikashop_completeLink('category&task=listing&cid='.$row->category_id.'&name='.$row->alias.$this->menu_id);
						$row->category_name = '<a href="'.$link.'">'.$row->category_name.'</a>';
					}
					echo $this->tabs->startPanel($row->category_name, 'category_pane_'.$k);
					if(!empty($row->childs)){
						?><ul class="hikashop_category_list<?php echo $this->params->get('ul_class_name'); ?>"><?php
							foreach($row->childs as $child){
								$link = hikashop_completeLink('category&task=listing&cid='.$child->category_id.'&name='.$child->alias.$this->menu_id);
								?>
								<li class="hikashop_category_list_item">
									<a href="<?php echo $link; ?>">
									<?php echo $child->category_name; ?>
									</a>
								</li>
								<?php
							}
						?></ul><?php
					}
					echo $this->tabs->endPanel();
				}
			}
			echo $this->tabs->endPane();
			break;
	}
	?>
	</div>
	<?php if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value){ $this->pagination->form = '_bottom'; ?>
	<form action="<?php echo hikashop_completeLink(JRequest::getWord('ctrl').'&task='.JRequest::getWord('task').'&cid='.$this->pageInfo->filter->cid);?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_subcategories_pagination">
		<?php echo $this->pagination->getListFooter(); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php }
} ?>