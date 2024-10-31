<?php function ft_bufferapp_config() {
	$key = get_option('ft_bufferapp_consumer_key');
	$token = get_option('ft_bufferapp_token');
	$profiles = get_option('ft_bufferapp_profiles');
	$selected = get_option('ft_bufferapp_profiles_selected');
	
	if (isset($_POST['submit'])) {
		if (function_exists('current_user_can') && !current_user_can('manage_options')) {
			die(__('Cheatin&#8217; uh?'));
		}
		
		check_admin_referer('ft-bufferapp-config');
		$key = $_POST['ft_bufferapp_consumer_key'];
		$token = $_POST['ft_bufferapp_token'];
		
		if($key && $token) {
			update_option('ft_bufferapp_consumer_key', $key);
			update_option('ft_bufferapp_token', $token);
			
			ft_bufferapp_syncprofiles();
			$profiles = get_option('ft_bufferapp_profiles');
			$selected = get_option('ft_bufferapp_profiles_selected');
		}
	} elseif(isset($_POST['update'])) {
		$selected = array();
		
		if(isset($_POST['profiles'])) {
			foreach($_POST['profiles'] as $profile) {
				$selected[] = $profile;
			}
		}
		
		update_option('ft_bufferapp_profiles_selected', $selected);
	} elseif (isset($_POST['clear'])) {
		delete_option('ft_bufferapp_consumer_key');
		delete_option('ft_bufferapp_token');
		delete_option('ft_bufferapp_profiles');
		delete_option('ft_bufferapp_profiles_selected');
		
		$key = null;
		$token = null;
		$profiles = none;
	} elseif(isset($_POST['sync'])) {
		if($key && $token) {
			ft_bufferapp_syncprofiles();
			$profiles = get_option('ft_bufferapp_profiles');
			$selected = get_option('ft_bufferapp_profiles_selected');
		}
	} elseif($key && $token && !$profiles) {
		ft_bufferapp_syncprofiles();
		$profiles = get_option('ft_bufferapp_profiles');
		$selected = get_option('ft_bufferapp_profiles_selected');
	}
	
	if(!is_array($selected) && $profiles && is_array($profiles)) {
		$selected = array();
		foreach($profiles as $profile) {
			$selected[] = $profile['id'];
		}
	}
	
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php printf(__('%s settings', 'ft-bufferapp'), 'Buffer'); ?></h2>
		
		<?php if(!$key || !$token || !$profiles) { ?>
			<div class="narrow">
				<h3><?php print(__('First, create an app that your blog can use', 'ft-bufferapp')); ?></h3>
				<ol>
					<li><?php printf(
						__('Start by <a href="%s" target="_blank"><strong>creating an app</strong></a> on %s', 'ft-bufferapp'),
						'http://bufferapp.com/developers/apps/create', 'bufferapp.com'
					); ?>
						<ul>
							<li><?php printf(
								__('<strong>Important:</strong> Set the callback URL to <code>%s</code>', 'ft-bufferapp'),
								plugins_url('post-to-buffer/callback.php')
							); ?></li>
						</ul>
					</li>
					<li><?php print(
						__('Put the Client ID and Token in the boxes below', 'ft-bufferapp')
					); ?></li>
				</ol>
				
				<form method="post">
					<div>
						<h3><?php print(__('App details', 'ft-bufferapp')); ?></h3>
						<table>
							<tbody>
								<tr>
									<td><label for="ft_bufferapp_consumer_key">Client ID</label></td>
									<td><input id="ft_bufferapp_consumer_key" name="ft_bufferapp_consumer_key" type="text" value="<?php echo $key; ?>" /></td>
								</tr>
								
								<tr>
									<td><label for="ft_bufferapp_consumer_url">Redirect URL</label></td>
									<td><input id="ft_bufferapp_consumer_url" type="text" readonly value="<?php echo plugins_url('post-to-buffer/callback.php'); ?>" /></td>
								</tr>
								
								<tr>
									<td><label for="ft_bufferapp_token">Access token</label></td>
									<td><input id="ft_bufferapp_token" name="ft_bufferapp_token" type="text" value="<?php echo $token; ?>" /><br /></td>
								</tr>
								
								<tr>
									<td></td>
									<td>
										<?php printf(__('This is not the same as Your Token. You need to wait for the email from %s', 'ft-bufferapp'), 'Buffer'); ?>
									</td>
								</tr>
							</tbody>
						</table>
						
						<?php wp_nonce_field('ft-bufferapp-config'); ?>
						<p class="submit">
							<input type="submit" name="submit" value="<?php _e('Update options &raquo;', 'ft-bufferapp'); ?>" />
							<?php if($key && $token) { ?>
							<input type="submit" name="clear" value="<?php _e('Start again', 'ft-bufferapp'); ?>" />
							<?php } ?>
						</p>
					</div>
				</form>
			</div>
		<?php } ?>
		
		<?php if($key && $token && $profiles) { ?>
			<h3><?php printf(__('Profiles', 'ft-bufferapp')); ?></h3>
			<p>
				<?php printf(__('We\'ve found the following profiles in your %s account.<br />When you create a post, you\'ll be able to choose which services to post to.', 'ft-bufferapp'), 'Buffer'); ?>
			</p>
			
			<form method="post">
				<ul>
					<?php foreach($profiles as $profile) { ?>
						<li>
							<label>
								<input name="profiles[]" type="checkbox" value="<?php echo $profile['id']; ?>"<?php if(in_array($profile['id'], $selected)) echo ' checked'; ?> />
								<?php echo $profile['verbose_name']; ?>
							</label>
						</li>
					<?php } ?>
				</ul>
				
				<p>
					<?php printf(
						__('If you\'ve made any changes, you can synchronise this blog with your %s account again.', 'ft-bufferapp'),
						'Buffer'
					); ?>
				</p>
				
				<p class="submit">
					<input type="submit" name="update" class="button-primary" value="<?php _e('Save Changes', 'bt-bufferapp'); ?>" />
					<input type="submit" name="sync" value="<?php _e('Sync My Profiles &raquo;', 'bt-bufferapp'); ?>" />
					<input type="submit" name="clear" value="<?php _e('Clear My Details', 'bt-bufferapp'); ?>" />
				</p>
			</form>
		<?php } ?>
	</div>
<?php } ?>