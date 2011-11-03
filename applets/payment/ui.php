<?php
	$settings = PluginData::get('settings');
	if(is_object($settings))
		$settings = get_object_vars($settings);
?>
<div class="vbx-applet">
<?php if(!empty($settings) && !empty($settings['api_key'])): ?>
	<div class="vbx-full-pane">
		<h3>Amount</h3>
		<p>A positive integer in cents representing much to charge the card.</p>
		<fieldset class="vbx-input-container">
			<input type="text" name="amount" class="medium" value="<?php echo AppletInstance::getValue('amount', 50); ?>" />
		</fieldset>
		<h3>Description</h3>
		<fieldset class="vbx-input-container">
			<input type="text" name="description" class="medium" value="<?php echo AppletInstance::getValue('description'); ?>" />
		</fieldset>
	</div>
	<h2>After the payment</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('success'); ?>
	</div>
	<h2>If the payment fails</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('fail'); ?>
	</div>
<?php else: ?>
	<div class="vbx-full-pane">
		<h3>Please set your Stripe.com settings first.</h3>
	</div>
<?php endif; ?>
</div>
