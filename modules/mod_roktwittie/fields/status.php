<?php
/**
 * RokTwittie Module
 *
 * @package RocketTheme
 * @subpackage roktwittie
 * @version   1.5 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die();
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldStatus extends JFormField {

	const COLOR_GREEN = 1;
	const COLOR_YELLOW = 2;
	const COLOR_RED = 3;

	private $_colors = array(self::COLOR_GREEN => 'green', self::COLOR_YELLOW => '#FF9900', self::COLOR_RED => 'red');
    
    public function getInput() {
        
		$document 	=& JFactory::getDocument();
		$document->addStyleSheet(JURI::Root(true)."/modules/mod_roktwittie/admin/css/admin.css");

        if (!extension_loaded('curl')) {
			return $this->getStatus('CURL extension is not enabled, contact your administrator.', self::COLOR_RED);
		}
		
		if (!$this->form->getValue('use_oauth', 'params', 0)) {
			return $this->getStatus('Using anonymous mode.', self::COLOR_GREEN);
		}

		if (!$this->form->getValue('consumer_key', 'params', '') || !$this->form->getValue('consumer_secret', 'params')) {
			return $this->getStatus('Consumer keys are not setup! Using anonymous mode.', self::COLOR_RED);
		}

		if (!$this->form->getValue('oauth_token', 'params') || !$this->form->getValue('oauth_token_secret', 'params')) {
			return $this->getStatus('Authentication is not completed! Using anonymous mode.', self::COLOR_YELLOW);
		}

		return $this->getStatus('Using authenticated mode.', self::COLOR_GREEN);
    }

	private function getStatus($message, $color = self::COLOR_GREEN)
	{
		return '<span style="color:' . $this->_colors[$color] . ';display:block;font-size: 13px;padding: 5px;width: 100%;margin-bottom:10px;">' . $message . '</span>';
	}
}