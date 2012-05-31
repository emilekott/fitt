<?php
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
	function addRow(){
		var count = parseInt(document.getElementById('count_warehouse').value);
		document.getElementById('count_warehouse').value=count+1;
		var theTable = document.getElementById('warehouse_listing');
		var oldRow = document.getElementById('warehouse_##');
		var rowData = oldRow.cloneNode(true);
		rowData.id = rowData.id.replace(/##/g,count);
		theTable.appendChild(rowData);
		for (var c = 0,m=oldRow.cells.length;c<m;c++){
	        rowData.cells[c].innerHTML = rowData.cells[c].innerHTML.replace(/##/g,count);
	    }
		return false;
	}
</script>
	<tr>
		<td class="key">
			<label for="shipping_tax_id">
				<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
			</label>
		</td>
		<td>
			<?php echo $this->data['categoryType']->display('data[shipping][shipping_tax_id]',@$this->element->shipping_tax_id,true);?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][access_code]">
				<?php echo JText::_( 'UPS_ACCESS_CODE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][access_code]" value="<?php echo @$this->element->shipping_params->access_code; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][user_id]">
				<?php echo JText::_( 'UPS_USER_ID' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][user_id]" value="<?php echo @$this->element->shipping_params->user_id; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][password]">
				<?php echo JText::_( 'PASSWORD' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][password]" value="<?php echo @$this->element->shipping_params->password; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][shipper_number]">
				<?php echo JText::_( 'SHIPPER_NUMBER' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][shipper_number]" value="<?php echo @$this->element->shipping_params->shipper_number; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][pickup_type]">
				<?php echo JText::_( 'PICKUP_TYPE' ); ?>
			</label>
		</td>
		<td>
			<select name="data[shipping][shipping_params][pickup_type]">
				<option <?php if($this->element->shipping_params->pickup_type == '01') echo "selected=\"selected\""; ?> value="01">Daily Pickup</option>
				<option <?php if($this->element->shipping_params->pickup_type == '03') echo "selected=\"selected\""; ?> value="03">Customer Counter</option>
				<option <?php if($this->element->shipping_params->pickup_type == '06') echo "selected=\"selected\""; ?> value="06">One Time Pickup</option>
				<option <?php if($this->element->shipping_params->pickup_type == '07') echo "selected=\"selected\""; ?> value="07">On Call Air</option>
				<option <?php if($this->element->shipping_params->pickup_type == '19') echo "selected=\"selected\""; ?> value="19">Letter Center</option>
				<option <?php if($this->element->shipping_params->pickup_type == '20') echo "selected=\"selected\""; ?> value="20">Air Service Center</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][destination_type]">
				<?php echo JText::_( 'DESTINATION_TYPE' ); ?>
			</label>
		</td>
		<td>
			<select name="data[shipping][shipping_params][destination_type]">
				<option <?php if($this->element->shipping_params->destination_type == 'auto') echo "selected=\"selected\""; ?> value="auto">Auto-determination</option>
				<option <?php if($this->element->shipping_params->destination_type == 'res') echo "selected=\"selected\""; ?> value="res">Residential Address</option>
				<option <?php if($this->element->shipping_params->destination_type == 'com') echo "selected=\"selected\""; ?> value="com">Commercial Address</option>
			</select>
		</td>
	</tr>
	<td>
