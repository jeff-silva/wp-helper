<?php

if (isset($_GET['wph-include-download'])) {
	add_action('init', function() {
		$file = $_GET['wph-include-download'];
		$content = "https://raw.githubusercontent.com/jeff-silva/wp-helper/master/{$file}";
		file_put_contents(__DIR__ .'/'. $file, $content);
		wp_redirect($_SERVER['HTTP_REFERER']);
	});
}

add_action('admin_menu', function() {
	add_submenu_page('options-general.php', 'Includes manager', 'Includes manager', 'manage_options', 'wph-includes-manager', function() {
		global $wph;
		?><br>
		<table class="table table-bordered">
			<colgroup>
				<col width="*">
				<col width="350px">
				<col width="150px">
			</colgroup>
			<thead>
				<tr>
					<th>Name</th>
					<th>File</th>
					<th>Download</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($wph->includes as $include): ?>
				<tr>
					<td>
						<div><strong><?php echo $include->title; ?></strong></div>
						<div><small class="text-muted"><?php echo $include->description; ?></small></div>
					</td>
					<td><?php echo $include->file; ?></td>
					<td>
						<a href="?wph-include-download=<?php echo $include->file; ?>" class="btn btn-secondary btn-block"><?php echo $include->exists? 'Refresh': 'Download'; ?></a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	});
});
