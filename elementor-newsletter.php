<?php

if (isset($manager)) {
	class Elementor_Newsletter extends \Elementor\Widget_Base {

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

			$posts_params = [
				'post_type' => ELEMENTOR_NEWSLETTER_POSTTYPE,
				'posts_per_page' => -1,
			];

			$posts = get_posts($posts_params);

			if (empty($posts)) {
				wp_insert_post([
					'post_title' => 'Detault Mailing',
					'post_type' => ELEMENTOR_NEWSLETTER_POSTTYPE,
					'post_status' => 'publish',
				]);
				$posts = get_posts($posts_params);
			}

			$mailing_options = [];
			foreach($posts as $item) { $mailing_options[ $item->ID ] = $item->post_title; }

			$this->add_control('mailing', [
				'label' => 'Select a mailing',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $mailing_options,
			]);

			$this->add_control('content', [
				'label' => 'HTML Form Content',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => "<div class=\"input-group\" style=\"max-width:600px; margin:0 auto;\">\n\t<input type=\"text\" placeholder=\"Name\" v-model=\"param.name\" class=\"form-control\">\n\t<input type=\"text\" placeholder=\"E-mail\" v-model=\"param.email\" class=\"form-control\">\n\t<div class=\"input-group-btn\">\n\t\t<button type=\"submit\" class=\"btn btn-primary\" :active=\"param.name && param.email\">\n\t\t\tINSCREVA-SE\n\t\t</button>\n\t</div>\n</div>",
			]);

			$this->add_control('css', [
				'label' => 'CSS',
				'type' => \Elementor\Controls_Manager::CODE,
				'default' => '.elementor-newsletter .text-danger {text-align:center;}',
			]);

			$this->add_control('error_email_invalid', [
				'label' => 'Erro: E-mail inválido',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'E-mail inválido',
			]);

			$this->add_control('error_email_exists', [
				'label' => 'Erro: E-mail inválido',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'E-mail já cadastrado',
			]);

			$this->add_control('success', [
				'label' => 'Mensagem de sucessso',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Obrigado por assinar nossa newsletter!',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = json_decode(json_encode($this->get_settings()));
	        $set->appid = uniqid('elementor-newsletter-');
	        $set->param = new stdClass;
	        $set->param->msg = '';
	        $set->param->msgClass = '';
	        $set->param->post_id = $set->mailing;
	        $set->param->error_email_invalid = $set->error_email_invalid;
	        $set->param->error_email_exists = $set->error_email_exists;
	        $set->param->success = $set->success;

	        $set->css = explode("\n", $set->css);
	        foreach($set->css as $i=>$css) {
	        	if (strpos($css, '::placeholder')) {
	        		$set->css[] = str_replace('::placeholder', '::-webkit-input-placeholder', $css);
	        		$set->css[] = str_replace('::placeholder', ':-ms-input-placeholder', $css);
	        	}
	        }
	        $set->css = implode("\n", $set->css);

	        ?>
			<section class="elementor-newsletter">
				<div id="<?php echo $set->appid; ?>">
					<form @submit.prevent="_submit();">
						<?php echo $set->content; ?>
						<div v-if="param.msg" :class="'text-'+param.msgClass" v-html="param.msg"></div>
					</form>
				</div>

				<style>
				.elementor-newsletter {}
				<?php echo $set->css; ?>
				</style>

				<script>
				new Vue({
					el: "#<?php echo $set->appid; ?>",
					data: <?php echo json_encode($set); ?>,
					methods: {
						_submit: function() {
							var vm=this, $=jQuery;
							$(vm.$el).css({opacity:.5});
							$.post("?elementor-newsletter-subscribe", {param:JSON.stringify(vm.param)}, function(resp) {
								$(vm.$el).css({opacity:1});
								vm.param = resp;
							}, "json");
						},
					},
				});
				</script>
			  </section>
	        <?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Newsletter();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});


/* Register post type */
$register_post_type = function($params=array()) {
    $params = array_merge(array(
        'singular' => 'Item',
        'plural' => 'Items',
        'slug' => 'item',
    ), $params);
    $labels = array(
        'name'                  => $params['plural'],
        'singular_name'         => $params['singular'],
        'menu_name'             => $params['plural'],
        'name_admin_bar'        => $params['singular'],
        'add_new'               => "Novo(a) {$params['singular']}",
        'add_new_item'          => "Novo(a) {$params['singular']}",
        'new_item'              => "Novo(a) {$params['singular']}",
        'edit_item'             => "Editar {$params['singular']}",
        'view_item'             => "Ver {$params['singular']}",
        'all_items'             => "Todos os {$params['plural']}",
        'search_items'          => "Pesquisar {$params['plural']}",
        'parent_item_colon'     => "Pai {$params['plural']}:",
        'not_found'             => "Nenhum {$params['plural']} encontrado.",
        'not_found_in_trash'    => "Nenhum {$params['plural']} encontrado na lixeira.",
        'featured_image'        => "Imagem de capa de {$params['singular']}",
        'set_featured_image'    => "Alterar como imagem de capa",
        'remove_featured_image' => "Remover imagem de capa",
        'use_featured_image'    => "Usar como imagem de capa",
        'archives'              => "Arquivos de {$params['singular']}",
        'insert_into_item'      => "Inserir dentro de {$params['singular']}",
        'uploaded_to_this_item' => "Enviado para {$params['singular']}",
        'filter_items_list'     => "Filtrar lista de {$params['plural']}",
        'items_list_navigation' => "Lista de navegação de {$params['plural']}",
        'items_list'            => "Lista de {$params['plural']}",
    );
 
    $params = array_merge([
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array('slug' => $params['slug']),
		// 'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 1,
		'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'elementor'],
    ], $params);
 
    register_post_type($params['slug'], $params);
};

$register_post_box = function($post_types, $title, $callback) {
	add_action('add_meta_boxes', function() use($post_types, $title, $callback) {
		foreach($post_types as $post_type) {
			$uniqid = sanitize_title($title);
			add_meta_box($uniqid, $title, $callback, $post_type);
		}
	});
};



define('ELEMENTOR_NEWSLETTER_POSTTYPE', 'elementor-newsletter');

$register_post_type([
	'slug' => ELEMENTOR_NEWSLETTER_POSTTYPE,
	'singular' => 'Mailing',
	'plural' => 'Mailings',
	'supports' => ['title'],
	'menu_position' => 30,
	'public' => false,
]);


$register_post_box ([ELEMENTOR_NEWSLETTER_POSTTYPE], 'E-mails', function($post) {
	$emails = get_post_meta($post->ID, 'maildata');
	?><br><table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($emails as $mail): ?>
			<tr>
				<td><?php echo $mail['name']; ?></td>
				<td><?php echo $mail['email']; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table><?php
});

if (isset($_GET['elementor-newsletter-subscribe'])) {
	add_action('init', function() {
		$param = isset($_POST['param'])? $_POST['param']: '{}';
		$param = stripslashes($param);
		$param = json_decode($param);
		$param = is_object($param)? $param: new stdClass;
		$param->msg = '';
		$param->msgClass = '';
		$param->name = $param->name? $param->name: '';
		$param->email = $param->email? $param->email: '';

		if (!filter_var($param->email)) {
			$param->msg = $param->error_email_invalid;
			$param->msgClass = 'danger';
		}

		else {
			foreach(get_post_meta($param->post_id, 'maildata') as $mail) {
				if ($mail['email']==$param->email) {
					$param->msg = $param->error_email_exists;
					$param->msgClass = 'danger';
					break;
				}
			}

			if (!$param->msg) {
				add_post_meta($param->post_id, 'maildata', [
					'name' => ($param->name? $param->name: $param->email),
					'email' => $param->email,
				]);
				$param->msg = $param->success;
				$param->msgClass = 'success';
				$param->email = '';
				$param->name = '';
			}
		}

		echo json_encode($param); die;
	});
}
