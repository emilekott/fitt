<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<h2 class="solo"><?php echo ((isset($this->_rootref['L_LOGIN'])) ? $this->_rootref['L_LOGIN'] : ((isset($user->lang['LOGIN'])) ? $user->lang['LOGIN'] : '{ LOGIN }')); ?> <?php echo (isset($this->_rootref['FORUM_NAME'])) ? $this->_rootref['FORUM_NAME'] : ''; ?></h2>

<form id="login_forum" method="post" action="<?php echo (isset($this->_rootref['S_LOGIN_ACTION'])) ? $this->_rootref['S_LOGIN_ACTION'] : ''; ?>">
<?php echo (isset($this->_rootref['S_FORM_TOKEN'])) ? $this->_rootref['S_FORM_TOKEN'] : ''; ?>

<div class="panel">
	<div class="inner"><span class="corners-top"><span></span></span>

	<p><?php echo ((isset($this->_rootref['L_LOGIN_FORUM'])) ? $this->_rootref['L_LOGIN_FORUM'] : ((isset($user->lang['LOGIN_FORUM'])) ? $user->lang['LOGIN_FORUM'] : '{ LOGIN_FORUM }')); ?></p>

	<fieldset class="fields2">
	<?php if ($this->_rootref['LOGIN_ERROR']) {  ?>

		<dl>
			<dt>&nbsp;</dt>
			<dd class="error"><?php echo (isset($this->_rootref['LOGIN_ERROR'])) ? $this->_rootref['LOGIN_ERROR'] : ''; ?></dd>
		</dl>
	<?php } ?>

	<dl>
		<dt><label for="password"><?php echo ((isset($this->_rootref['L_PASSWORD'])) ? $this->_rootref['L_PASSWORD'] : ((isset($user->lang['PASSWORD'])) ? $user->lang['PASSWORD'] : '{ PASSWORD }')); ?>:</label></dt>
		<dd><input class="inputbox narrow" type="password" name="password" id="password" size="25" tabindex="1" /></dd>
	</dl>
	<dl class="fields2">
		<dt>&nbsp;</dt>
		<dd><?php echo (isset($this->_rootref['S_HIDDEN_FIELDS'])) ? $this->_rootref['S_HIDDEN_FIELDS'] : ''; ?><input type="submit" name="login" id="login" class="button1" value="<?php echo ((isset($this->_rootref['L_LOGIN'])) ? $this->_rootref['L_LOGIN'] : ((isset($user->lang['LOGIN'])) ? $user->lang['LOGIN'] : '{ LOGIN }')); ?>" tabindex="2" /></dd>
	</dl>
	<?php echo (isset($this->_rootref['S_LOGIN_REDIRECT'])) ? $this->_rootref['S_LOGIN_REDIRECT'] : ''; ?>

	</fieldset>

	<span class="corners-bottom"><span></span></span></div>
</div>

</form>

<?php $this->_tpl_include('jumpbox.html'); $this->_tpl_include('overall_footer.html'); ?>