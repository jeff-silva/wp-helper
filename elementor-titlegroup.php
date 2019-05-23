<?php

if ($manager) {
	class Elementor_Titlegroup extends \Elementor\Widget_Base {

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
				'label' => 'Title',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => 'My title',
			]);

			$this->add_control('subtitle', [
				'label' => 'Title',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => 'My subtitle',
			]);

			$this->add_control('classes', [
				'label' => 'Classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = (object) $this->get_settings();
	        ?>
			<div class="<?php echo $set->classes; ?>">
				<h2 class="elementor-titlegroup-title"><?php echo $set->title; ?></h2>
				<span class="elementor-titlegroup-subtitle"><?php echo $set->subtitle; ?></span>
			</div>
	        <?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Titlegroup();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});

