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
function com_install(){
	include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');
	$lang =& JFactory::getLanguage();
	$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
	$installClass = new hikashopInstall();
	$installClass->addPref();
	$installClass->updatePref();
	$installClass->updateSQL();
	$installClass->displayInfo();
}
class hikashopInstall{
	var $level = 'starter';
	var $version = '1.5.5';
	var $update = false;
	var $fromLevel = '';
	var $fromVersion = '';
	var $db;
	function hikashopInstall(){
		$this->db =& JFactory::getDBO();
	}
	function displayInfo(){
		unset($_SESSION['hikashop']['li']);
		echo '<h1>Please wait... </h1><h2>HikaShop will now automatically install the Plugins and the Modules</h2>';
		$url = 'index.php?option=com_hikashop&ctrl=update&task=install&update='.(int)$this->update;
		echo '<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
		echo "<script language=\"javascript\" type=\"text/javascript\">document.location.href='$url';</script>\n";
	}
	function updatePref(){
		$this->db->setQuery("SELECT `config_namekey`, `config_value` FROM `#__hikashop_config` WHERE `config_namekey` IN ('version','level') LIMIT 2");
		$results = $this->db->loadObjectList('config_namekey');
		if($results['version']->config_value == $this->version AND $results['level']->config_value == $this->level) return true;
		$this->update = true;
		$this->fromLevel = $results['level']->config_value;
		$this->fromVersion = $results['version']->config_value;
		$query = "REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`) VALUES ('level',".$this->db->Quote($this->level)."),('version',".$this->db->Quote($this->version)."),('installcomplete','0')";
		$this->db->setQuery($query);
		$this->db->query();
	}
	function updateSQL(){
		if(!$this->update) return true;
		if(version_compare($this->fromVersion,'1.0.2','<')){
			$query = 'UPDATE `#__hikashop_user` AS a LEFT JOIN `#__hikashop_user` AS b ON a.user_email=b.user_email SET a.user_email=CONCAT(\'old_\',a.user_email) WHERE a.user_id>b.user_id';
			$this->db->setQuery($query);
			$this->db->query();
			$query = 'ALTER TABLE `#__hikashop_user` ADD UNIQUE (`user_email`)';
			$this->db->setQuery($query);
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.1.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_max_per_order` INT UNSIGNED DEFAULT 0");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.3.4','<')){
			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_discount`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'discount_auto_load'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_auto_load` TINYINT UNSIGNED DEFAULT 0");
				$this->db->query();
			}
		}
		if(version_compare($this->fromVersion,'1.3.3','>') && version_compare($this->fromVersion,'1.3.6','<')){
			$this->db->setQuery("DELETE FROM `#__modules` WHERE module='HikaShop Content Module' OR  module='HikaShop Cart Module' OR  module='HikaShop Currency Switcher Module'");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.1','<')){
			$rand=rand(0,999999999);
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload',`config_default` = 'media/com_hikashop/upload' WHERE `config_namekey` = 'uploadfolder' AND `config_value` LIKE 'components/com_hikashop/upload%' ");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe',`config_default` = 'media/com_hikashop/upload/safe' WHERE `config_namekey` = 'uploadsecurefolder' AND `config_value` LIKE 'components/com_hikashop/upload/safe%' ");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log',`config_default` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log' WHERE `config_namekey` IN ('cron_savepath','payment_log_file') ");
			$this->db->query();
			$updateClass = hikashop_get('helper.update');
			$removeFiles = array();
			$removeFiles[] = HIKASHOP_FRONT.'css'.DS.'backend_default.css';
			$removeFiles[] = HIKASHOP_FRONT.'css'.DS.'frontend_default.css';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'cron_report.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_admin_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_status_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_status_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account.html.php';
			foreach($removeFiles as $oneFile){
				if(is_file($oneFile)) JFile::delete($oneFile);
			}
			$fromFolders = array();
			$toFolders = array();
			$fromFolders[] = HIKASHOP_FRONT.'css';
			$toFolders[] = HIKASHOP_MEDIA.'css';
			$fromFolders[] = HIKASHOP_FRONT.'mail';
			$toFolders[] = HIKASHOP_MEDIA.'mail';
			$fromFolders[] = HIKASHOP_FRONT.'upload';
			$toFolders[] = HIKASHOP_MEDIA.'upload';
			foreach($fromFolders as $i => $oneFolder){
				if(!is_dir($oneFolder)) continue;
				if(is_dir($toFolders[$i]) || !@rename($oneFolder,$toFolders[$i])) $updateClass->copyFolder($oneFolder,$toFolders[$i]);
			}
			$deleteFolders = array();
			$deleteFolders[] = HIKASHOP_FRONT.'css';
			$deleteFolders[] = HIKASHOP_FRONT.'images';
			$deleteFolders[] = HIKASHOP_FRONT.'js';
			foreach($deleteFolders as $oneFolder){
				if(!is_dir($oneFolder)) continue;
				JFolder::delete($oneFolder);
			}
		}
		if(version_compare($this->fromVersion,'1.4.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_price` ADD `price_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_zone` ADD `zone_currency_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			if(version_compare(JVERSION,'1.6.0','<')){
				$query = 'UPDATE `#__plugins` SET `published`=0 WHERE  `element`=\'geolocation\' AND `folder`=\'hikashop\'';
			}else{
				$query = 'UPDATE `#__extensions` SET `enabled`=0 WHERE  `element`=\'geolocation\' AND `folder`=\'hikashop\'';
			}
			$this->db->setQuery($query);
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.5','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_group_after_purchase` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_contact` SMALLINT UNSIGNED DEFAULT 0");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.6','<')){
			$this->db->setQuery('ALTER TABLE `#__hikashop_product_related` DROP PRIMARY KEY ,
ADD PRIMARY KEY (  `product_id` ,  `product_related_id` ,  `product_related_type` )');
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_min_per_order` INT UNSIGNED DEFAULT 0");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.7','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_shipping` ADD `shipping_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.8','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_history` ADD `history_user_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_tax_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_shipping_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_discount_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.9','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_number` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("SELECT order_id,order_created FROM ".hikashop_table('order').' WHERE order_number=\'\'');
			$orders = $this->db->loadObjectList();
			if(!empty($orders)){
				foreach($orders as $k => $order){
					$orders[$k]->order_number = hikashop_encode($order);
				}
				$i = 0;
				$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_order_number` (`order_id` int(10) unsigned NOT NULL DEFAULT '0',`order_number` VARCHAR( 255 ) NOT NULL DEFAULT '') ENGINE=MyISAM ;");
				$this->db->query();
				$inserts = array();
				foreach($orders as $k => $order){
					$i++;
					$inserts[]='('.$order->order_id.','.$this->db->Quote($order->order_number).')';
					if($i >= 500){
						$i=0;
						$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
						$this->db->query();
						$inserts = array();
					}
				}
				$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
				$this->db->query();
				$this->db->setQuery('UPDATE `#__hikashop_order` AS a , `#__hikashop_order_number` AS b SET a.order_number=b.order_number WHERE a.order_id=b.order_id AND a.order_number=\'\'');
				$this->db->query();
				$this->db->setQuery('DROP TABLE IF EXISTS `#__hikashop_order_number`');
				$this->db->query();
			}
		}
		if(version_compare($this->fromVersion,'1.5.0','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$columnsTable = $this->db->getTableFields(hikashop_table('product'));
			$columns = reset($columnsTable);
			$found = false;
			foreach($columns as $i => $oneColumn){
				if($oneColumn=="product_contact"){
					$found = true;
				}
			}
			if(!$found){
				$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_min_per_order` INT UNSIGNED DEFAULT 0");
				$this->db->query();
			}
			if(version_compare(JVERSION,'1.6.0','<')){
				$query = 'UPDATE `#__plugins` SET `published`=0 WHERE  `element`=\'hikashop\' AND `folder`=\'user\'';
			}else{
				$query = 'UPDATE `#__extensions` SET `enabled`=0 WHERE  `element`=\'hikashop\' AND `folder`=\'user\'';
			}
			$this->db->setQuery($query);
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_minimum_products` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_quota_per_user` INT UNSIGNED DEFAULT 0");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.5.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_keywords` VARCHAR(255) NOT NULL");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_meta_description` varchar(155) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product_related` ADD `product_related_ordering` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_last_seen_date` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_file` ADD `file_free_download` tinyint(3) unsigned NOT NULL DEFAULT '0'");
			$this->db->query();
			$manufacturer = null;
			$manufacturer->category_type = 'manufacturer';
			$manufacturer->category_name = 'manufacturer';
			$class = hikashop_get('class.category');
			$class->save($manufacturer);
		}
		if(version_compare($this->fromVersion,'1.5.3','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_limit` (
  `limit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `limit_product_id` int(11) NOT NULL DEFAULT '0',
  `limit_category_id` int(11) NOT NULL DEFAULT '0',
  `limit_per_product` tinyint(4) NOT NULL DEFAULT '0',
  `limit_periodicity` varchar(255) NOT NULL DEFAULT '',
  `limit_type` varchar(255) NOT NULL DEFAULT '',
  `limit_value` int(10) NOT NULL DEFAULT '0',
  `limit_unit` varchar(255) DEFAULT NULL,
  `limit_currency_id` int(11) NOT NULL DEFAULT '0',
  `limit_access` varchar(255) NOT NULL DEFAULT '',
  `limit_status` varchar(255) NOT NULL DEFAULT '',
  `limit_published` tinyint(4) NOT NULL DEFAULT '0',
  `limit_created` int(10) DEFAULT NULL,
  `limit_modified` int(10) DEFAULT NULL,
  `limit_start` int(10) DEFAULT NULL,
  `limit_end` int(10) DEFAULT NULL,
  PRIMARY KEY (`limit_id`)
) ENGINE=MyISAM ;");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_zone` ADD INDEX (  `zone_code_3` )");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_sales` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_categories` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_with_sub_categories` TINYINT( 1 ) NOT NULL DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_payment` ADD  `payment_shipping_methods` TEXT NOT NULL DEFAULT  ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_cart_product` ADD `cart_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_order_product` ADD `order_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_taxation` ADD `taxation_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$class = hikashop_get('class.category');
			$tax = null;
			$tax->category_type = 'tax';
			$tax->category_parent_id = 'tax';
			$class->getMainElement($tax->category_parent_id);
			$tax->category_name = 'Default tax category';
			$tax->category_namekey = 'default_tax';
			$tax->category_depth = 2;
			$class->save($tax);
		}
		if(version_compare($this->fromVersion,'1.5.4','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_filter` (
  `filter_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `filter_name` varchar(250) NOT NULL,
  `filter_namekey` varchar(50) NOT NULL,
  `filter_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `filter_type` varchar(50) DEFAULT NULL,
  `filter_category_id` int(10) unsigned NOT NULL,
  `filter_ordering` smallint(5) unsigned DEFAULT '99',
  `filter_options` text,
  `filter_data` text NOT NULL,
  `filter_access` varchar(250) NOT NULL DEFAULT 'all',
  `filter_direct_application` tinyint(3) NOT NULL DEFAULT '0',
  `filter_value` text NOT NULL,
  `filter_category_childs` tinyint(3) unsigned NOT NULL,
  `filter_height` int(50) unsigned NOT NULL,
  `filter_deletable` tinyint(3) unsigned NOT NULL,
  `filter_dynamic` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM ;");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_currency` VARCHAR( 255 ) NOT NULL");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.5.5','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_waitlist` (
  `waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `date` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `product_item_id` int(11) NOT NULL,
  PRIMARY KEY (`waitlist_id`)
) ENGINE=MyISAM ;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_product` ADD `product_waitlist` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_discount` ADD `discount_coupon_nodoubling` TINYINT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_discount` ADD `discount_coupon_product_only` TINYINT NULL;");
			$this->db->query();
		}
	}
	function addPref(){
		$conf	=& JFactory::getConfig();
		$this->level = ucfirst($this->level);
		$allPref = array();
		$allPref['level'] =  $this->level;
		$allPref['version'] = $this->version;
		$allPref['from_name'] = $conf->getValue('config.fromname');
		$allPref['from_email'] = $conf->getValue('config.mailfrom');
		$allPref['reply_name'] = $conf->getValue('config.fromname');
		$allPref['reply_email'] =  $conf->getValue('config.mailfrom');
		$allPref['bounce_email'] =  '';
		$allPref['add_names'] = '1';
		$allPref['encoding_format'] =  'base64';
		$allPref['charset'] = 'UTF-8';
		$allPref['word_wrapping'] = '150';
		$allPref['embed_images'] = '0';
		$allPref['embed_files'] = '1';
		$allPref['multiple_part'] =  '1';
		$allPref['allowedfiles'] = 'zip,doc,docx,pdf,xls,txt,gz,gzip,rar,jpg,gif,xlsx,pps,csv,bmp,epg,ico,odg,odp,ods,odt,png,ppt,swf,xcf,wmv,avi,mkv,mp3,ogg,flac,wma,fla,flv,mp4,wav,aac,mov,epub';
		$allPref['allowedimages'] = 'gif,jpg,jpeg,png';
		$allPref['uploadfolder'] = 'media/com_hikashop/upload/';
		$allPref['uploadsecurefolder'] = 'media/com_hikashop/upload/safe/';
		$allPref['editor'] =  '0';
		$allPref['cron_next'] = '1251990901';
		$allPref['cron_last'] =  '0';
		$allPref['cron_fromip'] = '';
		$allPref['cron_report'] = '';
		$allPref['cron_frequency'] = '900';
		$allPref['cron_sendreport'] =  '2';
		$allPref['payment_notification_email'] = $allPref['order_creation_notification_email'] = $allPref['cron_sendto'] = $conf->getValue('config.mailfrom');
		$allPref['cron_fullreport'] =  '1';
		$allPref['cron_savereport'] =  '2';
		$allPref['cron_savepath'] =  'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log';
		$allPref['payment_log_file'] =  'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log';
		$allPref['notification_created'] =  '';
		$allPref['notification_accept'] =  '';
		$allPref['notification_refuse'] = '';
		$descriptions = array('Joomla!™ Shopping Cart Extension','Joomla!™ E-Commerce Extension','Joomla!™ Online Shop System','Joomla!™ Online Store Component');
		$allPref['description_starter'] = $descriptions[rand(0,3)];
		$allPref['description_essential'] = $descriptions[rand(0,3)];
		$allPref['description_business'] = $descriptions[rand(0,3)];
		$allPref['opacity'] = '100';
		$allPref['order_number_format'] = '{automatic_code}';
		$allPref['checkout_cart_delete'] = '1';
		$allPref['variant_default_publish'] = '1';
		$allPref['force_ssl'] = '0';
		$allPref['simplified_registration'] = '0';
		$allPref['tax_zone_type'] = 'billing';
		$allPref['discount_before_tax'] = '0';
		$allPref['default_type'] = 'individual';
		$allPref['main_tax_zone'] = '1375';
		$allPref['main_currency'] = '1';
		$allPref['order_status_for_download'] = 'shipped,confirmed';
		$allPref['download_time_limit'] = '2592000';
		$allPref['click_validity_period'] = '2592000';
		$allPref['click_min_delay'] = '86400';
		$allPref['partner_currency'] = '1';
		$allPref['allow_currency_selection'] = '0';
		$allPref['partner_click_fee'] = '0';
		$allPref['partner_lead_fee'] = '0';
		$allPref['ajax_add_to_cart'] ='0';
		$allPref['partner_percent_fee'] = '0';
		$allPref['partner_flat_fee'] = '0';
		$allPref['affiliate_terms'] = '';
		$allPref['order_created_status'] = 'created';
		$allPref['order_confirmed_status'] = 'confirmed';
		$allPref['download_number_limit'] = '50';
		$allPref['button_style'] = 'normal';
		$allPref['partner_valid_status'] = 'confirmed,shipped';
		$allPref['readmore'] = '0';
		$allPref['menu_style'] = 'title_bottom';
		$allPref['show_cart_image'] = '1';
		$allPref['thumbnail'] = '1';
		$allPref['thumbnail_x'] = '100';
		$allPref['thumbnail_y'] = '100';
		$allPref['image_x'] = '';
		$allPref['image_y'] = '';
		$allPref['max_x_popup'] = '760';
		$allPref['max_y_popup'] = '480';
		$allPref['vat_check'] = '0';
		$allPref['default_translation_publish'] = '0';
		$allPref['multilang_display'] = 'popups';
		$allPref['volume_symbols'] = 'm,dm,cm,mm,in,ft,yd';
		$allPref['weight_symbols'] = 'kg,g,mg,lb,oz,ozt';
		$allPref['store_address'] = "ACME Corporation\nGuildhall\n PO Box 270, London\nUnited Kingdom";
		$allPref['checkout'] = 'login_address_shipping_payment_confirm_coupon_cart_status_fields,end';
		$allPref['display_checkout_bar'] = '0';
		$allPref['affiliate_advanced_stats'] = '1';
		$allPref['cart_retaining_period'] = '2592000';
		$allPref['default_params'] = '';
		$allPref['default_image'] = 'barcode.png';
		$allPref['product_show_modules'] = '';
		$allPref['characteristic_display'] = 'dropdown';
		$allPref['characteristic_display_text'] = '1';
		$allPref['show_quantity_field'] = '1';
		$allPref['show_cart_price'] = '1';
		$allPref['show_cart_quantity'] = '1';
		$allPref['show_cart_delete'] = '1';
		$allPref['catalogue'] = '0';
		$allPref['redirect_url_after_add_cart'] = 'stay_if_cart';
		$allPref['redirect_url_when_cart_is_empty'] = '';
		$allPref['cart_retaining_period_checked'] = '1278664651';
		$allPref['auto_submit_methods'] = '1';
		$allPref['clean_cart_when_order_created'] = 'order_confirmed';
		$allPref['default_params'] = base64_encode('a:31:{s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";s:1:"0";s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";}');
		$allPref['category_image'] = 1;//not changeable yet
		$allPref['category_explorer'] = 1;
		$allPref['cancelled_order_status'] = 'cancelled,refunded';
		$allPref['order_status_notification.subject'] = 'ORDER_STATUS_NOTIFICATION_SUBJECT';
		$allPref['order_creation_notification.subject'] = 'ORDER_CREATION_NOTIFICATION_SUBJECT';
		$allPref['order_notification.subject'] = 'ORDER_NOTIFICATION_SUBJECT';
		$allPref['user_account.subject'] = 'USER_ACCOUNT_SUBJECT';
		$allPref['cron_report.subject'] = 'CRON_REPORT_SUBJECT';
		$allPref['order_status_notification.html']=1;
		$allPref['order_status_notification.published']=1;
		$allPref['order_creation_notification.html']=1;
		$allPref['order_creation_notification.published']=1;
		$allPref['order_notification.html']=1;
		$allPref['order_notification.published']=1;
		$allPref['order_admin_notification.html']=1;
		$allPref['order_admin_notification.subject'] = ' ';
		$allPref['order_admin_notification.published']=1;
		$allPref['unfinished_order.published']=1;
		$allPref['user_account.html']=1;
		$allPref['out_of_stock.html']=1;
		$allPref['out_of_stock.subject']='OUT_OF_STOCK_NOTIFICATION_SUBJECT';
		$allPref['user_account.published']=1;
		$allPref['cron_report.html']=1;
		$allPref['cron_report.published']=1;
		$allPref['out_of_stock.published']=1;
		$allPref['waitlist_notification.html']=1;
		$allPref['waitlist_notification.subject'] = 'WAITLIST_NOTIFICATION_SUBJECT';
		$allPref['waitlist_notification.published']=1;
		$allPref['show_footer'] = '1';
		$allPref['css_module'] = 'default';
		$allPref['css_frontend'] = 'default';
		$allPref['css_backend'] = 'default';
		$allPref['installcomplete'] = '0';
		$allPref['Starter'] =  '0';
		$allPref['Essential'] =  '1';
		$allPref['Business'] =  '2';
		$allPref['Enterprise'] =  '3';
		$allPref['Unlimited'] =  '9';
		$query = "INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES ";
		foreach($allPref as $namekey => $value){
			$query .= '('.$this->db->Quote($namekey).','.$this->db->Quote($value).','.$this->db->Quote($value).'),';
		}
		$query = rtrim($query,',');
		$this->db->setQuery($query);
		$this->db->query();
	}
}