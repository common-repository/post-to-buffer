<?php
require_once(dirname(__FILE__) . '/../../../wp-blog-header.php');
/* require_once(dirname(__FILE__) . '/oauth.php'); */

if(isset($_GET['state']) && isset($_GET['code'])) {
	$key = get_option('ft_bufferapp_consumer_key');
	$secret = get_option('ft_bufferapp_consumer_secret');
	
	$url = 'https://api.bufferapp.com/1/oauth2/token.json';
	$fields = array(
		'client_id' => $key,
		'client_secret' => $secret,
		'redirect_uri' => plugins_url('post-to-buffer/callback.php'),
		'code' => $_GET['code'],
		'grant_type' => 'authorization_code'
	);
	
	$r = wp_remote_post($url, array('body' => $fields));
	if(!function_exists('json_decode')) {
		wp_die('A JSON library does not appear to be installed.\n\nPlease contact your server admini f you need help installing one.');
	} else {
		$response = @json_decode($r['body']);
		if(isset($response->error)) {
			wp_die($status . ' ' . $response->error);
		} elseif(isset($response->access_token)) {
			update_option('ft_bufferapp_token', $response->access_token);
			wp_redirect('/wp-admin/options-general.php?page=ft-bufferapp');
		} else {
			wp_die('Unexpected response from Bufferapp');
		}
	}
} else {
	wp_die('Missing state, code or both');
}
?>