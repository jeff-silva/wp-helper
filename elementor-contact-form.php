<?php

if (! class_exists('Elementor_Contact_Form_Input')) {
	class Elementor_Contact_Form_Input
	{
		static $types = [];

		static function addType($type, $label, $call)
		{
			self::$types[ $type ] = [
				'type' => $type,
				'label' => $label,
				'call' => $call,
			];
		}


		static function types()
		{
			$opts = [];
			foreach(self::$types as $i=>$type) {
				$opts[ $i ] = $type['label'];
			}
			return $opts;
		}

		static function attrs($attrs=[])
		{
			$attributes = [];
			foreach($attrs as $key=>$val) { $attributes[] = "{$key}=\"{$val}\""; }
			return implode(' ', $attributes);
		}

		static function options($field)
		{
			$opts = [];
			foreach(explode("\n", $field->options) as $i=>$opt) {
				$opt = explode(':', $opt);
				if (isset($opt[1])) {
					$i = $opt[0];
					$opt = $opt[1];
				}
				else {
					$opt = $opt[0];
					$i = $opt;
				}
				$opts[ $i ] = $opt;
			}
			return $opts;
		}


		static function render($field)
		{
			if (! isset(self::$types[ $field->type ])) return null;
			$attrs = ['class'=>'form-control'];
			$attrs['v-model'] = "fields.{$field->name}.value";
			ob_start();
			call_user_func(self::$types[ $field->type ]['call'], $attrs, $field);
			echo "<div v-html='fields.{$field->name}.error'></div>";
			return ob_get_clean();
		}


		static $validations = [];
		static function addValidation($type, $name, $call)
		{
			self::$validations[ $type ] = [
				'type' => $type,
				'name' => $name,
				'call' => $call,
			];
		}

		static function errorHtml($field)
		{
			$errs = [];
			if (isset($field['validations']) AND is_array($field['validations'])) {
				foreach($field['validations'] as $valid) {
					if (isset(self::$validations[$valid])) {
						$valid = self::$validations[$valid];
						if ($err = call_user_func($valid['call'], $field['value'], $field['label'])) {
							$errs[] = $err;
						}
					}
				}
			}
			return empty($errs)? null: '<div class="text-danger">'. implode('<br>', $errs) .'</div>';
		}

		static function validations()
		{
			$return = [];
			foreach(self::$validations as $valid) {
				$return[ $valid['type'] ] = $valid['name'];
			}
			return $return;
		}
	}
}



Elementor_Contact_Form_Input::addType('text', 'Texto', function($attrs, $field) {
	$attrs['type'] = 'text';
	$attrs['placeholder'] = $field->label;
	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
	echo "<input {$attrs} />";
});

Elementor_Contact_Form_Input::addType('textarea', 'Área de texto', function($attrs, $field) {
	$attrs['placeholder'] = $field->label;
	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
	echo "<textarea {$attrs}></textarea>";
});

Elementor_Contact_Form_Input::addType('select', 'Select', function($attrs, $field) {
	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
	echo "<select {$attrs} ><option value=''>Selecione</option>";
	foreach(Elementor_Contact_Form_Input::options($field) as $i=>$opt) {
		echo "<option value='{$i}'>{$opt}</option>";
	}
	echo "</select>";
});

// Elementor_Contact_Form_Input::addType('checkbox', 'Checkbox', function($attrs, $field) {
// 	$name = $attrs['v-model'];
// 	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
// 	echo '<div class="input-group">';
// 	foreach(Elementor_Contact_Form_Input::options($field) as $i=>$opt) {
// 		echo "<label class='form-control'><input type='checkbox' name='{$name}' value='{$i}'>{$opt}</label>";
// 	}
// 	echo '</div>';
// });

// Elementor_Contact_Form_Input::addType('radio', 'Radio', function($attrs, $field) {
// 	$name = $attrs['v-model'];
// 	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
// 	echo '<div class="input-group">';
// 	foreach(Elementor_Contact_Form_Input::options($field) as $i=>$opt) {
// 		echo "<label class='form-control'><input type='radio' name='{$name}' value='{$i}'>{$opt}</label>";
// 	}
// 	echo '</div>';
// });

