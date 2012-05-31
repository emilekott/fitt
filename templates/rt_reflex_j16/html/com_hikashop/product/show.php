<?php
/**
 * @package    HikaShop for Joomla!
 * @version    1.5.5
 * @author    hikashop.com
 * @copyright  (C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="hikashop_product_<?php echo @$this->element->product_code; ?>_page" class="hikashop_product_page">
<?php if(empty($this->element)){
  $app =& JFactory::getApplication();
  $app->enqueueMessage(JText::_('PRODUCT_NOT_FOUND'));
}else{
?>
<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form" enctype="multipart/form-data">
  <div id="hikashop_product_top_part" class="hikashop_product_top_part">
    <h1>
      <span id="hikashop_product_name_main" class="hikashop_product_name_main">
        <?php
        echo $this->element->product_name;
        ?>
      </span>
      <!--<span id="hikashop_product_code_main" class="hikashop_product_code_main">
        <?php
        echo $this->element->product_code;
        ?>
      </span>-->
    </h1>
    <?php
    $pluginsClass = hikashop_get('class.plugins');
    $plugin = $pluginsClass->getByName('content','hikashopsocial');
    if(@$plugin->published || @$plugin->enabled){
      echo '{hikashop_social}';
    }
    ?>
  </div>
  <div id="hikashop_product_left_part" class="hikashop_product_left_part">
    <div id="hikashop_product_image_main" >
      <div id="hikashop_main_image_div" class="hikashop_main_image_div">
      <?php
            $image = null;
      if(!empty($this->element->images)){
        $image = reset($this->element->images);
      }
      if(!$this->config->get('thumbnail')){
        if(!empty($image)){
          echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
        }
      }else{
        $height = $this->config->get('thumbnail_y');
        $style='';
        if(!empty($this->element->images) && count($this->element->images)>1){
          if(!empty($height)){
            $style=' style="height:'.($height+5).'px;"';
          }
        } ?>
        <div class="hikashop_product_main_image_thumb" id="hikashop_main_image_thumb_div" <?php echo $style;?> >
        <?php echo $this->image->display(@$image->file_path,true,@$image->file_name,'id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"','id="hikashop_main_image_link"'); ?>
        </div>
    <?php  }
      ?>
      </div><div id="hikashop_small_image_div" class="hikashop_small_image_div"><?php
      if(!empty($this->element->images) && count($this->element->images)>1){
        foreach($this->element->images as $image){
          echo $this->image->display($image->file_path,'hikashop_main_image',$image->file_name,'class="hikashop_child_image"');
        }
      }
      ?>
      </div>
    </div>
  </div>
  <div id="hikashop_product_right_part" class="hikashop_product_right_part">
    <span id="hikashop_product_price_main" class="hikashop_product_price_main">
      <?php
      if($this->params->get('show_price')){
        $this->row =& $this->element;
        $this->setLayout('listing_price');
        echo $this->loadTemplate();
      }
      ?>
    </span><br />
    <?php if(isset($this->element->product_weight) && bccomp($this->element->product_weight,0,3)){ ?>
    <span id="hikashop_product_weight_main" class="hikashop_product_weight_main">
      <?php echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($this->element->product_weight,'0'),',.').' '.JText::_($this->element->product_weight_unit); ?><br />
    </span>
    <?php
    }
    if($this->config->get('dimensions_display',0) && bccomp($this->element->product_width,0,3)){ ?>
    <span id="hikashop_product_width_main" class="hikashop_product_width_main">
      <?php echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($this->element->product_width,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
    </span>
    <?php }
    if($this->config->get('dimensions_display',0) && bccomp($this->element->product_length,0,3)){ ?>
    <span id="hikashop_product_length_main" class="hikashop_product_length_main">
      <?php echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($this->element->product_length,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
    </span>
    <?php }
    if($this->config->get('dimensions_display',0) && bccomp($this->element->product_height,0,3)){ ?>
    <span id="hikashop_product_height_main" class="hikashop_product_height_main">
      <?php echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($this->element->product_height,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
    </span>
    <?php }
    if(!empty($this->element->characteristics)){
      ?><div id="hikashop_product_characteristics" class="hikashop_product_characteristics"><?php
      echo $this->characteristic->displayFE($this->element,$this->params);
      ?></div><br /><?php
    }
    $form = '';
    if(!$this->config->get('ajax_add_to_cart',0)){
      $form = ',\'hikashop_product_form\'';
    }
    if(hikashop_level(1) && !empty($this->element->options)){
      ?><div id="hikashop_product_options" class="hikashop_product_options"><?php
        $this->setLayout('option');
        echo $this->loadTemplate();
      ?></div><br /><?php
      $form = ',\'hikashop_product_form\'';
    }
    if(!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty($this->element->prices))){
      if(!empty($this->itemFields)){
      $form = ',\'hikashop_product_form\'';
       ?>
      <div id="hikashop_product_custom_item_info" class="hikashop_product_custom_item_info">
        <table width="100%">
        <?php
        foreach($this->itemFields as $fieldName => $oneExtraField) {
          $itemData = JRequest::getString('item_data_'.$fieldName,$this->element->$fieldName);  ?>
          <tr id="hikashop_item_<?php echo $oneExtraField->field_namekey; ?>" class="hikashop_item_<?php echo $oneExtraField->field_namekey;?>_line">
            <td class="key">
              <span id="hikashop_product_custom_item_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_name">
                <?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
              </span>
            </td>
            <td>
              <span id="hikashop_product_custom_item_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_item_value">
                <?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick'; ?>
                <?php echo $this->fieldsClass->display($oneExtraField,$itemData,'data[item]['.$oneExtraField->field_namekey.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'item\',0);"'); ?>
              </span>
            </td>
          </tr>
        <?php
          }?>
        </table>
      </div>
    <?php }
    }
    if(!empty($form) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user'){ ?>
      <input type="hidden" name="popup" value="1"/>
    <?php }
    if(hikashop_level(1) && !empty($this->element->options)){ ?>
      <span id="hikashop_product_price_with_options_main" class="hikashop_product_price_with_options_main">
      </span>
    <?php } ?>
    <div id="hikashop_product_quantity_main" class="hikashop_product_quantity_main"><?php
      $this->row =& $this->element;
      $this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1'.$form.'); } else { return false; }';
      $this->setLayout('quantity');
      echo $this->loadTemplate();
    ?>
    </div>
    <?php $contact = $this->config->get('product_contact',0); ?>
    <div id="hikashop_product_contact_main" class="hikashop_product_contact_main">
      <?php
      if(hikashop_level(1) && ($contact==2 || ($contact==1 && !empty($this->element->product_contact)))){
        $empty='';
        $params = new JParameter($empty);
        echo $this->cart->displayButton(JText::_('CONTACT_US_FOR_INFO'),'contact_us',$params,hikashop_completeLink('product&task=contact&cid='.$this->row->product_id),'window.location=\''.hikashop_completeLink('product&task=contact&cid='.$this->row->product_id).'\';return false;');
      } ?>
    </div>
    <?php if(!empty($this->fields)){?>
      <div id="hikashop_product_custom_info_main" class="hikashop_product_custom_info_main">
        <h4><?php echo JText::_('SPECIFICATIONS');?></h4>
        <table width="100%">
        <?php
        $this->fieldsClass->prefix = '';
        foreach($this->fields as $fieldName => $oneExtraField) {
          if(!empty($this->element->$fieldName)){ ?>
          <tr class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
            <td class="key">
              <span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name">
                <?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
              </span>
            </td>
            <td>
              <span id="hikashop_product_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
                <?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
              </span>
            </td>
          </tr>
        <?php }
          }?>
        </table>
      </div>
    <?php } ?>
    <span id="hikashop_product_id_main" class="hikashop_product_id_main">
      <input type="hidden" name="product_id" value="<?php echo $this->element->product_id; ?>" />
    </span>
  </div>
  <div id="hikashop_product_bottom_part" class="hikashop_product_bottom_part">
    <div id="hikashop_product_description_main" class="hikashop_product_description_main">
      <?php echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$this->element->product_description.' ')); ?>
    </div>
    <span id="hikashop_product_url_main" class="hikashop_product_url_main">
      <?php
      if(!empty($this->element->product_url)){
        echo JText::sprintf('MANUFACTURER_URL','<a href="'.$this->element->product_url.'" target="_blank">'.$this->element->product_url.'</a>');
      } ?>
    </span>
    <div id="hikashop_product_files_main" class="hikashop_product_files_main">
      <?php if(!empty($this->element->files)){
            $skip = true;
            foreach($this->element->files as $file){
              if($file->file_free_download) $skip = false;
            }
            if(!$skip){
              ?>
              <fieldset class="hikashop_product_files_fieldset">
                <?php $html = array();
                echo '<legend>'. JText::_('DOWNLOADS').'</legend>';
                foreach($this->element->files as $file){
                  if(empty($file->file_name)){
                    $file->file_name = $file->file_path;
                  }
                  $fileHtml = '';
                  if(!empty($file->file_free_download)){
                    $fileHtml = '<a class="hikashop_product_file_link" href="'.hikashop_completeLink('product&task=download&file_id='.$file->file_id).'">'.$file->file_name.'</a><br/>';
                  }
                  $html[]=$fileHtml;
                }
                echo implode('<br/>',$html); ?>
              </fieldset>
            <?php }
          } ?>
    </div>
  </div>
  <input type="hidden" name="add" value="1"/>
  <input type="hidden" name="ctrl" value="product"/>
  <input type="hidden" name="task" value="updatecart"/>
  <input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
</form>
<?php
  if(empty($this->element->variants)){
    if(hikashop_level(1) && !empty($this->element->options)){
      if(!empty($this->row->prices)){
        foreach($this->row->prices as $price){
          if(isset($price->price_min_quantity) && empty($this->cart_product_price)){
            if($price->price_min_quantity<=1){
              if($this->params->get('price_with_tax')){
                $priceUsed = $price->price_value_with_tax;
              }else{
                $priceUsed = $price->price_value;
              }
              echo '
            <input type="hidden" name="hikashop_price_product" value="'.$this->row->product_id.'" />
            <input type="hidden" id="hikashop_price_product_'.$this->row->product_id.'" value="'.$priceUsed.'" />
            <input type="hidden" id="hikashop_price_product_with_options_'.$this->row->product_id.'" value="'.$priceUsed.'" />';
            }
          }
        }
      }
    }
  }else{
    foreach($this->element->variants as $variant){
      $variant_name = array();
      if(!empty($variant->characteristics)){
        foreach($variant->characteristics as $k => $ch){
          $variant_name[]=$ch->characteristic_id;
        }
      }
      $variant_name = implode('_',$variant_name);
      if(!empty($variant->images)){
      ?>
      <div id="hikashop_product_image_<?php echo $variant_name;?>" style="display:none;">
        <div id="hikashop_main_image_div_<?php echo $variant_name;?>" class="hikashop_main_image_div">
        <?php
          $image = reset($variant->images);
          if(!$this->config->get('thumbnail')){
            echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image_'.$variant_name.'" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
          }else{
            $style='';
            if(!empty($variant->images) && count($variant->images)>1){
              $height = $this->config->get('thumbnail_y');
              if(!empty($height)){
                $style=' style="height:'.($height+5).'px;"';
              }
            }  ?>
            <div class="hikashop_product_main_image_thumb" id="hikashop_main_image_thumb_div_<?php echo $variant_name;?>" <?php echo $style;?>>
            <?php echo $this->image->display($image->file_path,true,$image->file_name,'id="hikashop_main_image_'.$variant_name.'" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"','id="hikashop_main_image_'.$variant_name.'_link"'); ?>
            </div>
          <?php
          }
          if(!empty($variant->images) && count($variant->images)>1){
            ?>
          </div><div id="hikashop_small_image_div_<?php echo $variant_name;?>" class="hikashop_small_image_div"><?php
            foreach($variant->images as $image){
              echo $this->image->display($image->file_path,'hikashop_main_image_'.$variant_name,$image->file_name,'class="hikashop_child_image"');
            }
          }
        ?>
        </div>
      </div>
      <?php  } ?>
      <div id="hikashop_product_name_<?php echo $variant_name;?>" style="display:none;">
        <?php echo $variant->product_name;?>
      </div>
      <div id="hikashop_product_code_<?php echo $variant_name;?>" style="display:none;">
        <?php echo $variant->product_code;?>
      </div>
      <div id="hikashop_product_price_<?php echo $variant_name;?>" style="display:none;">
        <?php
        if($this->params->get('show_price')){
          $this->row =& $variant;
          $this->setLayout('listing_price');
          echo $this->loadTemplate();
        }
        ?>
      </div>
      <?php
      if(hikashop_level(1) && !empty($this->element->options)){
        if(!empty($this->row->prices)){
          foreach($this->row->prices as $price){
            if(isset($price->price_min_quantity) && empty($this->cart_product_price)){
              if($price->price_min_quantity<=1){
                if($this->params->get('price_with_tax')){
                  $priceUsed = $price->price_value_with_tax;
                }else{
                  $priceUsed = $price->price_value;
                }
                echo '
              <input type="hidden" name="hikashop_price_product" value="'.$this->row->product_id.'" />
              <input type="hidden" id="hikashop_price_product_'.$this->row->product_id.'" value="'.$priceUsed.'" />
              <input type="hidden" id="hikashop_price_product_with_options_'.$this->row->product_id.'" value="'.$priceUsed.'" />';
              }
            }
          }
        }
      }
      ?>
      <div id="hikashop_product_quantity_<?php echo $variant_name;?>" style="display:none;">
        <?php
          $this->row =& $variant;
          $this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1'.$form.'); } else { return false; }';
          $this->setLayout('quantity');
          echo $this->loadTemplate();
        ?>
      </div>
      <div id="hikashop_product_contact_<?php echo $variant_name;?>" style="display:none;">
      <?php
        if(hikashop_level(1) && ($contact==2 || ($contact==1 && !empty($variant->product_contact)))){
          echo $this->cart->displayButton(JText::_('CONTACT_US_FOR_INFO'),'contact_us',@$this->params,hikashop_completeLink('product&task=contact&cid='.$variant->product_id),'window.location=\''.hikashop_completeLink('product&task=contact&cid='.$variant->product_id).'\';return false;');
        }
      ?>
      </div>
      <div id="hikashop_product_description_<?php echo $variant_name;?>" style="display:none;">
        <?php echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$variant->product_description));?>
      </div>
      <?php if(isset($variant->product_weight) && bccomp($variant->product_weight,0,3)){ ?>
      <div id="hikashop_product_weight_<?php echo $variant_name;?>" style="display:none;">
        <?php echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($variant->product_weight,'0'),',.').' '.JText::_($variant->product_weight_unit); ?><br />
      </div>
      <?php }
      if($this->config->get('dimensions_display',0)){
        if(isset($variant->product_width) && bccomp($variant->product_width,0,3)){ ?>
        <div id="hikashop_product_width_<?php echo $variant_name;?>" style="display:none;">
          <?php echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($variant->product_width,'0'),',.').' '.JText::_($variant->product_dimension_unit); ?><br />
        </div>
        <?php } ?>
        <?php if(isset($variant->product_length) && bccomp($variant->product_length,0,3)){ ?>
        <div id="hikashop_product_length_<?php echo $variant_name;?>" style="display:none;">
          <?php echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($variant->product_length,'0'),',.').' '.JText::_($variant->product_dimension_unit); ?><br />
        </div>
        <?php } ?>
        <?php if(isset($variant->product_height) && bccomp($variant->product_height,0,3)){ ?>
        <div id="hikashop_product_height_<?php echo $variant_name;?>" style="display:none;">
          <?php echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($variant->product_height,'0'),',.').' '.JText::_($variant->product_dimension_unit); ?><br />
        </div>
        <?php }
      } ?>
      <span id="hikashop_product_url_<?php echo $variant_name;?>" style="display:none;">
        <?php
        if(!empty($variant->product_url)){
          echo JText::sprintf('MANUFACTURER_URL','<a href="'.$variant->product_url.'" target="_blank">'.$variant->product_url.'</a>');
        } ?>
      </span>
      <span id="hikashop_product_id_<?php echo $variant_name;?>">
        <input type="hidden" name="product_id" value="<?php echo $variant->product_id; ?>" />
      </span>
      <?php if(!empty($this->fields)){?>
      <div id="hikashop_product_custom_info_<?php echo $variant_name;?>" style="display:none;">
        <h4><?php echo JText::_('SPECIFICATIONS');?></h4>
        <table width="100%">
        <?php
        $this->fieldsClass->prefix = '';
        foreach($this->fields as $fieldName => $oneExtraField) {
          if(!empty($variant->$fieldName)){ ?>
          <tr class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
            <td class="key">
              <span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>_<?php echo $variant_name;?>" class="hikashop_product_custom_name">
                <?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
              </span>
            </td>
            <td>
              <span id="hikashop_product_custom_value_<?php echo $oneExtraField->field_id;?>_<?php echo $variant_name;?>" class="hikashop_product_custom_value">
                <?php echo $this->fieldsClass->show($oneExtraField,$variant->$fieldName); ?>
              </span>
            </td>
          </tr>
        <?php }
          }?>
        </table>
      </div>
    <?php }
    if(!empty($variant->files)){
      $skip = true;
      foreach($variant->files as $file){
        if($file->file_free_download) $skip = false;
      }
      if(!$skip){ ?>
        <div id="hikashop_product_files_<?php echo $variant_name;?>" style="display:none;">
          <fieldset class="hikashop_product_files_fieldset">
            <?php $html = array();
            echo '<legend>'. JText::_('DOWNLOADS').'</legend>';
            foreach($variant->files as $file){
              if(empty($file->file_name)){
                $file->file_name = $file->file_path;
              }
              $fileHtml = '';
              if(!empty($file->file_free_download)){
                $fileHtml = '<a class="hikashop_product_file_link" href="'.hikashop_completeLink('product&task=download&file_id='.$file->file_id).'">'.$file->file_name.'</a><br/>';
              }
              $html[]=$fileHtml;
            }
            echo implode('<br/>',$html); ?>
          </fieldset>
        </div>
    <?php   }
      }
    }
  }
  $this->params->set('show_price_weight',0);
  ?>
  <div class="hikashop_submodules" id="hikashop_submodules" style="clear:both">
    <?php
    if(!empty($this->modules) && is_array($this->modules)){
      jimport('joomla.application.module.helper');
      foreach($this->modules as $module){
        echo JModuleHelper::renderModule($module);
      }
    }
    ?>
  </div>
  <div class="hikashop_external_comments" id="hikashop_external_comments" style="clear:both">
  <?php
  $config =& hikashop_config();
  if($config->get('comments_feature') == 'jcomments'){
    $comments = HIKASHOP_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php';
    if (file_exists($comments)) {
      require_once($comments);
      echo JComments::showComments($this->element->product_id, 'com_hikashop', $this->element->product_name);
    }
  }elseif($config->get('comments_feature') == 'jomcomment'){
    $comments = HIKASHOP_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php';
    if (file_exists($comments)) {
      require_once($comments);
      echo jomcomment($this->element->product_id, 'com_hikashop');
    }
  }
  ?>
  </div><?php
}
?>
</div>