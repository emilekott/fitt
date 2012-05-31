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
	$usefullVars=array(
		'address' =>  $vars['address'],	'address2' =>  $vars['address2'], 'lastname' =>  $vars['lastname'],
		'country' =>  $vars['country'],	'postal_code' =>  $vars['postal_code'],	'city' =>  $vars['city'],
		'state' =>  $vars['state'],	'phone_number' =>  $vars['phone_number'],	'title' =>  $vars['title'],
		'firstname' =>  $vars['firstname'], 'caddie' => $vars['caddie']
	);
	$xCaddie = base64_encode(serialize($usefullVars));
	$parm="merchant_id=". $vars["merchant_id"];
	$parm.=" merchant_country=".$vars["merchant_country"];
	$amount=$vars["amount"];
	$parm.=" amount=".$amount;
	$parm.=" currency_code=".$vars["currency_code"];
	$parm.=" pathfile=".$vars["upload_folder"]."pathfile";



	$parm.=" normal_return_url=".$vars["return_url"];
	$parm.=" cancel_return_url=".$vars["cancel_return_url"];
	$parm.=" automatic_response_url=".$vars["automatic_response_url"];
	$parm.=" language=".$vars["language"];
	$parm.=" payment_means=".$vars["payment_means"];
	$parm.=" header_flag=yes";
	$parm.=" capture_day=".$vars["delay"];
	$parm.=" capture_mode=".$vars["capture_mode"];
	$parm.=" block_align=center";
	$parm.=" block_order=1,2,3,4,5,6,7,8";
	$parm.=" caddie=".$xCaddie;
	$parm.=" customer_id=".$vars["user_id"];
	$parm.=" customer_email=".$vars["customer_email"];
	$parm.=" customer_ip_address=".$vars["customer_ip"];
	$parm.=" order_id=".$vars["caddie"];
	if(!empty($vars["data"])) $parm.=" data=".$vars["data"];
	$os=substr(PHP_OS, 0, 3);
	$os=strtolower($os);
	if($os=='win')
		$path_bin = $vars["bin_folder"]."request.exe";
	else
		$path_bin = $vars["bin_folder"]."request";
	$result=exec("$path_bin $parm");
	$tableau = explode ("!", "$result");
	$code = $tableau[1];
	$error = $tableau[2];
	$message = $tableau[3];
	  if (( $code == "" ) && ( $error == "" ) )
	 	{
	  	print ("<BR><CENTER>erreur appel request</CENTER><BR>");
	  	print ("executable request non trouve $path_bin");
	 	}
		else if ($code != 0){
			print ("<center><b><h2>Erreur appel API de paiement.</h2></center></b>");
			print ("<br /><br /><br />");
			print (" message erreur : $error <br />");
		}
		else {
			print ("<br />");
			print (" $error <br />");
			print (" $message <br />");
		}
	print ("</BODY></HTML>");
?>
