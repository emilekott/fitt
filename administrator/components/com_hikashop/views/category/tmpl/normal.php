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
					<table class="admintable"  width="100%">
						<tr>
							<td class="key">
								<label for="category_name">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
								</label>
							</td>
							<td>
								<input id="category_name" type="text" size="80" name="<?php echo $this->category_name_input; ?>" value="<?php echo $this->escape(@$this->element->category_name); ?>" />
								<?php if(isset($this->category_name_published)){
										$publishedid = 'published-'.$this->category_name_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_name_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="<?php echo $this->editor->name; ?>">
									<?php echo JText::_( 'CATEGORY_DESCRIPTION' ); ?>
								</label>
							</td>
							<td width="100%"></td>
						</tr>
						<tr>
							<td colspan="2" width="100%">
								<?php if(isset($this->category_description_published)){
										$publishedid = 'published-'.$this->category_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_description_published,'translation') ?></span>
								<br/>
								<?php }
									$this->editor->content = @$this->element->category_description;
									echo $this->editor->display();
								?>
							</td>
						</tr>
						<?php if(empty($this->element->category_type) || in_array($this->element->category_type,array('product','manufacturer'))){ ?>
						<tr>
							<td class="key">
								<label for="category_description">
									<?php echo JText::_( 'CATEGORY_META_DESCRIPTION' ); ?>
								</label>
							</td>
							<td>
								<textarea id="category_meta_description" cols="46" rows="2" name="<?php echo $this->category_meta_description_input; ?>"><?php echo $this->escape(@$this->element->category_meta_description); ?></textarea>
								<?php if(isset($this->category_meta_description_published)){
										$publishedid = 'published-'.$this->category_meta_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_meta_description_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="category_keywords">
									<?php echo JText::_( 'CATEGORY_KEYWORDS' ); ?>
								</label>
							</td>
							<td>
								<textarea id="category_keywords" cols="46" rows="1" name="<?php echo $this->category_keywords_input; ?>"><?php echo $this->escape(@$this->element->category_keywords); ?></textarea>
								<?php if(isset($this->category_keywords_published)){
										$publishedid = 'published-'.$this->category_keywords_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->category_keywords_published,'translation') ?></span>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
					</table>