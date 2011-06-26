<?php
	$api_key = PluginData::get('api_key');
?>
<div class="vbx-applet">
<?php if(!empty($api_key)): ?>
	<div class="vbx-full-pane">
		<h3>Amount</h3>
		<p>A positive integer in cents representing much to charge the card.</p>
		<fieldset class="vbx-input-container">
			<input type="text" name="amount" class="medium" value="<?php echo AppletInstance::getValue('amount'); ?>" />
		</fieldset>
		<h3>Description</h3>
		<fieldset class="vbx-input-container">
			<input type="text" name="description" class="medium" value="<?php echo AppletInstance::getValue('description'); ?>" />
		</fieldset>
	</div>
	<h2>Payment successful</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('success'); ?>
	</div><!-- .vbx-full-pane -->
	<h2>Payment failed</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('fail'); ?>
	</div><!-- .vbx-full-pane -->
<?php else: ?>
	<div class="vbx-full-pane">
		<h3>Please set your Stripe.com API key first.</h3>
	</div>
<?php endif; ?>
</div><!-- .vbx-applet -->
