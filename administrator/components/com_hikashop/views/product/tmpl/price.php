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
<script type="text/javascript">
function addPriceRow(){
	var count = parseInt(document.getElementById('count_price').value);
	document.getElementById('count_price').value=count+1;
	var theTable = document.getElementById('price_listing');
	var oldRow = document.getElementById('price_##');
	var rowData = oldRow.cloneNode(true);
	rowData.id = rowData.id.replace(/##/g,count);
	theTable.appendChild(rowData);
	for (var c = 0,m=oldRow.cells.length;c<m;c++){
        rowData.cells[c].innerHTML = rowData.cells[c].innerHTML.replace(/##/g,count);
    }
	return false;
}
</script>
<div style="float:right">
	<button type="button" onclick="return addPriceRow();">
		<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
	</button>
</div>
<br/>
<table class="adminlist" cellpadding="1" width="100%" id="price_listing_table">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_( 'PRICE' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'PRICE_WITH_TAX' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'CURRENCY' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'MINIMUM_QUANTITY' ); ?>
			</th>
			<?php if(hikashop_level(2)){ ?>
			<th class="title">
				<?php echo JText::_( 'ACCESS_LEVEL' ); ?>
			</th>
			<?php } ?>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="price_listing">
	<?php
		$a = @count($this->element->prices);
		if($a){
			for($i = 0;$i<$a;$i++){
				$row =& $this->element->prices[$i];
					if(empty($row->price_min_quantity)){
						$row->price_min_quantity = 1;
					}
				?>
				<tr class="row0" id="price_<?php echo $i;?>">
					<td>
						<input size="10" type="text" id="price[price_value][<?php echo $i;?>]" name="price[price_value][<?php echo $i;?>]" value="<?php echo @$row->price_value; ?>" onchange="updatePrice('price_with_tax_<?php echo $i;?>',this.value,this.form['data[product][product_tax_id]'].value,0);" />
					</td>
					<td>
						<input size="10" type="text" id="price_with_tax_<?php echo $i;?>" name="price_with_tax_<?php echo $i;?>" value="<?php echo @$row->price_value_with_tax; ?>" onchange="updatePrice('price[price_value][<?php echo $i;?>]',this.value,this.form['data[product][product_tax_id]'].value,1);"/>
					</td>
					<td>
						<?php echo @$this->currency->display('price[price_currency_id]['.$i.']',@$row->price_currency_id); ?>
					</td>
					<td>
						<input size="3" type="text" id="price[price_min_quantity][<?php echo $i;?>]" name="price[price_min_quantity][<?php echo $i;?>]" value="<?php echo @$row->price_min_quantity; ?>" />
					</td>
					<?php if(hikashop_level(2)){ ?>
					<td>
						<?php if(!empty($row->price_id)){ ?>
						<a onclick="el = document.getElementById('price_access_<?php echo $i;?>'); if(el){ this.href='<?php echo hikashop_completeLink('product&task=priceaccess&id='.$i,true);?>&access='+el.value; SqueezeBox.fromElement(this,{parse:'rel'}); } return false;" href="#" rel="{handler: 'iframe', size: {x: 380, y: 360}}">
							<img src="<?php echo HIKASHOP_IMAGES.'icons/icon-16-levels.png'?>" title="<?php echo JText::_('ACCESS_LEVEL');?>" />
						</a>
						<input type="hidden" id="price_access_<?php echo $i;?>" name="price[price_access][<?php echo $i;?>]" value="<?php echo @$row->price_access; ?>" />
						<?php }else{echo '--';}?>
					</td>
					<?php } ?>
					<td>
						<?php
						if(!empty($row->price_id)){
							echo $row->price_id. '<input type="hidden" id="price[price_id]['.$i.']" name="price[price_id]['.$i.']" value="'.$row->price_id.'" />';
						}else{
							echo '--';
						} ?>
					</td>
				</tr>
			<?php
			}
		}
		?>
	</tbody>
</table>
<input type="hidden" name="count_price" value="<?php echo $a;?>" id="count_price" />
<div style="display:none">
	<table class="adminlist" cellpadding="1" width="100%" id="price_listing_table_row">
		<tr class="row0" id="price_##">
			<td>
				<input size="10" type="text" id="price[price_value][##]" name="price[price_value][##]" value="0" onchange="updatePrice('price_with_tax_##',this.value,this.form['data[product][product_tax_id]'].value,0);" />
			</td>
			<td>
				<input size="10" type="text" id="price_with_tax_##" name="price_with_tax_##" value="0" onchange="updatePrice('price[price_value][##]',this.value,this.form['data[product][product_tax_id]'].value,1);"/>
			</td>
			<td>
				<?php echo @$this->currency->display('price[price_currency_id][##]',0); ?>
			</td>
			<td>
				<input type="text" size="3" id="price[price_min_quantity][##]" name="price[price_min_quantity][##]" value="1" />
			</td>
			<?php if(hikashop_level(2)){ ?>
			<td>
				--
			</td>
			<?php } ?>
			<td>
				--
			</td>
		</tr>
	</table>
</div>
