<?php if (!defined('IN_PHPBB')) exit; ?>Subject: Activate user account

Hello,

The account owned by "<?php echo (isset($this->_rootref['USERNAME'])) ? $this->_rootref['USERNAME'] : ''; ?>" has been deactivated or newly created, you should check the details of this user (if required) and handle it appropriately.

Use this link to view the user's profile:
<?php echo (isset($this->_rootref['U_USER_DETAILS'])) ? $this->_rootref['U_USER_DETAILS'] : ''; ?>


Use this link to activate the account:
<?php echo (isset($this->_rootref['U_ACTIVATE'])) ? $this->_rootref['U_ACTIVATE'] : ''; ?>



<?php echo (isset($this->_rootref['EMAIL_SIG'])) ? $this->_rootref['EMAIL_SIG'] : ''; ?>