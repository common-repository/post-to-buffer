<?php function ft_bufferapp_save_post($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	
	if (!wp_verify_nonce($_POST['ft_bufferapp_nonce'], plugin_basename(__FILE__))) {
		return;
	}
	
	$profiles = $_POST['ft_bufferapp_profiles'];
	delete_post_meta($post_id, '_ft_bufferapp_profiles');
	if(is_array($profiles)) {
		foreach($profiles as $profile) {
			add_post_meta($post_id, '_ft_bufferapp_profiles', $profile);
		}
	}
}

function ft_bufferapp_metabox($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'ft_bufferapp_nonce');
	
	$profiles = get_option('ft_bufferapp_profiles');
	$selected = get_option('ft_bufferapp_profiles_selected');
	
	if($post->ID) {
		if(get_post_meta($post->ID, '_ft_bufferapp_profiles', true) != null) {
			$selected = get_post_meta($post->ID, '_ft_bufferapp_profiles');
		}
		
		$posted = get_post_meta($post->ID, '_ft_bufferapp_posted', true);
	} else {
		$posted = false;
	}
	
	if(!is_array($selected)) {
		$selected = array();
	} ?>
	
	<p><?php if(!$posted) {
		if($profiles && is_array($profiles)) {
			foreach($profiles as $profile) { ?>
				<label><input type="checkbox" name="ft_bufferapp_profiles[]" value="<?php echo $profile['id']; ?>"<?php if(in_array($profile['id'], $selected)) { echo 'checked'; } ?> /> <?php echo $profile['verbose_name']; ?></label>
				<br />
			<?php }
		}
	} else { ?>
		<?php print(__('This post has already been added to your buffer.', 'ft-bufferapp'));
	} ?>
	</p>
<?php } ?>