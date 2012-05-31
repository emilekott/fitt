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
$data = array();
$i=0;
if(empty($this->widget->elements)){
	$data[] = 'data.setValue(0, 0, null);
			data.setValue(0, 1, 0);
			data.setValue(0, 2, null);';
}else{
	foreach($this->widget->elements as $element){
		$data[] = 'data.setValue('.$i.', 0, \''.$element->code.'\');
	        data.setValue('.$i.', 1, '.(int)$element->total.');
	        data.setValue('.$i.', 2, \''.$element->name.'\');';
		$i++;
	}
}
$js="
google.load('visualization', '1', {'packages':['geomap']});
      google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
      function drawChart_".$this->widget->widget_id."() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Code');
        data.addColumn('number', '".JText::_(strtoupper($this->widget->widget_params->content))."');
        data.addColumn('string', 'Country');
        data.addRows(".count($data).");
        ".implode("\n",$data)."
		var options = {};
        options['dataMode'] = 'regions';
        options['width'] = '300px';
        options['height'] = '210px';
        options['showLegend'] = false;
        options['region']='".$this->widget->widget_params->region."';
        var chart = new google.visualization.GeoMap(document.getElementById('graph_".$this->widget->widget_id."'));
        chart.draw(data, options);
      }";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="height: 210px;" align="center"></div>