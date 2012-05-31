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
foreach($this->widget->elements as $element){
	$data[] = 'data.setValue('.$i.', 0, \''.$element->name.'\');
        data.setValue('.$i.', 1, '.(int)$element->total.');';
	$i++;
}
$js="
google.load('visualization', '1', {'packages':['corechart']});
      google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
      function drawChart_".$this->widget->widget_id."() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'name');
        data.addColumn('number', 'total');
        data.addRows(".count($data).");
        ".implode("\n",$data)."
        var chart = new google.visualization.PieChart(document.getElementById('graph_".$this->widget->widget_id."'));
        chart.draw(data, {width: 300, height: 200, legend: 'none'});
      }";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="height: 210px;" align="center"></div>