</table>
</fieldset>
<fieldset>
	<legend><?php echo JText::_( 'WAREHOUSE' ); ?></legend>
	<div style="text-align:right;">
		<button type="button" onclick="return addRow();">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</div>
	<table class="adminlist" cellpadding="1" width="100%" id="warehouse_listing_table">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_( 'NAME' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'POST_CODE' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'COUNTRY' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'ZONE' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'DELETE_ZONE' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'UNITS' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'CURRENCY' ); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'DELETE' ); ?>
				</th>
			</tr>
		</thead>
		<tbody id="warehouse_listing">
		<?php
		$country=hikashop::get('type.country');
		$a = @count($this->element->shipping_params->warehouse);
		if($a){
			for($i = 0;$i<$a;$i++){
				$row =& $this->element->shipping_params->warehouse[$i];
				?>
				<tr class="row0" id="warehouse_<?php echo $i;?>">
					<td>
						<input size="10" type="text" id="warehouse_<?php echo $i;?>_name" name="warehouse[<?php echo $i;?>][name]" value="<?php echo @$row->name; ?>"/>
					</td>
					<td>
						<div id="warehouse_<?php echo $i;?>_zip">
						<input size="10" type="text" id="warehouse_<?php echo $i;?>_zip_input" name="warehouse[<?php echo $i;?>][zip]" value="<?php echo @$row->zip; ?>"/>
						</div>
					</td>
					<td>
						<?php $countryList=$country->display("warehouse[$i][country]", @$row->country, false , "style='width:100px;'"); echo $countryList; ?>
					</td>
					<td align="center">
						<span id="warehouse_<?php echo $i;?>_zone">
							<?php if(!empty($row->zone_name)){ echo $row->zone_name;} ?>
							<input type="hidden" name="warehouse[<?php echo $i;?>][zone]" value="<?php echo @$row->zone ?>"/>
						</span>
						<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop::completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_".$i."_zone&map=warehouse[".$i."][zone]&tmpl=component"); ?>" >
							<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
						</a>
					</td>
					<td align="center">
						<a href="#" onclick="return deleteZone('warehouse_<?php echo $i;?>_zone');">
							<img src="../media/com_hikashop/images/delete.png"/>
						</a>
					</td>
					<td>
						<select id="warehouse_<?php echo $i;?>_units"  name="warehouse[<?php echo $i;?>][units]">
							<option <?php if(@$row->units=='lb')  echo "selected=\"selected\""; ?> value="lb">LB/IN</option>
							<option <?php if(@$row->units=='kg')  echo "selected=\"selected\""; ?> value="kg">KG/CM</option>
						</select>
					</td>
					<td>
						<?php 	$currency=hikashop_get('type.currency');
							 	$currencyList=$currency->display("warehouse[$i][currency]", @$row->currency, 'id="warehouse_'.$i.'_currency"  name="warehouse['.$i.'][currency]"');
								echo $currencyList;
						?>
					</td>
					<td align="center">
						<a href="#" onclick="return deleteRow('warehouse_<?php echo $i;?>_zip','warehouse_<?php echo $i;?>_zip_input','warehouse_<?php echo $i;?>');">
							<img src="../media/com_hikashop/images/delete.png"/>
						</a>
					</td>
				</tr>
			<?php
			}
		}
		?>
		</tbody>
	</table>
	<input type="hidden" name="count_warehouse" value="<?php echo $a;?>" id="count_warehouse" />
	<div style="display:none">
		<table class="adminlist" cellpadding="1" width="100%" id="warehouse_listing_table_row">
			<tr class="row0" id="warehouse_##">
				<td>
					<input size="10" type="text" id="warehouse_##_name" name="warehouse[##][name]" value="-"/>
				</td>
				<td>
					<div id="warehouse_##_zip">
					<input size="10" type="text" id="warehouse_##_zip_input" name="warehouse[##][zip]" value="-"/>
					</div>
				</td>
				<td>
					<?php $countryList=$country->display("warehouse[##][country]", '', false , "style='width:100px;'"); echo $countryList; ?>
				</td>
				<td align="center">
					<span id="warehouse_##_zone">
						<input type="hidden" name="warehouse[##][zone]" value=""/>
					</span>
					<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop::completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_##_zone&map=warehouse[##][zone]&tmpl=component"); ?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;" >
						<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
					</a>
				</td>
				<td align="center">
					<a href="#" onclick="return deleteZone('warehouse_##_zone');">
						<img src="../media/com_hikashop/images/delete.png"/>
					</a>
				</td>
				<td>
					<select id="warehouse_##_units_input" name="warehouse[##][units]">
						<option value="lb">LB/IN</option>
						<option value="kg">KG/CM</option>
					</select>
				</td>
				<td>
					<?php 	$currency=hikashop_get('type.currency');
						 	$currencyList=$currency->display("warehouse[##][currency]", '', 'id="warehouse_##_currency_input" name="warehouse[##][curency]"');
							echo $currencyList;
					?>
				</td>
				<td align="center">
					<a href="#" onclick="return deleteRow('warehouse_##_zip','warehouse_##_zip_input','warehouse_##');">
						<img src="../media/com_hikashop/images/delete.png"/>
					</a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<table>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][services]">
				<?php echo JText::_( 'SHIPPING_SERVICES' ); ?>
			</label>
		</td>
		<td>
			<?php $i=-1; foreach($this->data['ups_methods'] as $method){
					$i++;
					$varName=strtolower($method['name']);
					$varName=str_replace(' ','_', $varName);
				?>
				<input name="data[shipping_methods][<?php echo $varName;?>][name]" type="checkbox" value="<?php echo $varName;?>" <?php echo (!empty($this->element->shipping_params->methods[$varName])?'checked="checked"':''); ?>/><?php echo $method['name'].' ('.$method['countries'].')'; ?><br/>
			<?php	} ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][group_package]">
				<?php echo JText::_( 'GROUP_PACKAGE' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', "data[shipping][shipping_params][group_package]" , '',@$this->element->shipping_params->group_package	); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][include_price]">
				<?php echo JText::_( 'INCLUDE_PRICE' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', "data[shipping][shipping_params][include_price]" , '',@$this->element->shipping_params->include_price	); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][shipping_min_price]">
				<?php echo JText::_( 'SHIPPING_MIN_PRICE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][shipping_min_price]" value="<?php echo @$this->element->shipping_params->shipping_min_price; ?>" />
			<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][shipping_max_price]">
				<?php echo JText::_( 'SHIPPING_MAX_PRICE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][shipping_max_price]" value="<?php echo @$this->element->shipping_params->shipping_max_price; ?>" />
			<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][handling_fees]">
				<?php echo JText::_( 'UPS_HANDLING_FEES' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][handling_fees]" value="<?php echo @$this->element->shipping_params->handling_fees; ?>" />
			<?php  echo $this->data['currency']->currency_code. ' ' .$this->data['currency']->currency_symbol; ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][handling_fees_percent]">
				<?php echo JText::_( 'UPS_PERCENTAGE_HANDLING_FEES' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][handling_fees_percent]" value="<?php echo @$this->element->shipping_params->handling_fees_percent; ?>" /> %
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][weight_approximation]">
				<?php echo JText::_( 'UPS_WEIGHT_APPROXIMATION' ); ?>
			</label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][weight_approximation]" value="<?php echo @$this->element->shipping_params->weight_approximation; ?>" />%
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][dim_approximation]">
				<?php echo JText::_( 'DIMENSION_APPROXIMATION' ); ?>
			</label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][dim_approximation]" value="<?php echo @$this->element->shipping_params->dim_approximation; ?>" />%
		</td>
	</tr>