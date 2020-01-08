<?php

add_action('elementor/widgets/widgets_registered', function($manager) {
	class Elementor_Table extends \Elementor\Widget_Base {

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

			$this->add_control('title', [
				'label' => 'Título',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$this->add_control('table_classes', [
				'label' => 'Classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control('title', [
				'label' => 'Título',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$repeater->add_control('width', [
				'label' => 'Largura',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$this->add_control('header', [
				'label' => 'Cabeçalho',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ title }}}',
			]);



			$repeater = new \Elementor\Repeater();

			$repeater->add_control('title', [
				'label' => 'Conteúdo (deixe vazio para nova linha)',
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'label_block' => true,
			]);

			$repeater->add_control('class', [
				'label' => 'TR/TD classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
			]);

			$this->add_control('table', [
				'label' => 'Conteúdo',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ title }}}',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = json_decode(json_encode($this->get_settings()));
	        ?>
			<?php if ($set->title): ?>
	        <h4 class="text-gray"><?php echo $set->title; ?></h4><br>
	    	<?php endif; ?>

			<table class="table <?php echo $set->table_classes; ?>">
				<colgroup>
					<?php foreach($set->header as $item): ?>
					<col width="<?php echo $item->width; ?>">
					<?php endforeach; ?>
				</colgroup>
				<thead>
					<tr>
						<?php foreach($set->header as $item): ?>
						<th><?php echo $item->title; ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr>
					<?php foreach($set->table as $item): ?>
					<?php if ($item->title): ?>
					<td class="<?php echo $item->class; ?>"><?php echo $item->title; ?></td>
					<?php else: ?>
					</tr><tr class="<?php echo $item->class; ?>">
					<?php endif; ?>
					<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
	        <?php
	    }

	    protected function content_template() {}
	}


	$manager->register_widget_type(new Elementor_Table());
});
