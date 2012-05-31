<?php

jimport('joomla.form.formfield');

/**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 class JFormFieldColorChooser extends JFormField {

    protected  $type = 'ColorChooser';
	protected static $js_loaded = false;

	protected function getInput()
	{
		global $stylesList;
		$output = '';
		$document 	=& JFactory::getDocument();

        if (!self::$js_loaded){
            $option = 'com_rokgallery';
			$document->addStyleSheet('components/'.$option.'/assets/moorainbow/mooRainbow.css');
			$document->addScript('components/'.$option.'/assets/moorainbow/mooRainbow.js');
			$scriptconfig = $this->rainbowInit();
			$document->addScriptDeclaration($scriptconfig);
			self::$js_loaded = true;
		}

		$transparent = 1;
		if ($this->element['transparent'] == 'false') $transparent = 0;

		$scriptconfig = $this->newRainbow($this->id, $transparent);

		$document->addScriptDeclaration($scriptconfig);

		$output .= "<input class=\"picker-input text-color\" id=\"".$this->id."\" name=\"".$this->name."\" type=\"text\" size=\"8\" maxlength=\"11\" value=\"".$this->value."\" />";
		$output .= "<div class=\"picker\" id=\"myRainbow_".$this->id."_input\"><div class=\"overlay".(($this->value == 'transparent') ? ' overlay-transparent' : '')."\" style=\"background-color: ".$this->value."\"><div></div></div></div>\n";
		$output .= "</div>";

		return $output;
	}

	protected function newRainbow($id, $transparent)
	{
        $option = "com_rokgallery";

		return "
		var r_".$id.";
		window.addEvent('domready', function() {
			document.id('".$id."').getParent().addEvents({
				'mouseenter': f_".$id.",
				'mouseleave': function(){
					this.removeEvent('mouseenter', f_".$id.");
				}
			});
		});

		var f_".$id." = function(){
			var input = document.id('".$id."');
			r_".$id." = new MooRainbow('myRainbow_".$id."_input', {
				id: 'myRainbow_".$id."',
				startColor: document.id('".$id."').get('value').hexToRgb(true) || [255, 255, 255],
				imgPath: 'components/".$option."/assets/moorainbow/images/',
				transparent: ".$transparent.",
				onChange: function(color) {
					if (color == 'transparent') {
						input.getNext().getFirst().addClass('overlay-transparent').setStyle('background-color', 'transparent');
						input.value = 'transparent';
					}
					else {
						input.getNext().getFirst().removeClass('overlay-transparent').setStyle('background-color', color.hex);
						input.value = color.hex;
					}

					if (this.visible) this.okButton.focus();
				}
			});

			r_".$id.".okButton.setStyle('outline', 'none');
			document.id('myRainbow_".$id."_input').addEvent('click', function() {
				(function() {r_".$id.".okButton.focus()}).delay(10);
			});
			input.addEvent('keyup', function(e) {
				if (e) e = new Event(e);
				if ((this.value.length == 4 || this.value.length == 7) && this.value[0] == '#') {
					var rgb = new Color(this.value);
					var hex = this.value;
					var hsb = rgb.rgbToHsb();
					var color = {
						'hex': hex,
						'rgb': rgb,
						'hsb': hsb
					}
					r_".$id.".fireEvent('onChange', color);
					r_".$id.".manualSet(color.rgb);
				};
			});

			input.getNext().getFirst().setStyle('background-color', r_".$id.".sets.hex);
			rainbowLoad('myRainbow_".$id."');
		};\n";
	}

	protected function rainbowInit()
	{
		return "var rainbowLoad = function(name, hex) {
				if (hex) {
					var n = name.replace('params', '');
					document.id(n+'_input').getPrevious().value = hex;
					document.id(n+'_input').getFirst().setStyle('background-color', hex);
				}
			};
		";
	}
}
