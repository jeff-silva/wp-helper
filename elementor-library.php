<?php

add_action('elementor/widgets/widgets_registered', function($manager) {
	$posts = get_posts('post_type=elementor_library&post_status=any&posts_per_page=-1');

	foreach($posts as $post) {
		$class_name = "Elementor_Library_{$post->ID}";

		$class_content = <<<EOF
class {$class_name} extends \Elementor\Widget_Base {

		public function get_name() {
		    return __CLASS__;
		}

		public function get_title() {
		    return 'Elementor Library: {$post->post_title}';
		}

		public function get_icon() {
		    return 'eicon-elementor-square';
		}

		public function get_categories() {
		    return [ 'general' ];
		}

		public function get_script_depends() {
		    return [];
		}

		public function get_style_depends() {
		    return [];
		}

		protected function _register_controls() {}

		protected function render() {
			\$pluginElementor = \Elementor\Plugin::instance();
			echo \$pluginElementor->frontend->get_builder_content($post->ID);
		}

		protected function content_template() {}
	}
EOF;

		eval($class_content);
		$element = new $class_name;
		$manager->register_widget_type($element);
	}
});
