<?php

if (! function_exists('dd')) {
	function dd() {
		foreach(func_get_args() as $data) {
			echo '<pre>'. print_r($data, true) .'</pre>';
		}
	}
}

/* Remove WP Emoji */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

/* Autoinclude files */
array_map(function($file) {
	$file = realpath($file);
	if ($file==__FILE__) return;
	include $file;
}, glob(__DIR__ . '/*'));
