<?php

if ($manager) {
	class Elementor_Faq extends \Elementor\Widget_Base {

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
				'label' => 'Content',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'FAQ',
			]);

			$repeater = new \Elementor\Repeater();

			$repeater->add_control('question', [
				'label' => 'Pergunta',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => 'This is the question?',
				'label_block' => true,
			]);

			$repeater->add_control('answer', [
				'label' => 'Resposta',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => 'No. This is the answer.',
				'label_block' => true,
			]);

			$this->add_control('questions', [
				'label' => 'Perguntas',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ question }}}',
			]);

	        $this->end_controls_section();
	    }

	    protected function render() {
	        $set = json_decode(json_encode($this->get_settings()));
	        $set->id = uniqid('elementor-faq-');

	        foreach($set->questions as $i=>$quest) {
	        	$quest->show = $i==0;
	        	$set->questions[ $i ] = $quest;
	        }

	        ?><div class="elementor-faq" id="<?php echo $set->id; ?>">
	        	<div class="list-group">
	        		<div class="list-group-item" v-for="q in questions">
	        			<div class="elementor-faq-header">
	        				<a href="javascript:;" class="elementor-faq-question" @click="_toggle(q);" v-html="q.question"></a>
	        			</div>
	        			<div class="elementor-faq-answer" v-html="q.answer" :ref="q._id" :class="{'elementor-faq-answer-show':q.show}"></div>
	        		</div>
	        	</div>
			</div>

			<style>
			.elementor-faq {}
			.elementor-faq * {transition: all 1000ms ease;}
			.elementor-faq-header {margin-bottom:5px;}
			.elementor-faq-question {}
			.elementor-faq-question, .elementor-faq-question * {font-size:16px; font-weight:bold !important;}
			.elementor-faq-answer {opacity:0; visibility:hidden; height:0px; overflow:hidden;}
			.elementor-faq-answer, .elementor-faq-answer * {font-size:14px;}
			.elementor-faq-answer-show {opacity:1; visibility:visible; height:auto;}
			.elementor-faq .list-group, .elementor-faq .list-group-item {border:none;}
			</style>

			<script>
			new Vue({
				el: "#<?php echo $set->id; ?>",
				methods: {
					_toggle(quest) {
						let vm=this, $=jQuery, target=vm.$refs[quest._id];
						vm.questions.forEach(function(q) {
							q.show = q._id==quest._id;
						});
					},
				},
				data: <?php echo json_encode($set); ?>,
			});
			</script><?php
	    }

	    protected function content_template() {}
	}

	return new Elementor_Faq();
}


add_action('elementor/widgets/widgets_registered', function($manager) {
	$element = include __FILE__;
	$manager->register_widget_type($element);
});

