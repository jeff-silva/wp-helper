<?php

add_action('elementor/widgets/widgets_registered', function($manager) {

	class Elementor_Grid extends \Elementor\Widget_Base {

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

			$repeater = new \Elementor\Repeater();

			$repeater->add_control('title', [
				'label' => 'Título',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$repeater->add_control('text', [
				'label' => 'Chamada',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '',
				'label_block' => true,
			]);

			$repeater->add_control('link', [
				'label' => 'Link',
				'type' => \Elementor\Controls_Manager::URL,
				'default' => ['url'=>''],
				'label_block' => true,
			]);

			$repeater->add_control('image', [
				'label' => 'Imagem',
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => ['url'=>''],
				'label_block' => true,
			]);

			$this->add_control('items', [
				'label' => 'Itens',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ title }}}',
			]);

			$this->add_control('eval', [
				'label' => 'Template (use $index e $item)',
				'type' => \Elementor\Controls_Manager::CODE,
				'label_block' => true,
				'default' => '<div class="elementor-grid-each">
	<div class="elementor-grid-each-title">
		<?php echo $item->title; ?>
	</div>
	<div class="elementor-grid-each-text">
		<?php echo $item->text; ?>
	</div>

	<?php if ($item->link->url): ?>
	<div class="elementor-grid-each-cta">
		<a href="<?php echo $item->link->url; ?>" class="btn btn-primary">
			Leia mais
		</a>
	</div>
	<?php endif; ?>
</div>
<br>',
			]);

		    $this->end_controls_section();
		}

		protected function render() {
		    $set = json_decode(json_encode($this->get_settings()));

		    $data = new stdClass;
		    $data->id = uniqid('elementor-grid-');

		    $content = explode('{{ content }}', $set->content);
		    $content[0] = isset($content[0])? $content[0]: '';
		    $content[1] = isset($content[1])? $content[1]: '';

		    ?>
			<div class="<?php echo $data->id; ?>">
				<div class="row">
					<?php foreach($set->items as $index=>$item): ?>
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

	$manager->register_widget_type(new Elementor_Grid());
});