Elementor_Contact_Form_Input::addType('file', 'Arquivo', function($attrs, $field) {
	$attrs['type'] = 'file';
	$attrs['style'] = 'display:none;';
	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
	echo "<label class='form-control'><input {$attrs} /></label>";
});

Elementor_Contact_Form_Input::addType('date', 'Data', function($attrs, $field) {
	$attrs['type'] = 'text';
	$attrs['data-flatpickr'] = '{}';
	$attrs = Elementor_Contact_Form_Input::attrs($attrs);
	echo "<input {$attrs} />";
});

Elementor_Contact_Form_Input::addValidation('required', 'Obrigatório', function($value, $label=null) {
	if (! $value) return 'Este campo é Obrigatório';
	return false;
});

Elementor_Contact_Form_Input::addValidation('email', 'E-mail', function($value, $label=null) {
	if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
		return 'E-mail inválido';
	}
	return false;
});

if ($manager) {
	class Elementor_Contact_Form extends \Elementor\Widget_Base {

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

			$this->add_control('subject', [
				'label' => 'Assunto',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]);

			$this->add_control('mail_to', [
				'label' => 'Enviar para e-mail (um em cada linha)',
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => get_option('admin_email'),
			]);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control('label', [
				'label' => 'Label',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Label',
			]);

			$repeater->add_control('name', [
				'label' => 'Input name',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
			]);

			$repeater->add_control('type', [
				'label' => 'Tipo',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => 'text',
				'options' => Elementor_Contact_Form_Input::types(),
			]);

			$repeater->add_control('options', [
				'label' => 'Opções',
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => "opt1:Option 1\nopt2:Option 2",
			]);

			$cols = range(0, 12);
			unset($cols[0]);
			$repeater->add_control('col', [
				'label' => 'Coluna',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '12',
				'options' => $cols,
			]);

			$repeater->add_control('validations', [
				'label' => 'Validações',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'default' => '',
				'options' => Elementor_Contact_Form_Input::validations(),
			]);


			$this->add_control('fields', [
				'label' => 'Campos do formulário',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ type }}} {{{ label }}}',
			]);

			$this->add_control('captcha', [
				'label' => 'Captcha',
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => [
					'-' => 'Sem captcha',
					'simple' => 'Simples',
				],
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = (object) $this->get_settings();
	        $set->id = uniqid('elementor-contact-form-');

	        $vuedata = [];
	        $vuedata['id'] = $set->id;
	        $vuedata['elementor-contact-form-submit'] = 1;
	        $vuedata['subject'] = $set->subject;
	        $vuedata['mail_to'] = $set->mail_to;
	        $vuedata['captcha'] = ['type'=>$set->captcha, 'code'=>'', 'value'=>'', 'error'=>''];
	        $vuedata['resp'] = '';

	        $vuedata['fields'] = [];
	        foreach($set->fields as $field) {
	        	if (! $field['name']) continue;
	        	$field['value'] = '';
	        	$field['error'] = '';
	        	$vuedata['fields'][ $field['name'] ] = $field;
	        }

	        ?>
	        <div id="<?php echo $set->id; ?>">
		        <form action="" @submit.prevent="_submit();">
		        	<div v-html="resp"></div>
					<div class="row">
						<?php foreach($set->fields as $field): $field = (object) $field; ?>
						<div class="col-<?php echo $field->col; ?> form-group">
							<label><?php echo $field->label; ?></label>
							<?php echo Elementor_Contact_Form_Input::render($field); ?>
						</div>
						<?php endforeach; ?>

						<?php if ($set->captcha): ?>
						<div class="col-6">
							<?php if ($set->captcha=='simple'): ?>
							<div class="captcha">
								<div class="input-group">
									<div class="input-group-prepend" style="position:relative; cursor:pointer;" title="Clique para atualizar" @click="_captcha();">
										<div class="captcha-img" style="position:relative; height:100%;">
											<div style="position:relative; height:100%;">&nbsp;</div>
										</div>
									</div>
									<input type="text" class="form-control" placeholder="Digite o Captcha" v-model="captcha.value">
								</div>
								<div v-html="captcha.error"></div>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<div class="col-6 text-right">
							<button type="submit" class="btn btn-primary">Enviar</button>
						</div>
					</div>
		        </form>
	        </div>
	        <script>
	        new Vue({
	        	el: "#<?php echo $set->id; ?>",
	        	data: <?php echo json_encode($vuedata); ?>,
	        	methods: {
	        		_submit: function() {
	        			var vm=this, $=jQuery;
						Vue.set(vm, "resp", '<i class="fa fa-fw fa-spin fa-refresh"></i> Carregando');
						$(vm.$el).css({opacity:.5});
						$.post("<?php echo site_url('?elementor-contact-form-submit=1'); ?>", vm.$data, function(resp) {
							Vue.set(vm, "resp", resp.resp);
							Vue.set(vm, "fields", resp.fields);
							Vue.set(vm, "captcha", resp.captcha);
							vm._captcha();
							$(vm.$el).css({opacity:1});
						}, "json");
	        		},

	        		_captcha: function() {
	        			var vm=this, $=jQuery;

	        			var captcha = {width:150, height:50, code:""};
	        			captcha.rand = Math.round(Math.random()*99999);
	        			captcha.charsl = 3 + Math.round(Math.random()*2);
	        			captcha.chars = "abcdefghjklmnpqrstuvwxyz23456789";
	        			for(var i=0; i<captcha.charsl; i++) {
	        				captcha.code += captcha.chars.charAt(Math.floor(Math.random() * captcha.chars.length));
	        			}

	        			captcha.bgCode = "https://dummyimage.com/{width}x{height}/000/fff.jpg&text={code}";
	        			captcha.bgimg = "https://picsum.photos/{width}/{height}?rand={rand}";

	        			for(var i in captcha) {
	        				captcha.bgCode = captcha.bgCode.replace('{'+i+'}', captcha[i]);
	        				captcha.bgimg = captcha.bgimg.replace('{'+i+'}', captcha[i]);
	        			}

	        			vm.captcha.code = captcha.code;
	        			$(vm.$el).find(".captcha-img").css({background:"url("+captcha.bgCode+") center center no-repeat", backgroundPosition:"cover"});
	        			$(vm.$el).find(".captcha-img>div").css({background:"url("+captcha.bgimg+") center center no-repeat", backgroundPosition:"cover", width:captcha.width, opacity:.6});
	        		},
	        	},
	        	mounted: function() {
	        		var vm=this, $=jQuery;
	        		vm._captcha();
	        	},
	        });
	        </script>
	        <?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Contact_Form();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});


