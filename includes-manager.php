<?php

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
