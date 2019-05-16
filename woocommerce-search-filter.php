<?php

define('ATTR_COLOR', 'attr_color');
define('ATTR_IMAGE', 'attr_image');
define('ATTR_SIGLA', 'attr_sigla');

define('CATEGORY_KEY', 'category');
define('WOO_SHOP', get_permalink(wc_get_page_id('shop')));

function woocommerce_search_filter() {
	global $wp_query;

	$input = (object) $_GET;

	$input->min_price = $input->min_price? $input->min_price: 0;
	$input->min_price = preg_replace('/[^0-9]/', '', $input->min_price);

	$input->max_price = $input->max_price? $input->max_price: 100000;
	$input->max_price = preg_replace('/[^0-9]/', '', $input->max_price);

	$widgetRender = function($title, $call) {
		echo "<aside class='widget woocommerce widget_product_categories'><h4 class='widget-title'>{$title}<span class='toggle'></span></h4><div>";
		call_user_func($call, $this);
		echo '</div></aside>';
	};

	echo '<form action="'. WOO_SHOP .'" method="get" id="wc_filter">';

	$widgetRender('Pesquisar', function() {
		$input = (object) $_GET;
		?>
		<div class="input-group input-group-search">
			<input type="text" name="s" value="<?php echo $input->s; ?>" class="form-control" placeholder="Pesquisar" >
			<div class="input-group-btn">
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-search"></i>
				</button>
			</div>
		</div>
		<?php
	});

	$widgetRender('Categorias', function() {
		$_href = function($term) {
			$params = $_GET;
			$params['post_type'] = 'product';
			$params[CATEGORY_KEY] = $term->slug;
			return WOO_SHOP .'?'. http_build_query($params);
		};

		$category = isset($_GET[CATEGORY_KEY])? $_GET[CATEGORY_KEY]: '';
		$categories = get_terms('product_cat', array('hide_empty' => false));

		?>
		<ul class="nav-products-categories">
			<?php foreach($categories as $term1): if ($term1->parent==0): ?>
			<li class="cart-item <?php echo "cart-item-{$term1->slug}"; ?>">
				<a href="<?php echo $_href($term1); ?>"><?php echo $term1->name; ?></a>
				<ul>
					<?php foreach($categories as $term2): if ($term2->parent==$term1->term_id): ?>
					<li class="cart-item <?php echo "cart-item-{$term2->slug}"; ?>">
						<a href="<?php echo $_href($term2); ?>"><?php echo $term2->name; ?></a>
						<ul>
							<?php foreach($categories as $term3): if ($term3->parent==$term2->term_id): ?>
							<li class="cart-item <?php echo "cart-item-{$term3->slug}"; ?>">
								<a href="<?php echo $_href($term3); ?>"><?php echo $term3->name; ?></a>
								<ul>
									<?php foreach($categories as $term4): if ($term4->parent==$term3->term_id): ?>
									<li class="cart-item <?php echo "cart-item-{$term4->slug}"; ?>">
										<a href="<?php echo $_href($term4); ?>"><?php echo $term4->name; ?></a>
										<!-- <ul></ul> -->
									</li>
									<?php endif; endforeach; ?>
								</ul>
							</li>
							<?php endif; endforeach; ?>
						</ul>
					</li>
					<?php endif; endforeach; ?>
				</ul>
			</li>
			<?php endif; endforeach; ?>
		</ul>

		<input type="hidden" name="<?php echo CATEGORY_KEY; ?>" value="<?php echo $category; ?>" id="hidden_category" >
		<style>.cart-item-<?php echo $category; ?> > a {font-weight:bold; text-decoration:underline}</style>
		<?php
	});

	$widgetRender('Faixa de Preço', function() use($input) {
		?><div class="price_slider_wrapper">
			<input type="hidden" name="min_price" class="form-control" value="<?php echo $input->min_price; ?>" onkeyup="updateSliderData('from', this.value);">
			<input type="hidden" name="max_price" class="form-control" value="<?php echo $input->max_price; ?>" onkeyup="updateSliderData('to', this.value);">
			<input type="text" class="form-control wc-filter-range">
		</div><?php
	});

	$index = 0;
	$taxos = wc_get_attribute_taxonomies();
	foreach($taxos as $taxo) {
		$taxo->terms = get_terms(wc_attribute_taxonomy_name($taxo->attribute_name), 'hide_empty=0');
		$widgetRender($taxo->attribute_label, function() use($taxo, $index) {
			foreach($taxo->terms as $term) {
				$value = null;

				if (isset($_GET[$term->taxonomy][$term->slug]) AND !empty($_GET[$term->taxonomy][$term->slug])) {
					$value = $_GET[$term->taxonomy][$term->slug];
				}

				echo woocommerce_attr_icon($term, "{$term->taxonomy}[{$term->slug}]", $value) . ' &nbsp; ';
				$index++;
			}
		});
	}


	?><script>
	var updateSliderData = function(field, value) {
		var $ = jQuery;
		var data = $(".wc-filter-range").data("ionRangeSlider");
		var update = {};
		update[field] = value;
		data.update(update);
	};

	var productCategoriesActive = function() {
		var $=jQuery;
		$("#nav-products-categories li").removeClass("active");
		$("#nav-products-categories input").each(function() {
			if (this.checked) {
				$(this).closest("li").addClass("active");
			}
		});
	};

	jQuery(document).ready(function($) {
		productCategoriesActive();
		$("#nav-products-categories input").on("change", productCategoriesActive);
		$(".wc-filter-range").ionRangeSlider({
			type: "double",
			min: 0,
			max: 2000,
			from: <?php echo $input->min_price; ?>,
			to: <?php echo $input->max_price; ?>,
			grid: true,
			onChange: function(data) {
				var $=jQuery;
				$("[name=min_price]").val(data.from);
				$("[name=max_price]").val(data.to);
			},
		});
	});

	var navCategoriesToggle = function(ev) {
		var $=jQuery;
		$(ev.target).closest("li").find(">ul").slideToggle(200);
	}
	</script>

	<input type="hidden" name="post_type" value="product">
	<div class="wc_filter_footer row" style="padding:5px;">
		<div class="col-6">
			<a href="?post_type=product&s=" class="btn btn-block btn-link">Limpar</a>
		</div>
		<div class="col-6">
			<input type="submit" value="Filtrar" class="btn btn-block btn-primary">
		</div>
	</div>
	<?php

	echo '</form>';
}


