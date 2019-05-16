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


/* Includes manager */
if (isset($_GET['wph-update'])) {
	add_action('init', function() {
		$file = $_GET['wph-update'];
		$content = wph_content("https://raw.githubusercontent.com/jeff-silva/wp-helper/master/{$file}");
		file_put_contents(__DIR__ .'/'. $file, $content);
		wp_redirect($_SERVER['HTTP_REFERER']);
	});
}

add_action('admin_menu', function() {
	add_submenu_page('options-general.php', 'Includes manager', 'Includes manager', 'manage_options', 'wph-includes-manager', function() {
		global $wph;

		$files = wph_content('https://api.github.com/repos/jeff-silva/wp-helper/contents/');
		$files = json_decode($files);
		$files = is_array($files)? $files: [];
		foreach($files as $i=>$file) {
			if (in_array($file->name, ['README.md'])) {
				unset($files[$i]);
				continue;
			}
			$file->file_exists = file_exists(__DIR__ .'/'. $file->name);
		}

		?><br>
		<table class="table table-bordered">
			<colgroup>
				<col width="*">
				<col width="150px">
			</colgroup>
			<thead>
				<tr>
					<th>Name</th>
					<th>Download</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($files as $file): ?>
				<tr>
					<td>
						<div><strong><?php echo $file->name; ?></strong></div>
					</td>
					<td>
						<a href="?wph-update=<?php echo $file->name; ?>" class="btn btn-secondary btn-block"><?php echo $file->file_exists? 'Refresh': 'Download'; ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	});
});
