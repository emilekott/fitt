<?php
/**
 * @package   Reflex Template - RocketTheme
 * @version   1.5.2 November 11, 2011
 * @author    YOOtheme http://www.yootheme.com & RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2009 YOOtheme GmbH
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * These template overrides are based on the fantastic GNU/GPLv2 overrides created by YOOtheme (http://www.yootheme.com)
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ROOT."/components/com_gantry/gantry.php");
global $gantry;
include_once(dirname(__FILE__).DS.'..'.DS.'icon.php');

$canEdit = ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own'));
$showIcons = ($canEdit || $this->item->params->get('show_pdf_icon') || $this->item->params->get('show_print_icon') || $this->item->params->get('show_email_icon'));
$articleInfo = ($this->item->params->get('show_create_date') || (intval($this->item->modified) != 0 && $this->item->params->get('show_modify_date')) || ($showIcons));
$articleInfoExtended = ($this->item->params->get('show_author') && ($this->item->author != "") || ($this->item->params->get('show_url') && $this->item->urls) || ($this->item->params->get('show_section') && $this->item->sectionid) || ($this->item->params->get('show_category') && $this->item->catid));
?>

<div class="rt-article <?php if ($this->item->state == 0) echo 'unpublished' ?>">
	<div class="rt-article-inner"><?php if ($articleInfo) : ?><div class="rt-article-inner-bg"><?php endif; ?>
		<?php if ($articleInfo) : ?>
		<div class="rt-article-left-col">
			<?php /** Begin Created Date **/ if ($this->item->params->get('show_create_date')) : ?>
			<span class="rt-date-posted">
				<span class="date-item"><?php echo JHTML::_('date', $this->item->created, JText::_('%d')); ?><br /></span>
				<span class="month-item"><?php echo JHTML::_('date', $this->item->created, JText::_('%b')); ?></span>
				<span class="year-item"><?php echo JHTML::_('date', $this->item->created, JText::_('%y')); ?></span>
			</span>
			<?php /** End Created Date **/ endif; ?>
	
			<?php /** Begin Modified Date **/ if ( intval($this->item->modified) != 0 && $this->item->params->get('show_modify_date')) : ?>
			<span class="rt-date-modified">
				<?php echo JText::sprintf('LAST_UPDATED2', JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?>
			</span>
			<?php /** End Modified Date **/ endif; ?>
			<?php /** Begin Article Icons **/ if ($showIcons) : ?>
			<div class="rt-article-icons">
				<?php if ($this->item->params->get('show_pdf_icon')) :
					echo RokIcon::pdf($this->item, $this->item->params, $this->access);
				endif;
				if ($this->item->params->get('show_print_icon')) :
					echo RokIcon::print_popup($this->item, $this->item->params, $this->access);
				endif;
				if ($this->item->params->get('show_email_icon')) :
					echo RokIcon::email($this->item, $this->item->params, $this->access);
				endif; ?>
				<?php if ($canEdit) : ?>
				<span class="icon edit">
					<?php echo JHTML::_('icon.edit', $this->item, $this->item->params, $this->access); ?>
				</span>
				<?php endif; ?>
			</div>
			<?php /** End Article Icons **/ endif; ?>
		</div>
		<?php endif; ?>
		<div class="rt-article-right-col">
		<?php /** Begin Article Title **/ if ($this->item->params->get('show_title')) : ?>
			<div class="module-title">
			<?php if ($this->item->params->get('show_title')) : ?>
			<h1 class="title">
			<?php if ($this->item->params->get('link_titles') && $this->item->readmore_link != '') : ?><a href="<?php echo $this->item->readmore_link; ?>"><?php echo $this->escape($this->item->title); ?></a><?php else : ?><?php echo $this->escape($this->item->title); ?><?php endif; ?>
			</h1>
			<?php endif; ?>
			</div>
		<?php /** End Article Title **/ endif; ?>			
		<?php if ($articleInfoExtended) : ?>
		<div class="rt-articleinfo">
			<div class="rt-articleinfo-text"><div class="rt-articleinfo-text2">
				<?php /** Begin Author **/ if (($this->item->params->get('show_author')) && ($this->item->author != "")) : ?>
				<span class="rt-author">
					<?php JText::printf(($this->escape($this->item->created_by_alias) ? $this->escape($this->item->created_by_alias) : $this->escape($this->item->author)) ); ?>
				</span>
				<?php /** End Author **/endif; ?>

				<?php /** Begin Url **/ if ($this->item->params->get('show_url') && $this->item->urls) : ?>
				<span class="rt-url">
					<a href="http://<?php echo $this->escape($this->item->urls) ; ?>" target="_blank"><?php echo $this->escape($this->item->urls); ?></a>
				</span>
				<?php /** End Url **/ endif; ?>
				<?php /** Begin Article Sec/Cat **/ if (($this->item->params->get('show_section') && $this->item->sectionid) || ($this->item->params->get('show_category') && $this->item->catid)) : ?>
					<?php if ($this->item->params->get('show_section') && $this->item->sectionid && isset($this->item->section)) : ?>
					<span class="rt-section">
						<?php if ($this->item->params->get('link_section')) : ?>
							<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->item->sectionid)).'">'; ?>
						<?php endif; ?>
						<?php echo $this->escape($this->item->section); ?>
						<?php if ($this->item->params->get('link_section')) : ?>
							<?php echo '</a>'; ?>
						<?php endif; ?>
							<?php if ($this->item->params->get('show_category')) : ?>
							<?php echo ' - '; ?>
						<?php endif; ?>
					</span>
					<?php endif; ?>
					<?php if ($this->item->params->get('show_category') && $this->item->catid) : ?>
					<span class="rt-category">
						<?php if ($this->item->params->get('link_category')) : ?>
							<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug, $this->item->sectionid)).'">'; ?>
						<?php endif; ?>
						<?php echo $this->escape($this->item->category); ?>
						<?php if ($this->item->params->get('link_category')) : ?>
							<?php echo '</a>'; ?>
						<?php endif; ?>
					</span>
					<?php endif; ?>
				<?php /** End Article Sec/Cat **/ endif; ?>			
			</div></div>
		</div>
		<?php endif; ?>
		<div class="module-content">
			<?php  if (!$this->item->params->get('show_intro')) :
				echo $this->item->event->afterDisplayTitle;
			endif; ?>
		
			<?php echo $this->item->event->beforeDisplayContent; ?>
			
			<?php if (isset ($this->item->toc)) : ?>
				<?php echo $this->item->toc; ?>
			<?php endif; ?>
		
			<?php echo $this->item->text; ?>
		
			<?php /** Begin Read More **/ if ($this->item->params->get('show_readmore') && $this->item->readmore) : ?>
			<a href="<?php echo $this->item->readmore_link; ?>" class="readon"><span>
				<?php if ($this->item->readmore_register) :
					echo JText::_('Register to read more...');
				elseif ($readmore = $this->item->params->get('readmore')) :
					echo $readmore;
				else :
					echo JText::sprintf('Read more...');
				endif; ?></span></a>
			<?php /** End Read More **/ endif; ?>
	
			<?php echo $this->item->event->afterDisplayContent; ?>
		</div>
		<div class="clear"></div>
	</div>
	<?php if ($articleInfo) : ?></div><?php endif; ?></div>
</div>