add_shortcode('woocommerce-search-filter', function() {
	ob_start();
	woocommerce_search_filter();
	return ob_get_clean();
});



function woocommerce_attr_icon($term, $name=null, $value=null) {
	global $woocommerce_attr_icon_style;

	$data[ATTR_COLOR] = trim(get_term_meta($term->term_id, ATTR_COLOR, true));
	$data[ATTR_IMAGE] = trim(get_term_meta($term->term_id, ATTR_IMAGE, true));
	$data[ATTR_SIGLA] = trim(get_term_meta($term->term_id, ATTR_SIGLA, true));

	$term->is_icon = ($data[ATTR_IMAGE] OR $data[ATTR_COLOR] OR $data[ATTR_SIGLA]);

	$checked = $value==$term->slug? 'checked="checked"': null;
	
	ob_start(); ?>
	<label class="wp-check <?php echo $term->is_icon? 'wp-check-icon': 'wp-check-name'; ?>" title="<?php echo $term->name; ?>">
		<input type='checkbox' name="<?php echo $name; ?>" value="<?php echo $term->slug; ?>" <?php echo $checked; ?> >

		<?php if ($term->is_icon): ?>
		<div class="wc-check-inner" style="background:url(<?php echo $data[ATTR_IMAGE]; ?>) #<?php echo $data[ATTR_COLOR]; ?> center center no-repeat;">
			<?php echo $data[ATTR_SIGLA]; ?>
		</div>
		<?php else: ?>
		<div class="wc-check-inner">
			<?php echo $term->name; ?>
		</div>
		<?php endif; ?>
	</label>

	<?php if (! $woocommerce_attr_icon_style): ?><style>
	.wp-check {cursor:pointer; vertical-align:top;}
	.wp-check.wp-check-name {}
	.wp-check.wp-check-icon {}
	.wc-check-inner {padding:5px 7px; border:solid 3px #ddd; text-align:center; min-height:37px;}

	.wp-check.wp-check-icon .wc-check-inner {width:36px; border-radius:50%;}

	.wp-check input {display:none;}
	.wp-check input:checked ~ .wc-check-inner,
	.wc-check-inner:hover {border-color:#666; background:#eee;}
	</style><?php $woocommerce_attr_icon_style = true; endif; ?>

	<?php
	$return = ob_get_clean();

	return $return;
}



class Woocommerce_Full_Filter extends WP_Widget {

	function __construct() {
		parent::__construct('foo_widget', 'Woocommerce Filtro Completo', ['description' => 'Woocommerce Filtro Completo']);
	}

	public function widget($args, $instance) {
		woocommerce_search_filter();
	}

	public function form($instance) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		return $instance;
	}

}





add_action('pre_get_posts', function($query) {

	$tax_query = [
		'relation' => 'AND',
	];

	// ?pa_color=red
	foreach(wc_get_attribute_taxonomies() as $tax) {
		$tax->kname = "pa_{$tax->attribute_name}";
		if (isset($_GET[ $tax->kname ]) AND !empty($_GET[ $tax->kname ])) {
			
			$terms = $_GET[ $tax->kname ];
			$terms = is_array($terms)? $terms: [$terms];
			$terms = array_values($terms);

			// $tax_query = $query->query_vars['tax_query'];
			// $tax_query['relation'] = 'OR';
			$tax_query[] = [
				'taxonomy' => $tax->kname,
				'field'    => 'slug',
				'terms'    => $terms,
				// 'operator' => 'IN',
			];
			// dd($query); die;
		}
	}

	// ?{CATEGORY_KEY}=bombonieres
	if (isset($_GET[CATEGORY_KEY]) AND !empty($_GET[CATEGORY_KEY])) {
		if( !is_admin() && $query->is_main_query() ) {
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $_GET[CATEGORY_KEY],
			];
		}
	}


	$query->set('tax_query', $tax_query);
});



