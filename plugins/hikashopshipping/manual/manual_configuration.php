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
<form action="<?php echo hikashop_completeLink('plugins'); ?>" method="post" name="adminForm">
			<table class="adminlist" cellpadding="1">
		    	<thead>
					<tr>
						<th class="title titlenum">
							<?php echo JText::_( 'HIKA_NUM' );?>
						</th>
						<th class="title titlebox">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->data['dbrates']); ?>);" />
						</th>
						<th class="title">
							<?php echo JText::_('HIKA_NAME'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('PRICE'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('RESTRICTIONS'); ?>
						</th>
						<th class="title titleorder">
							<?php
								echo JText::_( 'HIKA_ORDER' );
								if ($this->data['order']->ordering) echo JHTML::_('grid.order',  $this->data['dbrates'] );
							?>
						</th>
						<th class="title">
							<?php echo JText::_('HIKA_DELETE'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('HIKA_PUBLISHED'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$k = 0;
						$i = 0;
						$a = count($this->data['dbrates']);
						$rates = array_values($this->data['dbrates']);
						foreach($rates as $rate){
							$publishedid = 'shipping_published-'.$rate->shipping_id;
							$id='shipping_'.$rate->shipping_id;
							?>
							<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
								<td align="center">
								<?php echo $i+1;?>
								</td>
								<td align="center">
									<?php echo JHTML::_('grid.id', $i, $rate->shipping_id ); ?>
								</td>
								<td>
									<a href="<?php echo hikashop_completeLink('plugins&plugin_type=shipping&task=edit&name='.$this->data['manual'].'&subtask=shipping_edit&shipping_id='.$rate->shipping_id);?>"><?php echo $rate->shipping_name;?></a>
								</td>
								<td align="center">
									<?php
									echo $this->data['currencyHelper']->displayPrices(array($rate),'shipping_price','shipping_currency_id');
									if(isset($rate->shipping_params->shipping_percentage) && bccomp($rate->shipping_params->shipping_percentage,0,3)){
										echo ' +'.$rate->shipping_params->shipping_percentage.'%';
									}
									?>
								</td>
								<td>
									<?php
										$restrictions=array();
										if(!empty($rate->shipping_params->shipping_min_volume)){
											$restrictions[]=JText::_('SHIPPING_MIN_VOLUME').':'.$rate->shipping_params->shipping_min_volume.$rate->shipping_params->shipping_size_unit;
										}
										if(!empty($rate->shipping_params->shipping_max_volume)){
											$restrictions[]=JText::_('SHIPPING_MAX_VOLUME').':'.$rate->shipping_params->shipping_max_volume.$rate->shipping_params->shipping_size_unit;
										}
										if(!empty($rate->shipping_params->shipping_min_weight)){
											$restrictions[]=JText::_('SHIPPING_MIN_WEIGHT').':'.$rate->shipping_params->shipping_min_weight.$rate->shipping_params->shipping_weight_unit;
										}
										if(!empty($rate->shipping_params->shipping_max_weight)){
											$restrictions[]=JText::_('SHIPPING_MAX_WEIGHT').':'.$rate->shipping_params->shipping_max_weight.$rate->shipping_params->shipping_weight_unit;
										}
										if(isset($rate->shipping_params->shipping_min_price) && bccomp($rate->shipping_params->shipping_min_price,0,5)){
											$rate->shipping_min_price=$rate->shipping_params->shipping_min_price;
											$restrictions[]=JText::_('SHIPPING_MIN_PRICE').':'.$this->data['currencyHelper']->displayPrices(array($rate),'shipping_min_price','shipping_currency_id');
										}
										if(isset($rate->shipping_params->shipping_max_price) && bccomp($rate->shipping_params->shipping_max_price,0,5)){
											$rate->shipping_max_price=$rate->shipping_params->shipping_max_price;
											$restrictions[]=JText::_('SHIPPING_MAX_PRICE').':'.$this->data['currencyHelper']->displayPrices(array($rate),'shipping_max_price','shipping_currency_id');
										}
										if(!empty($rate->shipping_params->shipping_zip_prefix)){
											$restrictions[]=JText::_('SHIPPING_PREFIX').':'.$rate->shipping_params->shipping_zip_prefix;
										}
										if(!empty($rate->shipping_params->shipping_min_zip)){
											$restrictions[]=JText::_('SHIPPING_MIN_ZIP').':'.$rate->shipping_params->shipping_min_zip;
										}
										if(!empty($rate->shipping_params->shipping_max_zip)){
											$restrictions[]=JText::_('SHIPPING_MAX_ZIP').':'.$rate->shipping_params->shipping_max_zip;
										}
										if(!empty($rate->shipping_params->shipping_zip_suffix)){
											$restrictions[]=JText::_('SHIPPING_SUFFIX').':'.$rate->shipping_params->shipping_zip_suffix;
										}
										if(!empty($rate->zone_name_english)){
											$restrictions[]=JText::_('ZONE').':'.$rate->zone_name_english;
										}
										echo implode('<br/>',$restrictions);
									?>
								</td>
								<td class="order">
									<span><?php echo $this->data['pagination']->orderUpIcon( $i, $this->data['order']->reverse XOR ( $rate->shipping_ordering >= @$rates[$i-1]->shipping_ordering ), $this->data['order']->orderUp, 'Move Up',$this->data['order']->ordering ); ?></span>
									<span><?php echo $this->data['pagination']->orderDownIcon( $i, $a, $this->data['order']->reverse XOR ( $rate->shipping_ordering <= @$rates[$i+1]->shipping_ordering ), $this->data['order']->orderDown, 'Move Down' ,$this->data['order']->ordering); ?></span>
									<input type="text" name="order[]" size="5" <?php if(!$this->data['order']->ordering) echo 'disabled="disabled"'?> value="<?php echo $rate->shipping_ordering; ?>" class="text_area" style="text-align: center" />
								</td>
								<td align="center">
									<span class="spanloading"><?php echo $this->data['toggleClass']->delete($id,"manual-".$rate->shipping_id,'shipping',true); ?></span>
								</td>
								<td align="center">
									<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->data['toggleClass']->toggle($publishedid,(int) $rate->shipping_published,'shipping') ?></span>
								</td>
							</tr>
							<?php
							$k = 1-$k;
							$i++;
						}
					?>
				</tbody>
	    	</table>
	    	<input type="hidden" name="boxchecked" value="0" />
	    	<input type="hidden" name="subtask" value="copy"/>
	    	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
			<input type="hidden" name="plugin_type" value="shipping" />
			<input type="hidden" name="name" value="manual" />
			<?php echo JHTML::_( 'form.token' ); ?>
</form>