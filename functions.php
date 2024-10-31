<?php
/**
 * @package post-to-buffer
 * @version 0.7
 */
/*
Plugin Name: WordPress to Buffer
Plugin URI: http://flamingtarball.com/plugins/buffer/
Description: When you publish posts to your WordPress blog, this plugin gives you the option to add them to your Buffer. See http://bufferapp.com for details.
Author: Flaming Tarball
Version: 0.7
Author URI: http://flamingtarball.com/
*/

function ft_bufferapp_connectnotice() {
	if ((empty($_SERVER['SCRIPT_FILENAME']) || (basename($_SERVER['SCRIPT_FILENAME']) != 'options-general.php' && $_GET['page'] != 'ft-bufferapp'))) {
		if(!get_option('ft_bufferapp_token')) {
			printf(
				'<div id="ft-bufferapp-warning" class="updated fade"><p><strong>%s</strong> %s</p></div>',
				sprintf(__('%s is almost ready.', 'ft-bufferapp'), 'Buffer'),
				sprintf(__('Please <a href="%s">connect your blog to %s</a>.', 'ft-bufferapp'), '/wp-admin/options-general.php?page=ft-bufferapp', 'Buffer')
			);
		}
	}
}

function ft_bufferapp_syncnotice() {
	printf(
		'<div id="ft-bufferapp-notice" class="updated fade"><p>%s</p></div>',
		sprintf(__('Your %s profiles have been synchronised.', 'ft-bufferapp'), 'Buffer')
	);
}

function ft_bufferapp_admin_init() {
	add_action('admin_notices', 'ft_bufferapp_connectnotice'); 
}

function ft_bufferapp_admin_menu() {
	if (function_exists('add_submenu_page')) {
		add_submenu_page(
			'options-general.php',
			__('Buffer Settings'),
			__('Buffer'),
			'manage_options',
			'ft-bufferapp',
			'ft_bufferapp_config'
		);
	}
}

function ft_bufferapp_syncprofiles() {
	$token = get_option('ft_bufferapp_token');
	$url = 'https://api.bufferapp.com/1/profiles.json?access_token=' . urlencode($token);
	$r = wp_remote_get($url);
	
	if(!function_exists('json_decode')) {
		wp_die('A JSON library does not appear to be installed.\n\nPlease contact your server admini f you need help installing one.');
	} else {
		$response = @json_decode($r['body']);
		
		if(!isset($response) || !is_array($response)) {
			delete_option('ft_bufferapp_profiles');
			print(
				'<p>Buffer has not returned an expected result.<br />' .
				'Please check your Client ID and Token.</p>'
			);
			
			return;
		}
		
		$profiles = array();
		$selected = array();
		
		foreach($response as $profile) {
			$profiles[] = array(
				'id' => $profile->id,
				'name' => $profile->service,
				'username' => $profile->service_username,
				'verbose_name' => ucwords($profile->service) . ' (' . ($profile->service == 'twitter' ? '@' : '' ) . $profile->service_username . ')'
			);
			
			$selected[] = $profile->id;
		}
		
		update_option('ft_bufferapp_profiles', $profiles);
		update_option('ft_bufferapp_profiles_selected', $selected);
	}
}

function ft_bufferapp_metaboxes() {
	add_meta_box( 
        'ft_bufferapp',
        __('Add to Buffer', 'ft-bufferapp'),
        'ft_bufferapp_metabox',
        'post',
        'side',
        'core'
    );
}

function tp_bufferapp_truncatewords($text, $length) {
	while(strlen($text) > $length) {
		$space = strpos($text, ' ', -1);
		if($space === false) {
			$text = substr($text, 0, 100);
			$trunc = true;
			break;
		}
		
		$text = substr($text, 0, $space);
		$trunc = true;
	}
	
	if($trunc) {
		$text .= '…';
	}
	
	return $text;
}

