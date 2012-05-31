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
foreach($this->widget->elements as $element){
	$data[] = '[new Date('.$element->year.', '.(int)$element->month.', '.(int)$element->day.'), '.(int)$element->total.']';
}
$js="
google.load('visualization', '1', {'packages':['annotatedtimeline']});
      google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
      function drawChart_".$this->widget->widget_id."() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', undefined);
        data.addColumn('number', undefined);
        data.addRows([
          ".implode(', ',$data)."
        ]);
		var el = document.getElementById('graph_".$this->widget->widget_id."');
        var chart = new google.visualization.AnnotatedTimeLine(el);
        chart.draw(data,{'wmode':'transparent'});
        el.style.width = null;
      }";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="width: 300px; height: 210px;" align="center"></div>