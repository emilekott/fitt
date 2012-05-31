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
jimport('joomla.html.pagination');
class hikashopPaginationHelper extends JPagination{
	var $hikaSuffix='';
	var $form = '';
	function getPagesLinks(){
		$app =& JFactory::getApplication();
		$lang =& JFactory::getLanguage();
		$data = $this->_buildDataObject();
		$list = array();
		$itemOverride = false;
		$listOverride = false;
		$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'pagination.php';
		if (file_exists($chromePath)){
			require_once ($chromePath);
			if (function_exists('pagination_list_render')) {
				$listOverride = true;
			}
		}
		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}
		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}
		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}
		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		} else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		} else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}
		if($this->total > $this->limit){
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else{
			return '';
		}
	}
	function _item_active(&$item){
			if($item->base>0)
				return "<a class=\"pagenav\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=".$item->base."; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
			else
				return "<a class=\"pagenav\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm".$this->hikaSuffix.$this->form.".limitstart".$this->hikaSuffix.".value=0; document.adminForm".$this->hikaSuffix.$this->form.".submit();return false;\">".$item->text."</a>";
	}
	function _item_inactive(&$item){
		$mainframe =& JFactory::getApplication();
		if ($mainframe->isAdmin()) {
			return "<span>".$item->text."</span>";
		} else {
			$class = 'pagenav';
			if(!is_numeric($item->text)){
				$class .= ' pagenav_text';
			}
			return '<span class="'.$class.'">'.$item->text."</span>";
		}
	}
	function _list_render($list){
		$html = null;
		$html .= '<span class="pagenav_start_chevron">&lt;&lt; </span>';
		$html .= $list['start']['data'];
		$html .= '<span class="pagenav_previous_chevron"> &lt; </span>';
		$html .= $list['previous']['data'];
		foreach( $list['pages'] as $page ) {
			$html .= ' '.$page['data'];
		}
		$html .= ' '. $list['next']['data'];
		$html .= '<span class="pagenav_next_chevron"> &gt;</span>';
		$html .= ' '. $list['end']['data'];
		$html .= '<span class="pagenav_end_chevron"> &gt;&gt;</span>';
		return $html;
	}
	function _list_footer($list){
		$html = "<div class=\"list-footer\">\n";
		if(version_compare(JVERSION,'1.6','>=')){
			$display = JText::_('JGLOBAL_DISPLAY_NUM');
		}else{
			$display = JText::_('Display Num');
		}
		$html .= "\n<div class=\"limit\">".$display.$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
		$html .= "\n<input type=\"hidden\" name=\"limitstart".$this->hikaSuffix."\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";
		return $html;
	}
	function getListFooter(){
		$list = array();
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox();
		$list['pagescounter']	= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();
		return $this->_list_footer($list);
	}
	function getLimitBox(){
		$limits = array ();
		$limits[] = JHTML::_('select.option', (string)$this->limit);
		for ($i = 5; $i <= 30; $i += 5) {
			$limits[] = JHTML::_('select.option', "$i");
		}
		$limits[] = JHTML::_('select.option', '50');
		$limits[] = JHTML::_('select.option', '100');
		$limits[] = JHTML::_('select.option', '0', JText::_('all'));
		return JHTML::_('select.genericlist',  $limits, 'limit'.$this->hikaSuffix, 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $this->_viewall ? 0 : $this->limit);
	}
}