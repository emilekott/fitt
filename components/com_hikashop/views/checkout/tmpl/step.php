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
<div id="hikashop_checkout_page" class="hikashop_checkout_page">
	<?php
	if(hikashop_level(1)){
		$open_hour = $this->config->get('store_open_hour',0);
		$close_hour = $this->config->get('store_close_hour',0);
		$open_minute = $this->config->get('store_open_minute',0);
		$close_minute = $this->config->get('store_close_minute',0);
		if($open_hour!=$close_hour || $open_minute!=$close_minute){
			function getCurrentDate($format = '%H'){
				if(version_compare(JVERSION,'1.6.0','>=')) $format = str_replace(array('%H','%M'),array('H','i'),$format);
				return (int)JHTML::_('date',time()- date('Z'),$format,null);
			}
			$current_hour = getCurrentDate('%H');
			$current_minute = getCurrentDate('%M');
			$closed=false;
			if($open_hour<$close_hour || ($open_hour==$close_hour && $open_minute<$close_minute)){
				if($current_hour<$open_hour || ($current_hour==$open_hour && $current_minute<$open_minute)){
					$closed=true;
				}
				if($close_hour<$current_hour || ($current_hour==$close_hour && $close_minute<$current_minute)){
					$closed=true;
				}
			}else{
				$closed=true;
				if($current_hour<$close_hour || ($current_hour==$close_hour && $current_minute<$close_minute)){
					$closed=false;
				}
				if($open_hour<$current_hour || ($current_hour==$open_hour && $open_minute<$current_minute)){
					$closed=false;
				}
			}
			if($closed){
				$app=& JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('THE_STORE_IS_ONLY_OPEN_FROM_X_TO_X',$open_hour.':'.sprintf('%02d', $open_minute),$close_hour.':'.sprintf('%02d', $close_minute)));
				echo '</div>';
				return;
			}
		}
	}
	global $Itemid;
	$checkout_itemid = $this->config->get('checkout_itemid');
	if(!empty($checkout_itemid )){
		$Itemid = $checkout_itemid ;
	}
	$url_itemid='';
	if(!empty($Itemid)){
		$url_itemid='&Itemid='.$Itemid;
	}
	if($this->display_checkout_bar){
		?>
			<div id="hikashop_cart_bar" class="hikashop_cart_bar">
			<?php
			$already=true;
			if (count($this->steps) > $this->step+1) $link=true;
			foreach($this->steps as $k => $step){
				$step=explode('_',trim($step));
				$step_name = reset($step);
				if($this->display_checkout_bar==2 && $step_name=='end'){
					continue;
				}
				$class = '';
				if($k==$this->step){
					$already=false;
					$class .= ' hikashop_cart_step_current';
				}
				if($already){
					$class .= ' hikashop_cart_step_finished';
				}
			?>	<div class="hikashop_cart_step<?php echo $class;?>">
					<span>
					<?php if($k==$this->step || empty($link)){ ?>
							<?php echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));?>
					<?php }else{ ?>
						<a href="<?php echo hikashop_completeLink('checkout&task=step&step='.$k.$url_itemid);?>">
							<?php echo JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step_name));?>
						</a>
					<?php } ?>
					</span>
				</div><?php
			}
			?>
			</div>
		<?php
	}
	if(empty($this->noform)){
		?>
		<form action="<?php echo hikashop_completeLink('checkout&task=step&step='.($this->step+1).$url_itemid); ?>" method="post" name="hikashop_checkout_form" enctype="multipart/form-data">
		<?php
	}
	$this->nextButton = true;
	foreach($this->layouts as $layout){
		$layout=trim($layout);
		if($layout=='end'){
			$this->continueShopping='';
		}
		$this->setLayout($layout);
		echo $this->loadTemplate();
	}
	if(empty($this->noform)){
		?>
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
		<input type="hidden" name="option" value="com_hikashop"/>
		<input type="hidden" name="ctrl" value="checkout"/>
		<input type="hidden" name="task" value="step"/>
		<input type="hidden" name="previous" value="<?php echo $this->step; ?>"/>
		<input type="hidden" name="step" value="<?php echo $this->step+1; ?>"/>
		<input type="hidden" id="hikashop_validate" name="validate" value="0"/>
		<?php echo JHTML::_( 'form.token' ); ?>
		<br style="clear:both"/>
		<?php
		if($this->nextButton){
			echo $this->cart->displayButton(JText::_('HIKA_NEXT'),'next',$this->params,hikashop_completeLink('checkout&task=step&step='.($this->step+1)),'if(hikashopCheckChangeForm(\'order\',\'hikashop_checkout_form\')){ if(hikashopCheckMethods()){ document.getElementById(\'hikashop_validate\').value=1; document.forms[\'hikashop_checkout_form\'].submit();}} return false;','id="hikashop_checkout_next_button"');
		}
		?>
		</form>
		<?php
		if($this->continueShopping){
			if(strpos($this->continueShopping,'Itemid')===false){
				if(strpos($this->continueShopping,'index.php?')!==false){
					$this->continueShopping.=$url_itemid;
				}
			}
			if(!preg_match('#^https?://#',$this->continueShopping)) $this->continueShopping = JURI::base().ltrim($this->continueShopping,'/');
			echo $this->cart->displayButton(JText::_('CONTINUE_SHOPPING'),'continue_shopping',$this->params,JRoute::_($this->continueShopping),'window.location=\''.JRoute::_($this->continueShopping).'\';return false;','id="hikashop_checkout_shopping_button"');
		}
	}
	?>
</div>
<div class="clear_both"></div>
<?php
if(JRequest::getWord('tmpl','')=='component'){
	exit;
}