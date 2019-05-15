<?php

global $wp_helper;
$wp_helper = $wp_helper? $wp_helper: new stdClass;
$wp_helper->dir = __DIR__;


// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');


if (! function_exists('dd')) {
	function dd() {
		foreach(func_get_args() as $data) {
			echo '<pre>'. print_r($data, true) .'</pre>';
		}
	}
}

$wp_helper->includes = [];

$wp_helper->includes[] = (object) [
	'title' => 'Woocommerce Filtros',
	'file' => 'woocommerce-search-filter.php',
];

foreach($wp_helper->includes as $include) {
	include __DIR__ .'/'. $include->file;
}

