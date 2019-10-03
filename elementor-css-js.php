<?php

if ($manager) {
	class Elementor_Css_Js extends \Elementor\Widget_Base {

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

			$this->add_control('css', [
				'label' => 'CSS',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => '',
			]);

			$this->add_control('js', [
				'label' => 'JS',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => '',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = json_decode(json_encode($this->get_settings()));
	        echo "<script>{$set->js}</script>";
	        echo "<style>{$set->css}</style>";
	    }

	    protected function content_template() {}
	}

	return new Elementor_Css_Js();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});