function ft_bufferapp_publish_post($post_id) {
	$selected = get_post_meta($post_id, '_ft_bufferapp_profiles');
	$posted = get_post_meta($post_id, '_ft_bufferapp_posted', true);
	$profiles = get_option('ft_bufferapp_profiles');
	$token = get_option('ft_bufferapp_token');
	$attachments = get_children(
		array(
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' =>'image'
		)
	);
	
	if($posted || !is_array($profiles) || count($profiles) == 0) {
		return;
	}
	
	$url = 'https://api.bufferapp.com/1/updates/create.json?access_token=' . urlencode($token);
	$permalink = get_permalink($post_id);
	$post = get_post($post_id);
	$title = $post->post_title ? $post->post_title : strip_tags($post->post_content);
	$format = get_post_format($post_id);
	
	foreach($selected as $profile_id) {
		foreach($profiles as $profile) {
			if($profile['id'] == $profile_id) {
				$params = array();
				$text = $title;
				
				switch($profile['name']) {
					case 'twitter':
						$trunc = false;
						
						if($format && $format != 'standard' && $format != 'status' && $format != 'aside') {
							if($text) {
								$text = '[' . ucwords($format) . '] ' . $text;
							} else {
								$text = ucwords($format);
							}
						}
						
						$text = tp_bufferapp_truncatewords($text, 119) . ' ' . $permalink;
						break;
					default:
						$params['media[link]'] = $permalink;
						foreach ($attachments as $attachment_id => $attachment) {
							$params['media[picture]'] = wp_get_attachment_url($attachment_id, 'medium');
						}
						
						$text = tp_bufferapp_truncatewords($text, 119) . ' ' . $permalink;
						break;
				}
				
				$params['text'] = $text;
				$params['profile_ids[]'] = $profile_id;
				
				wp_remote_post($url, array('body' => $params, 'blocking' => false));
				break;
			}
		}
	}
	
	add_post_meta($post_id, '_ft_bufferapp_posted', true);
}

function ft_bufferapp_xmlrpc_publish_post($post_id) {
	$posted = get_post_meta($post_id, '_ft_bufferapp_posted', true);
	$profiles = get_option('ft_bufferapp_profiles');
	$token = get_option('ft_bufferapp_token');
	$attachments = get_children(
		array(
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' =>'image'
		)
	);
	
	if($posted || !is_array($profiles) || count($profiles) == 0) {
		return;
	}
	
	$url = 'https://api.bufferapp.com/1/updates/create.json?access_token=' . urlencode($token);
	$permalink = get_permalink($post_id);
	$post = get_post($post_id);
	$title = $post->post_title ? $post->post_title : strip_tags($post->post_content);
	$format = get_post_format($post_id);
	
	foreach($profiles as $profile) {
		$params = array();
		$text = $title;
		$count = 0;
		
		switch($profile['name']) {
			case 'twitter':
				$trunc = false;
				
				if($format && $format != 'standard' && $format != 'status' && $format != 'aside') {
					if($text) {
						$text = '[' . ucwords($format) . '] ' . $text;
					} else {
						$text = ucwords($format);
					}
				}
									
				$text = tp_bufferapp_truncatewords($text, 119) . ' ' . $permalink;
				break;
			default:
				$params['media[link]'] = $permalink;
				foreach ($attachments as $attachment_id => $attachment) {
					$params['media[picture]'] = wp_get_attachment_url($attachment_id, 'medium');
				}
				
				$text = tp_bufferapp_truncatewords($text, 119) . ' ' . $permalink;
				break;
		}
		
		$params['text'] = $text;
		$params['profile_ids[]'] = $profile['id'];
		
		wp_remote_post($url, array('body' => $params, 'blocking' => false));
	}
	
	add_post_meta($post_id, '_ft_bufferapp_posted', true);
}

add_action('admin_init', 'ft_bufferapp_admin_init');
add_action('admin_menu', 'ft_bufferapp_admin_menu');
add_action('add_meta_boxes', 'ft_bufferapp_metaboxes');
add_action('save_post', 'ft_bufferapp_save_post');
add_action('publish_post', 'ft_bufferapp_publish_post');
add_action('publish_future_post', 'ft_bufferapp_publish_post');
add_action('xmlrpc_publish_post', 'ft_bufferapp_xmlrpc_publish_post');

require_once(dirname(__FILE__) . '/admin.php');
require_once(dirname(__FILE__) . '/meta.php');