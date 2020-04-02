<?php

add_action('elementor/widgets/widgets_registered', function($manager) {
	class Elementor_Nav_Social_Walker extends Walker_Nav_Menu {
		function start_el(&$output, $item, $depth=0, $args=[], $id=0) {
			$socials = [
				'facebook.com' => (object) [
					'name' => 'facebook',
					'icon' => 'fa fa-fw fa-facebook',
				],
				'twitter.com' => (object) [
					'name' => 'twitter',
					'icon' => 'fa fa-fw fa-twitter',
				],
				'instagram.com' => (object) [
					'name' => 'instagram',
					'icon' => 'fa fa-fw fa-instagram',
				],
				'linkedin.com' => (object) [
					'name' => 'linkedin',
					'icon' => 'fa fa-fw fa-linkedin',
				],
			];

			foreach($socials as $find=>$social) {
				if (strpos($item->url, $find) !== false) {
					$output .= "<li><a href=\"{$item->url}\"><i class=\"{$social->icon}\"></i></a>";
					break;
				}
			}
		}
	}

	class Elementor_Nav extends \Elementor\Widget_Base {

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

			$nav_options = [];
			foreach(wp_get_nav_menus() as $menu) {
				$nav_options[ $menu->slug ] = $menu->name;
			}

			$this->add_control('nav', [
				'label' => 'Menu',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'label_block' => true,
				'options' => $nav_options,
			]);

			$this->add_control('walker', [
				'label' => 'Estilo',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'label_block' => true,
				'options' => [
					'Elementor_Nav_Social_Walker' => 'Ícones de redes sociais',
					'Elementor_Nav_Responsive' => 'Menu responsivo',
				],
			]);

			$this->add_control('fixed', [
				'label' => 'Fixo',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => false,
				'label_block' => true,
			]);

			$this->add_control('css', [
				'label' => 'CSS',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => "\$desktop {}\n\$mobile {}",
				'label_block' => true,
			]);

			/*
			$this->add_control('text', [
				'label' => 'Texto',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quo aut magni perferendis repellat rerum assumenda, facere. Alias deserunt pariatur magnam rerum quod voluptates, quidem id labore quam. Illum, nemo, minus?</p>',
			]);

			$this->add_control('thanks_test', [
				'label' => 'Testar tela de agradecimento',
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => false,
				'label_block' => true,
			]);

			$this->add_control('thanks_title', [
				'label' => 'Título do agradecimento',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Título',
				'label_block' => true,
			]);

			$this->add_control('thanks_text', [
				'label' => 'Texto do agradecimento',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quo aut magni perferendis repellat rerum assumenda, facere. Alias deserunt pariatur magnam rerum quod voluptates, quidem id labore quam. Illum, nemo, minus?</p>',
			]);
			*/

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = json_decode(json_encode($this->get_settings()));
	        $set->id = uniqid('elementor-nav-');
	        $set->mobile_id = "{$set->id}-mobile";
	        $set->desktop_id = "{$set->id}-desktop";
	        $set->navid = "{$set->id}-navid";

	        $walker = false;
			if ($set->walker=='Elementor_Nav_Social_Walker') {
				$walker = new Elementor_Nav_Social_Walker();
			}

	        ob_start();
	        echo "<ul class=\"elementor-nav-ul\">";
	        wp_nav_menu([
				'menu' => $set->nav,
				'container' => '',
				'items_wrap' => '%3$s',
				'walker' => $walker,
			]);
			if ($set->walker=='Elementor_Nav_Responsive') {
				echo '<li class="elementor-nav-mobile-close d-lg-none" style="margin-top:30px;"><a href="javascript:;" data-toggle="collapse" data-target="#<?php echo $set->navid; ?>">Fechar</a></li>';
			}
			echo '</ul>';
	        $ul_content = ob_get_clean();

	        $ul_content_nav = $ul_content;
	        if ($set->walker=='Elementor_Nav_Responsive') {
	        	$ul_content_nav = "<nav class=\"navbar navbar-expand-lg\">";
	        	$ul_content_nav .= "<button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#{$set->navid}\" aria-controls=\"{$set->navid}\">";
	        	$ul_content_nav .= "<span class=\"fa fa-fw fa-bars\"></span></button>";
	        	$ul_content_nav .= "<div class=\"collapse navbar-collapse\" id=\"{$set->navid}\">";
	        	$ul_content_nav .= "{$ul_content}</div></nav>";
	        }

	        ?>
	        <!-- elementor-nav start | <?php echo $set->walker; ?> -->
			<style>
			#<?php echo $set->id; ?> .navbar-toggler {outline:none; border:none; box-shadow:none;}
			#<?php echo $set->id; ?> * {transition: all 500ms ease;}
			#<?php echo $set->navid; ?>.collapsing {display:none;}
			#<?php echo $set->navid; ?>.show {
				display: flex;
				align-items: center;
				justify-content: center;
				position: fixed;
				top: 0px;
				left: 0px;
				width: 100%;
				height: 100%;
				z-index: 999999 !important;
				background:#ffffffdd;
			}

			#<?php echo $set->navid; ?> ul {list-style-type:none;}

			#<?php echo $set->navid; ?> > ul {
				max-width: 600px;
				margin: 0 auto;
				max-height: 95%;
				overflow: auto;
				padding: 0px;
			}


			<?php $css = str_replace('$root', "#{$set->id}", $set->css);
			$css = str_replace('$desktop', "#{$set->desktop_id}", $css);
			echo str_replace('$mobile', "#{$set->mobile_id}", $css); ?>
			</style>

			<div class="elementor-nav" id="<?php echo $set->id; ?>">
				<!-- mobile -->
				<div class="d-block d-md-none" id="<?php echo $set->mobile_id; ?>"><?php echo $ul_content_nav; ?></div>

				<!-- desktop -->
				<div class="d-none d-md-block" id="<?php echo $set->desktop_id; ?>"><?php echo $ul_content; ?></div>
			</div>

			<script>jQuery(document).ready(function($) {
				$("#<?php echo $set->id; ?>").on("click", "a", function(ev) {
					$("#<?php echo $set->navid; ?>").collapse('hide');
				});

				<?php if ($set->fixed): ?> 
				var handleFixedNavPosition = function() {
					var adminBarHeight = $("#wpadminbar").height() || 0;

					var $parent = $("#<?php echo $set->id; ?>").closest(".elementor-section-wrap").css({
						position: "fixed",
						top: adminBarHeight,
						left: 0,
						width: "100%",
						zIndex: 99,
					});

					$('.elementor-nav-fixed-spacer').remove();
					var $spacer = $('<div class="elementor-nav-fixed-spacer" style="height:'+(adminBarHeight+60)+'px;"></div>');
					$("body").prepend($spacer);
				};

				handleFixedNavPosition();
				$(window).on("resize", handleFixedNavPosition);

				<?php endif; ?>
			});</script>
			<!-- elementor-nav final | <?php echo $set->walker; ?> -->
			<?php
	    }

	    protected function content_template() {}
	}


	$manager->register_widget_type(new Elementor_Nav());
});
