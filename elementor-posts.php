<?php

add_action('elementor/widgets/widgets_registered', function($manager) {

	class Elementor_Posts extends \Elementor\Widget_Base {

		public function get_name() {
		    return __CLASS__;
		}

		public function get_title() {
		    return preg_replace('/[^a-zA-Z0-9]/', ' ', __CLASS__);
		}

		// https://pojome.github.io/elementor-icons/
		public function get_icon() {
		    return 'eicon-editor-code';
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
			$this->start_controls_section('section_heading', [
				'label' => 'Configurações',
			]);

			$this->add_control('columns_xs', [
				'label' => 'Colunas (Mobile)',
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default' => 'col-6',
				'options' => [
					'col-12' => '1 coluna',
					'col-6' => '2 colunas',
					'col-4' => '3 colunas',
					'col-3' => '4 colunas',
					'col-2' => '6 colunas',
				],
			]);

			$this->add_control('columns_md', [
				'label' => 'Colunas (Desktop)',
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'default' => 'col-md-4',
				'options' => [
					'col-md-12' => '1 coluna',
					'col-md-6' => '2 colunas',
					'col-md-4' => '3 colunas',
					'col-md-3' => '4 colunas',
					'col-md-2' => '6 colunas',
				],
			]);

			$this->add_control('query', [
				'label' => 'Query',
				'type' => \Elementor\Controls_Manager::CODE,
				'label_block' => true,
				'default' => json_encode(['post_type'=>'post']),
			]);

			$this->add_control('eval', [
				'label' => 'Template (use $index e $post)',
				'type' => \Elementor\Controls_Manager::CODE,
				'label_block' => true,
				'default' => '<div class="card">
    <div class="card-header font-weight-bold text-uppercase">
        <?php echo $post->post_title; ?>
    </div>
    <div class="card-body p-0">
        <?php if ($post->thumbnail): ?>
        <div style="background:url(<?php echo $post->thumbnail; ?>) center center no-repeat; background-size:cover; height:200px;"></div>
        <?php endif; ?>
        <div class="p-3"><?php echo $post->excerpt; ?></div>
    </div>
    <div class="card-footer text-right">
        <a href="<?php echo $post->permalink; ?>" class="btn btn-primary btn-sm">Leia mais</a>
    </div>
</div>
<br>',
			]);

		    $this->end_controls_section();
		}

		protected function render() {
		    $set = json_decode(json_encode($this->get_settings()));

		    $data = new stdClass;
		    $data->id = uniqid('elementor-posts-');

		    $data->query = json_decode($set->query, true);
		    $data->posts = array_map(function($post) {
		    	$post->thumbnail = get_the_post_thumbnail_url($post->ID);
		    	$post->permalink = get_the_permalink($post->ID);
		    	$post->excerpt = get_the_excerpt($post->ID);
		    	return $post;
		    }, get_posts($data->query));

		    ?>
			<div class="<?php echo $data->id; ?>">
				<div class="row">
					<?php foreach($data->posts as $index=>$post): ?>
					<div class="<?php echo $set->columns_xs; ?> <?php echo $set->columns_md; ?>" style="position:relative;">
						<?php eval(" ?>{$set->eval}<?php "); ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		    <?php
		}

		protected function content_template() {}
	}

	$manager->register_widget_type(new Elementor_Posts());
});
