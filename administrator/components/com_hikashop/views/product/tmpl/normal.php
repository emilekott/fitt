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
								<label for="product_name">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
								</label>
							</td>
							<td>
								<input id="product_name" type="text" size="80" name="<?php echo $this->product_name_input; ?>" value="<?php echo $this->escape(@$this->element->product_name); ?>" />
								<?php if(isset($this->product_name_published)){
										$publishedid = 'published-'.$this->product_name_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_name_published,'translation') ?></span>
								<?php } ?>*
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="<?php echo $this->editor->name; ?>">
									<?php echo JText::_( 'PRODUCT_DESCRIPTION' ); ?>
								</label>
							</td>
							<td width="100%"></td>
						</tr>
						<tr>
							<td colspan="2" width="100%">
								<?php if(isset($this->product_description_published)){
										$publishedid = 'published-'.$this->product_description_id;
								?>
								<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_description_published,'translation') ?></span>
								<br/>
								<?php }
									$this->editor->content = @$this->element->product_description;
									echo $this->editor->display();
								?>
							</td>
						</tr>
						<?php if($this->element->product_type=='main'){ ?>
							<tr>
								<td class="key">
									<label for="product_url">
										<?php echo JText::_( 'URL' ); ?>
									</label>
								</td>
								<td>
									<input id="product_url" type="text" size="80" name="<?php echo $this->product_url_input; ?>" value="<?php echo $this->escape(@$this->element->product_url); ?>" />
									<?php if(isset($this->product_url_published)){
											$publishedid = 'published-'.$this->product_url_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_url_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="product_meta_description">
										<?php echo JText::_( 'PRODUCT_META_DESCRIPTION' ); ?>
									</label>
								</td>
								<td>
									<textarea id="product_meta_description" cols="46" rows="2" name="<?php echo $this->product_meta_description_input; ?>"><?php echo $this->escape(@$this->element->product_meta_description); ?></textarea>
									<?php if(isset($this->product_meta_description_published)){
											$publishedid = 'published-'.$this->product_meta_description_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_meta_description_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="product_keywords">
										<?php echo JText::_( 'PRODUCT_KEYWORDS' ); ?>
									</label>
								</td>
								<td>
									<textarea id="product_keywords" cols="46" rows="2" name="<?php echo $this->product_keywords_input; ?>"><?php echo $this->escape(@$this->element->product_keywords); ?></textarea>
									<?php if(isset($this->product_keywords_published)){
											$publishedid = 'published-'.$this->product_keywords_id;
									?>
									<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->product_kaywords_published,'translation') ?></span>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
					</table>