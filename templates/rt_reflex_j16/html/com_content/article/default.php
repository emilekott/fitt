<?php
/**
 * @package   Template Overrides - RocketTheme
 * @version   1.6.4 December 12, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Gantry Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// Create shortcuts to some parameters.
$params		= $this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$showIcons = ($params->get('show_print_icon') || $params->get('show_email_icon') || $canEdit);
$articleInfo = ($params->get('show_create_date') || $params->get('show_modify_date') || ($showIcons));
$articleInfoExtended = (($params->get('show_author') && !empty($this->item->author )) or ($params->get('show_hits')) or ($params->get('show_parent_category') && $this->item->parent_id != 1) || $params->get('show_category') || $params->get('show_category') || $params->get('show_publish_date'));
$user		= JFactory::getUser();
$publishdate = version_compare(JVERSION,'1.7.3','<') ? 'COM_CONTENT_PUBLISHED_DATE' : 'COM_CONTENT_PUBLISHED_DATE_ON';

?>
<div class="article-wrapper">
<div class="rt-article">
	<div class="rt-article-inner"><?php if ($articleInfo) : ?><div class="rt-article-inner-bg"><?php endif; ?>
		<?php if ($articleInfo) : ?>
		<div class="rt-article-left-col">
			<?php if ($params->get('show_create_date')) : ?>
			<span class="rt-date-posted">
				<span class="date-item"><?php echo JHtml::_('date',$this->item->created, JText::_('d')); ?><br /></span>
				<span class="month-item"><?php echo JHtml::_('date',$this->item->created, JText::_('M')); ?></span>
				<span class="year-item"><?php echo JHtml::_('date',$this->item->created, JText::_('y')); ?></span>
			</span>
			<?php endif; ?>
			<?php if ($params->get('show_modify_date')) : ?>
			<span class="rt-date-modified">
				<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date',$this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?>
			</span>
			<?php endif; ?>
			<?php /** Begin Article Icons **/ if ($showIcons) : ?>
			<div class="rt-article-icons">
				<ul class="actions">
				<?php if (!$this->print) : ?>
				<?php if ($params->get('show_print_icon')) : ?>
				<li class="print-icon">
					<?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?>
				</li>
				<?php endif; ?>
		
				<?php if ($params->get('show_email_icon')) : ?>
				<li class="email-icon">
					<?php echo JHtml::_('icon.email',  $this->item, $params); ?>
				</li>
				<?php endif; ?>
					
				<?php if ($canEdit) : ?>
				<li class="edit-icon">
					<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
				</li>
				<?php endif; ?>
				<?php else : ?>
				<li>
					<?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
				</li>
				<?php endif; ?>
				</ul>
			</div>
			<?php /** End Article Icons **/ endif; ?>
			</div>
			<?php endif; ?>
		<div class="rt-article-right-col">	
		<?php /** Begin Page Title **/ if ($this->params->get('show_page_heading', 1)) : ?>
		<h1 class="rt-pagetitle">
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
		<?php /** End Page Title **/  endif; ?>
		<?php /** Begin Article Title **/ if ($params->get('show_title')) : ?>
			<div class="module-title">
				<h1 class="title">
					<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
					<a href="<?php echo $this->item->readmore_link; ?>">
						<?php echo $this->escape($this->item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->title); ?>
					<?php endif; ?>
				</h1>
			</div>
		<?php /** End Article Title **/ endif; ?>
			
		<?php if ($articleInfoExtended) : ?>
			<div class="rt-articleinfo">
				<div class="rt-articleinfo-text"><div class="rt-articleinfo-text2">
					<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
					<span class="rt-author"> 
						<?php $author =  $this->item->author; ?>
						<?php $author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author);?>
			
						<?php if (!empty($this->item->contactid ) &&  $params->get('link_author') == true):?>
							<?php echo JHtml::_('link',JRoute::_('index.php?option=com_contact&view=contact&id='.$this->item->contactid),$author); ?>
			
						<?php else :?>
							<?php echo $author; ?>
						<?php endif; ?>
					</span>
					<?php endif; ?>
					<?php if ($params->get('show_publish_date')) : ?>
					<span class="rt-date-published">
						<?php echo JText::sprintf($publishdate, JHtml::_('date',$this->item->publish_up, JText::_('DATE_FORMAT_LC3'))); ?>
					</span>
					<?php endif; ?>	
					<?php if ($params->get('show_hits')) : ?>
					<span class="rt-hits">
						<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
					</span>
					<?php endif; ?>
							<?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
					<span class="rt-parent-category">
						<?php	$title = $this->escape($this->item->parent_title);
								$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)).'">'.$title.'</a>';?>
						<?php if ($params->get('link_parent_category') AND $this->item->parent_slug) : ?>
							<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
							<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
						<?php endif; ?>
					</span>
					<?php endif; ?>
					<?php if ($params->get('show_category')) : ?>
					<span class="rt-category">
						<?php 	$title = $this->escape($this->item->category_title);
								$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)).'">'.$title.'</a>';?>
						<?php if ($params->get('link_category') AND $this->item->catslug) : ?>
							<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
							<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
						<?php endif; ?>
					</span>
					<?php endif; ?>
				</div></div>
			</div>
			<?php endif; ?>

		<?php  if (!$params->get('show_intro')) :
			echo $this->item->event->afterDisplayTitle;
		endif; ?>

		<?php echo $this->item->event->beforeDisplayContent; ?>

		<?php if (isset ($this->item->toc)) : ?>
			<?php echo $this->item->toc; ?>
		<?php endif; ?>

		<?php if ($params->get('access-view')):?>
			<?php echo $this->item->text; ?>
			
		<?php //optional teaser intro text for guests ?>
		<?php elseif ($params->get('show_noauth') == true AND  $user->get('guest') ) : ?>
			<?php echo $this->item->introtext; ?>
			<?php //Optional link to let them register to see the whole article. ?>
			<?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
				$link1 = JRoute::_('index.php?option=com_users&view=login');
				$link = new JURI($link1);?>
				<p class="readmore">
				<a href="<?php echo $link; ?>">
				<?php $attribs = json_decode($this->item->attribs);  ?> 
				<?php 
				if ($attribs->alternative_readmore == null) :
					echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
				elseif ($readmore = $this->item->alternative_readmore) :
					echo $readmore;
					if ($params->get('show_readmore_title', 0) != 0) :
					    echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					endif;
				elseif ($params->get('show_readmore_title', 0) == 0) :
					echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');	
				else :
					echo JText::_('COM_CONTENT_READ_MORE');
					echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
				endif; ?></a>
				</p>
			<?php endif; ?>
		<?php endif; ?>
		</div>
		<?php echo $this->item->event->afterDisplayContent; ?>

	<?php if ($articleInfo) : ?></div><?php endif; ?></div>
</div>
</div>