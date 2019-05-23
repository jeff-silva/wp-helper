<?php

if ($manager) {
	class Elementor_Woocommerce_Products extends \Elementor\Widget_Base {

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

			$this->add_control('query', [
				'label' => 'Query',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => "post_type=product\nposts_per_page=12",
			]);

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

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = (object) $this->get_settings();
	        $set->id = uniqid('elementor-woocommerce-products');

	        $query = json_decode($set->query, true);
	        if (! is_array($query)) {
	        	$query = str_replace("\n", '&', $set->query);
	        	parse_str($query, $query);
	        }

	        if ($set->merge_url) {
	        	$query = array_merge($_GET, $query);
	        }

	        $query = new WP_Query($query);

	        ?>
	        <div id="<?php echo $set->id; ?>" style="display:none;">
	        	<?php
				wc_get_template_part('loop/loop-start');
				while ( $query->have_posts() ) : $query->the_post(); global $product;
					wc_get_template_part( 'content', 'product' );
				endwhile;
				wc_get_template_part('loop/loop-end');


				if ($set->pagination) {
					$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
					
					echo '<br><div style="clear:both;"><ul class="pagination" style="margin:0 auto;">';

					for($p=1; $p<=$query->max_num_pages; $p++) {
						$active = $p==$paged? 'active': null;
						$href = '?' . http_build_query(array_merge($_GET, ['paged'=>$p]));
						echo "<li class='page-item {$active}'><a href='{$href}' class='page-link'>{$p}</a></li>";
					}

					// foreach ( $pages as $page ) { echo "<li class='page-item'>$page</li>"; }
					echo '</ul></div>';
				}


	        	?>
	        </div>

	        <script>
	        jQuery(document).ready(function($) {
	        	var $parent = $("#<?php echo $set->id; ?>");
	        	$parent.find(">div").addClass("<?php echo $set->row_class; ?>");
	        	$parent.find(">div>div").each(function() {
	        		var classes = this.className.split(" ").filter(function(el) {
	        			return !/^col/.test(el);
	        		}).join(" ");
	        		$(this).attr("class", "<?php echo $set->col_class; ?> "+classes);
	        	});
	        	$parent.find("ul.pagination a").addClass("page-link");
	        	$parent.fadeIn(200);
	        });
	        </script>
	        <?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Woocommerce_Products();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});