if (isset($_GET['elementor-contact-form-submit'])) {
	add_action('init', function() {
		$data = $_REQUEST;
		$errors = 0;

		if (isset($data['fields']) AND is_array($data['fields'])) {
			foreach($data['fields'] as $i=>$field) {
				if ($data['fields'][$i]['error'] = Elementor_Contact_Form_Input::errorHtml($field)) {
					$errors++;
				}
			}
		}

		$data['captcha']['error'] = '';
		if ($data['captcha']['type']=='simple') {
			if ($data['captcha']['value'] != $data['captcha']['code']) {
				$data['captcha']['error'] = '<div class="text-danger">Captcha incorreto</div>';
				$errors++;
			}
		}

		if ($errors) {

			$data['resp'] = '<div class="alert alert-danger">Existem alguns erros no formulário</div>';
		}

		else {
			$mail_body = '';

			if (isset($data['fields']) AND is_array($data['fields'])) {
				foreach($data['fields'] as $i=>$field) {
					$value = nl2br($field['value']);
					$mail_body .= "<div style='padding:10px;'><strong>{$field['label']}</strong><br><div>{$value}</div></div>";
				}
			}

			$data['resp'] = '<div class="alert alert-success">Formulário enviado</div>';

			$data['captcha']['value'] = '';
			if (isset($data['fields']) AND is_array($data['fields'])) {
				foreach($data['fields'] as $i=>$field) {
					$data['fields'][$i]['value'] = '';
				}
			}
		}

		echo json_encode($data); die;
	});
}