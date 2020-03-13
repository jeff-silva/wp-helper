<?php

foreach(get_post_types() as $post_type) {
	add_filter("manage_{$post_type}_posts_columns", function($columns) use($post_type) {
		$columns2 = [];

		foreach($columns as $field=>$name) {
			$columns2[$field] = $name;
			if ($field=='cb') {
				$columns2['thumbnail'] = 'Thumbnail';
			}
		}

		return $columns2;
	});

	add_action("manage_{$post_type}_posts_custom_column" , function($column, $post_id) use($post_type) {
		global $post;
		wp_enqueue_media();

		if ($column=='thumbnail') {
			$post->thumbnail = get_the_post_thumbnail_url($post->ID);
			?><div style="width:100%;red; background:#ddd;; cursor:pointer; min-height:60px;" onclick="_thumbnailImageSelector(this, <?php echo $post->ID; ?>);">
				<img src="<?php echo $post->thumbnail; ?>" alt="" style="width:100%;">
			</div>
			<script>var _thumbnailImageSelector = function(parent, post_id) {
				var $=jQuery, $parent=$(parent), $img=$parent.find('img');

				var media = wp.media({
					title: 'Selecionar Thumbnail',
					multiple : false,
					library : {type : 'image'}
				});
				
				media.on('select', function() {
					var attach = media.state().get('selection').first().toJSON();
					$img.css({opacity:.5});
					$.post('?table-thumbnail-save', {post_id:post_id, thumb_id:attach.id}, function(resp) {
						$img.attr('src', resp.url).css({opacity:1});
					}, 'json');
				});

				media.open();
			};</script>
			<style>.column-thumbnail {width:100px;}</style>
			<?php
		}
	}, 10, 2);
}


if (isset($_GET['table-thumbnail-save'])) {
	add_action('init', function() {
		set_post_thumbnail($_POST['post_id'], $_POST['thumb_id']);
		$_POST['url'] = get_the_post_thumbnail_url($_POST['post_id']);
		echo json_encode($_POST); die;
	});
}
