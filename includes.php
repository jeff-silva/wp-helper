<?php

global $wph;
$wph = $wph? $wph: new stdClass;
$wph->dir = __DIR__;


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

function wph_content($url, $post=null) {
	$method = $post===null? 'GET': 'POST';
	return file_get_contents($url, false, stream_context_create([
		'http' => [
			'method' => $method,
			'header' => [
				'User-Agent: PHP',
			],
		],
	]));
}

$wph->includes = [];

$wph->includes[] = (object) [
	'file' => 'woocommerce-search-filter.php',
	'title' => 'Woocommerce Filtros',
	'description' => 'Widget de filtros Woocommerce',
];

$wph->includes[] = (object) [
	'file' => 'includes-manager.php',
	'title' => 'Includes manager',
	'description' => 'Gerenciador de includes',
];

$wph->includes[] = (object) [
	'file' => 'elementor-grid-panel.php',
	'title' => 'Elementor Grid Panel',
	'description' => 'Gerenciador de grid de elementos Elementor',
];




foreach($wph->includes as $include) {
	$include->exists = file_exists(__DIR__ .'/'. $include->file);
}

foreach($wph->includes as $include) {
	if ($include->exists) {
		include $include->file;
	}
}


foreach(['wp_enqueue_scripts', 'admin_enqueue_scripts'] as $hook) {
	add_action($hook, function() {
		$assets = [];
		$assets[] = ['wp_enqueue_script', 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js'];
		$assets[] = ['wp_enqueue_script', 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'];
		$assets[] = ['wp_enqueue_style', 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'];
		$assets[] = ['wp_enqueue_style', 'font-awesome', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css'];
		foreach($assets as $asset) {
			$function = array_shift($asset);
			call_user_func_array($function, array_values($asset));
		}
	});
}

