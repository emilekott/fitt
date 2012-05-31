<?php
defined('_JEXEC') or die('Restricted access');
function ShowError () {
	echo "<html><head><title>Results</title></head><body><table width=100% height=50%><tr><td><p><h2><center>Compruebe que todos los datos del formulario son correctos!!</center></h2></p></td></tr></table></body></html>\n";
} # End of function ShowError
function ShowForm ($amount,$currency,$producto,$id_pedido,$methods,$method_id) {
$method =& $methods[$method_id];
$url_tpvv=$method->payment_params->url;
$merchantName=$method->payment_params->merchantName;
$clave=$method->payment_params->encriptionKey;
$code=$method->payment_params->merchantId;
$terminal=$method->payment_params->terminalId;
$url_OK=HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=$id_pedido";
$url_KO=HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=$id_pedido";
$currency='978';
$transactionType='0';
$urlMerchant=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=servired';
$order= '00'.$id_pedido;
 
echo "
<script language=JavaScript>
function calc() {
document.getElementById('compra').submit();
}
</script>
<form id=compra name=compra action=$url_tpvv method=post target=_self>
<pre>";
echo "
<input type=hidden name=Ds_Merchant_Amount value='$amount'>
<input type=hidden name=Ds_Merchant_Currency value='$currency'>
<input type=hidden name=Ds_Merchant_Order  value='$order'>
<input type=hidden name=Ds_Merchant_MerchantCode value='$code'>
<input type=hidden name=Ds_Merchant_Terminal value='$terminal'>
<input type=hidden name=Ds_Merchant_TransactionType value='$transactionType'>
<input type=hidden name=Ds_Merchant_MerchantURL value='$urlMerchant'>
<input type=hidden name=Ds_Merchant_UrlOK value='$url_OK'>
<input type=hidden name=Ds_Merchant_UrlKO value='$url_KO'>";

$message = $amount.$order.$code.$currency.$transactionType.$urlMerchant.$clave;
$signature = strtoupper(sha1($message));

echo "<input type=hidden name=Ds_Merchant_MerchantSignature value='$signature'>
<center>
<span class='art-button-wrapper'>
<span class='art-button-l'> </span>
<span class='art-button-r'> </span>
<a class='button art-button' href='javascript:calc()'><img src='/tpvirtual.jpg' border=0 ALT='Ir al TPV Virtual'></a></span></center>
</pre>
</form>
";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration("window.addEvent('domready', function() {document.getElementById('compra').submit();});");

} # End of function ShowForm
?>

<div class="hikashop_servired_end" id="hikashop_servired_end">
	<span id="hikashop_servired_end_message" class="hikashop_servired_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_servired_end_spinner" class="hikashop_servired_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
   <?PHP

      ShowForm($amount_total,'978','mi producto',$id_pedido,$methods,$method_id) ;
?>

</div>