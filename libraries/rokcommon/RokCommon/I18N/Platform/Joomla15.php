<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_I18N_Platform_Joomla15 extends JText implements RokCommon_I18N_Platform
{

    /**
	 * javascript strings
	 */
	protected static $strings=array();

    /**
     * @param  $string
     * @return string
     */
    public function translateFormatted($string)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'sprintf'), $args);
    }

    /**
     * @param  $count
     * @param  $string
     * @return string
     */
    public function translatePlural($string, $count)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'plural'), $args);
    }

    /**
     * replaces j1.6 functions missing from j1.5
     *
     */
    public function translate($string)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, '_'), $args);
    }

	/**
	 * Like JText::sprintf but tries to pluralise the string.
	 *
	 * @param	string	The format string.
	 * @param	int		The number of items
	 * @param	mixed	Mixed number of arguments for the sprintf function. The first should be an integer.
	 * @param	array	optional Array of option array('jsSafe'=>boolean, 'interpreteBackSlashes'=>boolean, 'script'=>boolean) where
	 *					-jsSafe is a boolean to generate a javascript safe string
	 *					-interpreteBackSlashes is a boolean to interprete backslashes \\->\, \n->new line, \t->tabulation
	 *					-script is a boolean to indicate that the string will be push in the javascript language store
	 * @return	string	The translated strings or the key if 'script' is true in the array of options
	 * @example	<script>alert(Joomla.JText._('<?php echo JText::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1, array("script"=>true));?>'));</script> will generate an alert message containing '1 plugin successfully disabled'
	 * @example	<?php echo JText::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1);?> it will generate a '1 plugin successfully disabled' string
	 * @since	1.6
	 */
	public function plural($string, $n)
	{
		$lang = JFactory::getLanguage();
		$args = func_get_args();
		$count = count($args);

		if ($count > 1) {
			// Try the key from the language plural potential suffixes
			$found = false;
			$suffixes = $this->getPluralSuffixes((int)$n);
			foreach ($suffixes as $suffix) {
				$key = $string.'_'.$suffix;
				if ($lang->hasKey($key)) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				// Not found so revert to the original.
				$key = $string;
			}
			if (is_array($args[$count-1])) {
				$args[0] = $lang->_($key, array_key_exists('jsSafe', $args[$count-1]) ? $args[$count-1]['jsSafe'] : false, array_key_exists('interpreteBackSlashes', $args[$count-1]) ? $args[$count-1]['interpreteBackSlashes'] : true);
				if (array_key_exists('script',$args[$count-1]) && $args[$count-1]['script']) {
					self::$strings[$key] = call_user_func_array(array($this, 'sprintf'), $args);
					return $key;
				}
			}
			else {
				$args[0] = $lang->_($key);
			}
			return call_user_func_array(array($this, 'sprintf'), $args);
		}
		elseif ($count > 0) {

			// Default to the normal sprintf handling.
			$args[0] = $lang->_($string);
			var_dump($args);
			return call_user_func_array(array($this, 'sprintf'), $args);
		}

		return '';
	}
	
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param	int $count  The number of items.
	 * @return	array  An array of potential suffixes.
	 * @since	1.6
	 */
	public static function getPluralSuffixes($count) {
		if ($count == 0) {
			$return =  array('0');
		}
		elseif($count == 1) {
			$return =  array('1');
		}
		else {
			$return = array('MORE');
		}
		return $return;
	}
}
	