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

		protected function _register_controls() {
			\$this->start_controls_section('section_heading', [
				'label' => 'Configurações',
			]);

			\$this->add_control('script', [
				'label' => 'Javascript',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => '',
				'label_block' => true,
			]);

			\$this->add_control('style', [
				'label' => 'CSS',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => '',
				'label_block' => true,
			]);

			\$this->end_controls_section();
		}

		protected function render() {
			\$set = (object) \$this->get_settings();

			echo \Elementor\Plugin::\$instance->frontend->get_builder_content($post->ID, true);
			echo '<script>'. \$set->script .'</script>';
			echo '<style>'. \$set->style .'</style>';

			if (isset(\$_GET['action']) AND in_array(\$_GET['action'], ['elementor', 'elementor_ajax'])): ?>
			<style>body.elementor-editor-active .elementor-{$post->ID}:after {content:"Este elemento foi importado da biblioteca do Elementor. Para editá-lo, acesse a opção 'Modelos > {$post->post_title}' do painel"; position:absolute; top:0px; left:0px; width:100%; height:100%; background:#00000044; z-index:99; text-align:center; padding:50px 30% 0 30%; color:#fff;}</style>
			<?php endif;
		}

		protected function content_template() {}
	}
EOF;

		eval($class_content);
		$element = new $class_name;
		$manager->register_widget_type($element);
	}
});
