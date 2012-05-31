<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<form id="confirm" method="post" action="<?php echo (isset($this->_rootref['S_CONFIRM_ACTION'])) ? $this->_rootref['S_CONFIRM_ACTION'] : ''; ?>">

<fieldset>
	<h1><?php echo (isset($this->_rootref['MESSAGE_TITLE'])) ? $this->_rootref['MESSAGE_TITLE'] : ''; ?></h1>
	<p><?php echo (isset($this->_rootref['MESSAGE_TEXT'])) ? $this->_rootref['MESSAGE_TEXT'] : ''; ?></p>

	<?php echo (isset($this->_rootref['S_HIDDEN_FIELDS'])) ? $this->_rootref['S_HIDDEN_FIELDS'] : ''; ?>


	<div style="text-align: center;">
		<input type="submit" name="confirm" value="<?php echo ((isset($this->_rootref['L_YES'])) ? $this->_rootref['L_YES'] : ((isset($user->lang['YES'])) ? $user->lang['YES'] : '{ YES }')); ?>" class="button2" />&nbsp; 
		<input type="submit" name="cancel" value="<?php echo ((isset($this->_rootref['L_NO'])) ? $this->_rootref['L_NO'] : ((isset($user->lang['NO'])) ? $user->lang['NO'] : '{ NO }')); ?>" class="button2" />
	</div>

	<h2><?php echo ((isset($this->_rootref['L_PRUNE_USERS_LIST'])) ? $this->_rootref['L_PRUNE_USERS_LIST'] : ((isset($user->lang['PRUNE_USERS_LIST'])) ? $user->lang['PRUNE_USERS_LIST'] : '{ PRUNE_USERS_LIST }')); ?></h2>
	<?php if ($this->_rootref['S_DEACTIVATE']) {  ?><p><?php echo ((isset($this->_rootref['L_PRUNE_USERS_LIST_DEACTIVATE'])) ? $this->_rootref['L_PRUNE_USERS_LIST_DEACTIVATE'] : ((isset($user->lang['PRUNE_USERS_LIST_DEACTIVATE'])) ? $user->lang['PRUNE_USERS_LIST_DEACTIVATE'] : '{ PRUNE_USERS_LIST_DEACTIVATE }')); ?></p><?php } else { ?><p><?php echo ((isset($this->_rootref['L_PRUNE_USERS_LIST_DELETE'])) ? $this->_rootref['L_PRUNE_USERS_LIST_DELETE'] : ((isset($user->lang['PRUNE_USERS_LIST_DELETE'])) ? $user->lang['PRUNE_USERS_LIST_DELETE'] : '{ PRUNE_USERS_LIST_DELETE }')); ?></p><?php } ?>


	<br />
	<?php $_users_count = (isset($this->_tpldata['users'])) ? sizeof($this->_tpldata['users']) : 0;if ($_users_count) {for ($_users_i = 0; $_users_i < $_users_count; ++$_users_i){$_users_val = &$this->_tpldata['users'][$_users_i]; ?>

	&raquo; <a href="<?php echo $_users_val['U_PROFILE']; ?>"><?php echo $_users_val['USERNAME']; ?></a><?php if ($_users_val['U_USER_ADMIN']) {  ?> [<a href="<?php echo $_users_val['U_USER_ADMIN']; ?>"><?php echo ((isset($this->_rootref['L_USER_ADMIN'])) ? $this->_rootref['L_USER_ADMIN'] : ((isset($user->lang['USER_ADMIN'])) ? $user->lang['USER_ADMIN'] : '{ USER_ADMIN }')); ?></a>]<?php } ?><br />
	<?php }} ?>


	<br /><br />

</fieldset>

</form>

<?php $this->_tpl_include('overall_footer.html'); ?>