add_action('widgets_init', function() {
	register_widget('Woocommerce_Full_Filter');
});



add_action('edit_term', function($term_id) {
	update_term_meta($term_id, ATTR_COLOR, $_POST[ATTR_COLOR]);
	update_term_meta($term_id, ATTR_IMAGE, $_POST[ATTR_IMAGE]);
	update_term_meta($term_id, ATTR_SIGLA, $_POST[ATTR_SIGLA]);
}, 10, 3);



add_action('edit_tag_form_fields', function($term) { ?>
<table class="form-table">
	<tbody>
		<tr class="form-field form-required">
			<th scope="row" valign="top">
				<label for="attr_type">Tipo de atributo</label>
			</th>
			<td>
				
				<?php $sigla = get_term_meta($term->term_id, ATTR_SIGLA, true); ?>
				<input type="text" name="<?php echo ATTR_SIGLA; ?>" id="<?php echo ATTR_SIGLA; ?>" value="<?php echo $sigla; ?> " placeholder="Sigla" style="width:150px; background:#<?php echo $color; ?>">

				<?php $color = get_term_meta($term->term_id, ATTR_COLOR, true); ?>
				<input type="text" name="<?php echo ATTR_COLOR; ?>" id="<?php echo ATTR_COLOR; ?>" value="<?php echo $color; ?> " onclick="_colorpicker(event);" style="width:150px; background:#<?php echo $color; ?>">

				<?php $image = get_term_meta($term->term_id, ATTR_IMAGE, true); ?>
				<input type="hidden" name="<?php echo ATTR_IMAGE; ?>" id="<?php echo ATTR_IMAGE; ?>" value="<?php echo $image; ?> ">
				<input type="file" onchange="_upload(event);" style="width:250px;"><br>
				<img src="<?php echo $image; ?>" style="width:50px;" id="<?php echo ATTR_IMAGE; ?>_preview"><br>
				<a href="javascript:;" onclick="<?php echo ATTR_IMAGE; ?>.value=''; <?php echo ATTR_IMAGE; ?>_preview.src='';">Remover imagem</a>

				<?php // echo woocommerce_attr_icon($term); ?>

				<p class="description">Ícone/Cor do atributo</p>
			</td>
		</tr>
	</tbody>
</table>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.0.4/jscolor.min.js"></script> -->
<script>
var _upload = function(ev) {
	var file = ev.target.files[0] || false;
	if (! file) return;
	var reader = new FileReader();
	reader.readAsDataURL(file);
	reader.onload = function() {
		<?php echo ATTR_IMAGE; ?>.value = reader.result;
		<?php echo ATTR_IMAGE; ?>_preview.src = reader.result;
	};
};

var _colorpicker = function(ev) {
	var vm=this, $=jQuery;
	try {
		var picker = new jscolor(ev.target);
		picker.show();
		picker.onFineChange = function() {
			Vue.set(vm, "attr_value", picker.toHEXString());
		};
	}
	catch(e) {}
};
</script>
<?php });




foreach(['wp_enqueue_scripts', 'admin_enqueue_scripts'] as $hook) {
	add_action($hook, function() {
		$assets = [];
		$assets[] = ['wp_enqueue_script', 'jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.0.4/jscolor.min.js'];
		$assets[] = ['wp_enqueue_script', 'ion.rangeSlider', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/js/ion.rangeSlider.min.js'];
		$assets[] = ['wp_enqueue_style', 'ion.rangeSlider', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/css/ion.rangeSlider.min.css'];
		foreach($assets as $asset) {
			$function = array_shift($asset);

			if ($asset[1][0]=='@') {
				$asset[1][0] = str_replace('@', __DIR__, $asset[1][0]);
			}

			call_user_func_array($function, array_values($asset));
		}
	});
}
