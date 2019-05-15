<?php

if ($manager) {
	class Elementor_Grid_Panel extends \Elementor\Widget_Base {

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

			$repeater = new \Elementor\Repeater();

			$repeater->add_control('title', [
				'label' => 'Título',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Título',
			]);

			$repeater->add_control('image', [
				'label' => 'Image',
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => array('id'=>'', 'url'=>''),
			]);

			$repeater->add_control('image_url', [
				'label' => 'Or URL',
				'type' => \Elementor\Controls_Manager::URL,
				'default' => ['url'=>''],
			]);

			$repeater->add_control('content', [
				'label' => 'Conteúdo',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>',
			]);

			$repeater->add_control('link', [
				'label' => 'Image',
				'type' => \Elementor\Controls_Manager::URL,
				'default' => array('url'=>'', 'is_external'=>'', 'nofollow'=>''),
			]);

			$repeater->add_control('width', [
				'label' => 'Width',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '50',
				'options' => array('25'=>'25%', '50'=>'50%', '75'=>'75%', '100'=>'100%'),
			]);

			$repeater->add_control('height', [
				'label' => 'Height',
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '50',
				'options' => array('25'=>'25%', '50'=>'50%', '75'=>'75%', '100'=>'100%'),
			]);

			$repeater->add_control('padding', [
				'label' => 'Padding',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '5px',
			]);

			$this->add_control('items', [
				'label' => 'Painel de items',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ title }}}',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = (object) $this->get_settings();
	        ?>
			<div class="wph-gridpanel" style="position:relative; width:100%; height:600px;">
				<?php foreach($set->items as $item): $item = (object) $item;
				$image_url = $item->image['url']? $item->image['url']: $item->image_url['url'];
				?>
				<a href="<?php echo $item->link['url']; ?>" class="wph-gridpanel-each" style="display:block; position:relative; float:left; width:<?php echo $item->width; ?>%; height:<?php echo $item->height; ?>%; padding:<?php echo $item->padding; ?>">
					<div style="position:relative; width:100%; height:100%; background:url(<?php echo $image_url; ?>) center center no-repeat; background-size:cover;">
						<div style="position:relative; height:100%; display:flex; align-items:center; justify-content:center;">
							<div class="wph-gridpanel-border-tl"></div>
							<div class="wph-gridpanel-border-br"></div>
							<div style="max-width:70%; text-align:center;">
								<?php echo $item->content; ?>
							</div>
						</div>
					</div>
				</a>
				<?php endforeach; ?>
				<div style="clear:both;"></div>
			</div>

			<style>
			.wph-gridpanel {}
			.wph-gridpanel * {transition: all 300ms ease;}
			.wph-gridpanel-each {}
			.wph-gridpanel-border-tl {position:absolute; top:-10px; left:-10px; border:solid 2px #fff; width:40%; height:40%; border-right:none; border-bottom:none;}
			.wph-gridpanel-border-br {position:absolute; bottom:-10px; right:-10px; border:solid 2px #fff; width:40%; height:40%; border-left:none; border-top:none;}
			.wph-gridpanel-each:hover {opacity:.8; z-index:2;}
			.wph-gridpanel-each:hover .wph-gridpanel-border-tl {top:10px; left:10px;}
			.wph-gridpanel-each:hover .wph-gridpanel-border-br {bottom:10px; right:10px;}
			</style>
	        <?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Grid_Panel();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});

