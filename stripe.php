<?php
	if(!empty($_POST['api_key']))
		PluginData::set('api_key', $_POST['api_key']);
	$api_key = PluginData::get('api_key');
?>
<style>
	.vbx-stripe form {
		padding: 20px 5%;
	}
</style>
<div class="vbx-content-main">
	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Stripe.com Settings</h2>
	</div>
    <div class="vbx-table-section vbx-stripe">
		<form method="post" action="">
			<fieldset class="vbx-input-container">
				<p>
					<label class="field-label">API Key<br/>
						<input type="password" name="api_key" class="medium" value="<?php echo htmlentities($api_key); ?>" />
					</label>
				</p>
				<p><button type="submit" class="submit-button"><span>Save</span></button></p>
			</fieldset>
		</form>
    </div>
</div>
