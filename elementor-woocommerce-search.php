<?php

/*
Name: Elementor Woocommerce Search
Description: Lista de produtos Woocommerce para Elementor
Version: 1.0.0
*/

if ($manager) {
	class Elementor_Woocommerce_Search extends \Elementor\Widget_Base {

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

			$this->add_control('cols_order', [
				'label' => 'Query',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => 'results-search',
				'options' => [
					'search-results' => 'Pesquisa > Resultados',
					'results-search' => 'Resultados > Pesquisa',
				],
			]);

			$this->add_control('results_row_classes', [
				'label' => 'Results classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'col-12 col-md-8',
			]);

			$this->add_control('search_row_classes', [
				'label' => 'Search classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'col-12 col-md-4',
			]);

			$this->add_control('query_default', [
				'label' => 'Default query',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => "post_type=product\nposts_per_page=16",
			]);

			/*

			$this->add_control('merge_url', [
				'label' => 'Unir com URL?',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '1',
				'options' => [
					'0' => 'Não',
					'1' => 'Sim',
				],
			]);

			$this->add_control('pagination', [
				'label' => 'Pagination',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '1',
				'options' => [
					'0' => 'Não exibir',
					'1' => 'Exibir',
				],
			]);

			$this->add_control('row_class', [
				'label' => 'Row classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'row',
			]);

			$this->add_control('col_class', [
				'label' => 'Col classes',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'col-12 col-md-4 col-lg-3',
			]);
			*/

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = (object) $this->get_settings();
	        $set->id = uniqid('elementor-woocommerce-search');
	        ?>
	        <div class="row">
	        	<?php foreach(explode('-', $set->cols_order) as $col) {
	        		if ($col=='search') {
	        			echo "<div class='{$set->search_row_classes}'>";
	        			$this->search();
	        			echo "</div>";
	        		}
	        		else if ($col=='results') {
	        			echo "<div class='{$set->results_row_classes}'>";
	        			$this->results();
	        			echo "</div>";
	        		}
	        	} ?>
	        </div>
	        <?php
	    }

	    protected function content_template() {}

	    protected function search()
	    {
	    	global $wp_query;

	    	if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
	        	$query_default = str_replace("\n", '&', $set->query_default);
	        	$wp_query = new Wp_Query($query_default);
	        }

	    	wc_get_template_part('loop/loop-start');
			while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $product;
				wc_get_template_part( 'content', 'product' );
			endwhile;
			wc_get_template_part('loop/loop-end');
	    }

	    protected function results()
	    {
			if (! function_exists('woocommerce_search_filter')) {
				echo 'Default filter';
				return false;
			}

			woocommerce_search_filter();
	    }
	}

	return new Elementor_Woocommerce_Search();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});
