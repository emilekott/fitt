<?php if (!defined('IN_PHPBB')) exit; if ($this->_rootref['S_TYPE'] == (1)) {  ?>

<div class="panel">
	<div class="inner"><span class="corners-top"><span></span></span>

	<h3><?php echo ((isset($this->_rootref['L_CONFIRMATION'])) ? $this->_rootref['L_CONFIRMATION'] : ((isset($user->lang['CONFIRMATION'])) ? $user->lang['CONFIRMATION'] : '{ CONFIRMATION }')); ?></h3>
	<p><?php echo ((isset($this->_rootref['L_CONFIRM_EXPLAIN'])) ? $this->_rootref['L_CONFIRM_EXPLAIN'] : ((isset($user->lang['CONFIRM_EXPLAIN'])) ? $user->lang['CONFIRM_EXPLAIN'] : '{ CONFIRM_EXPLAIN }')); ?></p>

	<fieldset class="fields2">
<?php } if ($this->_rootref['S_RECAPTCHA_AVAILABLE']) {  ?>

	<dl>
	<dt><label><?php echo ((isset($this->_rootref['L_CONFIRM_CODE'])) ? $this->_rootref['L_CONFIRM_CODE'] : ((isset($user->lang['CONFIRM_CODE'])) ? $user->lang['CONFIRM_CODE'] : '{ CONFIRM_CODE }')); ?></label>:<br /><span><?php echo ((isset($this->_rootref['L_RECAPTCHA_EXPLAIN'])) ? $this->_rootref['L_RECAPTCHA_EXPLAIN'] : ((isset($user->lang['RECAPTCHA_EXPLAIN'])) ? $user->lang['RECAPTCHA_EXPLAIN'] : '{ RECAPTCHA_EXPLAIN }')); ?></span></dt>
	<dd>
		<script type="text/javascript">
		// <![CDATA[
		var RecaptchaOptions = {
			lang : '<?php echo ((isset($this->_rootref['LA_RECAPTCHA_LANG'])) ? $this->_rootref['LA_RECAPTCHA_LANG'] : ((isset($this->_rootref['L_RECAPTCHA_LANG'])) ? addslashes($this->_rootref['L_RECAPTCHA_LANG']) : ((isset($user->lang['RECAPTCHA_LANG'])) ? addslashes($user->lang['RECAPTCHA_LANG']) : '{ RECAPTCHA_LANG }'))); ?>',
			tabindex : <?php if ($this->_tpldata['DEFINE']['.']['CAPTCHA_TAB_INDEX']) {  echo (isset($this->_tpldata['DEFINE']['.']['CAPTCHA_TAB_INDEX'])) ? $this->_tpldata['DEFINE']['.']['CAPTCHA_TAB_INDEX'] : ''; } else { ?>10<?php } ?>

		};
		// ]]>
		</script>
		<script type="text/javascript" src="<?php echo (isset($this->_rootref['RECAPTCHA_SERVER'])) ? $this->_rootref['RECAPTCHA_SERVER'] : ''; ?>/challenge?k=<?php echo (isset($this->_rootref['RECAPTCHA_PUBKEY'])) ? $this->_rootref['RECAPTCHA_PUBKEY'] : ''; echo (isset($this->_rootref['RECAPTCHA_ERRORGET'])) ? $this->_rootref['RECAPTCHA_ERRORGET'] : ''; ?>"></script>
		<script type="text/javascript">
		// <![CDATA[
		<?php if ($this->_rootref['S_CONTENT_DIRECTION'] == ('rtl')) {  ?>

			document.getElementById('recaptcha_table').style.direction = 'ltr';
		<?php } ?>

		// ]]>
		</script>
		<noscript>
		<div>
			<object data="<?php echo (isset($this->_rootref['RECAPTCHA_SERVER'])) ? $this->_rootref['RECAPTCHA_SERVER'] : ''; ?>/noscript?k=<?php echo (isset($this->_rootref['RECAPTCHA_PUBKEY'])) ? $this->_rootref['RECAPTCHA_PUBKEY'] : ''; echo (isset($this->_rootref['RECAPTCHA_ERRORGET'])) ? $this->_rootref['RECAPTCHA_ERRORGET'] : ''; ?>" type="text/html" height="300" width="500"></object><br />
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
		</div>
		</noscript>

	</dd>
	</dl>
<?php } else { ?>

<?php echo ((isset($this->_rootref['L_RECAPTCHA_NOT_AVAILABLE'])) ? $this->_rootref['L_RECAPTCHA_NOT_AVAILABLE'] : ((isset($user->lang['RECAPTCHA_NOT_AVAILABLE'])) ? $user->lang['RECAPTCHA_NOT_AVAILABLE'] : '{ RECAPTCHA_NOT_AVAILABLE }')); ?>

<?php } if ($this->_rootref['S_TYPE'] == (1)) {  ?>

	</fieldset>
	<span class="corners-bottom"><span></span></span></div>
</div>
<?php } ?>