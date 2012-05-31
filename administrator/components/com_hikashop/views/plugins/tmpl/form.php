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
<div class="iframedoc" id="iframedoc"></div>
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=plugins" method="post" name="adminForm" enctype="multipart/form-data">
		<?php
		if(empty($this->noForm)){
			$type=$this->plugin_type;
			$upType=strtoupper($type);
			$plugin_name = $type.'_name';
			$plugin_name_input =$plugin_name.'_input';
			$plugin_images = $type.'_images';
			?>
			<table class="admintable">
				<tr>
					<td width="70%" valign="top">
						<fieldset class="adminform" id="htmlfieldset">
							<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
							<?php
								$this->$plugin_name_input = "data[$type][$plugin_name]";
								if($this->translation){
									$this->setLayout('translation');
									echo $this->loadTemplate();
								}else{
									$this->setLayout('normal');
									echo $this->loadTemplate();
								}
							?>
						</fieldset>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td class="key">
									<label for="data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]">
										<?php echo JText::_( 'ZONE' ); ?>
									</label>
								</td>
								<td>
									<span id="zone_id" >
										<?php echo @$this->element->zone_id.' '.@$this->element->zone_name_english;
										$plugin_zone_namekey = $type.'_zone_namekey';
										?>
										<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]" value="<?php echo @$this->element->$plugin_zone_namekey; ?>" />
									</span>
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=".$type,true ); ?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
									</a>
									<a href="#" onclick="document.getElementById('zone_id').innerHTML='<input type=\'hidden\' name=\'data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]\' value=\'\' />';return false;" >
										<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
									</a>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="data[<?php echo $type;?>][<?php echo $type;?>_images]">
										<?php echo JText::_( 'HIKA_IMAGES' ); ?>
									</label>
								</td>
								<td>
									<input type="text" id="plugin_images" name="data[<?php echo $type;?>][<?php echo $type;?>_images]" value="<?php echo @$this->element->$plugin_images; ?>" />
									<a class="modal" id="plugin_images_link" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("plugins&task=selectimages&values=".@$this->element->$plugin_images.'&type='.$type,true ); ?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
									</a>
								</td>
							</tr>
							<?php if($this->plugin_type=='payment'){ ?>
							<tr>
								<td class="key">
									<label for="data[payment][payment_shipping_methods]">
										<?php echo JText::_( 'HIKASHOP_SHIPPING_METHOD' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->shippingMethods->display('data[payment][payment_shipping_methods][]',@$this->element->payment_shipping_methods_type,@$this->element->payment_shipping_methods_id,true,'multiple="multiple" size="3"'); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="data[payment][payment_currency]">
										<?php echo JText::_( 'CURRENCY' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->currencies->display('data[payment][payment_currency][]',@$this->element->payment_currency,'multiple="multiple" size="3"'); ?>
								</td>
							</tr>
							<?php } ?>
							<?php echo $this->content;?>
						</table>
						<fieldset>
							<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
							<?php
							if(hikashop_level(2)){
								$acltype = hikashop_get('type.acl');
								$access = $type.'_access';
								echo $acltype->display($access,@$this->element->$access,$type);
							}else{
								echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
							} ?>
						</fieldset>
					</td>
				</tr>
			</table>
			<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_id]" value="<?php echo $this->id;?>"/>
			<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_type]" value="<?php echo $this->plugin;?>"/>
		<?php
		}else{
			echo $this->content;
		}
		?>
		<input type="hidden" name="task" value="save"/>
		<input type="hidden" name="name" value="<?php echo $this->plugin;?>"/>
		<input type="hidden" name="ctrl" value="plugins" />
		<input type="hidden" name="plugin_type" value="<?php echo $this->plugin_type;?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>