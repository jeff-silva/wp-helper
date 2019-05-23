<?php

/*
Name: Wooxcel
*/


class WooxcelApi
{
	// from: https://stackoverflow.com/questions/46874020/delete-a-product-by-id-using-php-in-woocommerce
	static function product_delete($product_id)
	{
		$product = wc_get_product($product_id);

	    if (empty($product)) {
	    	throw new \Exception("Produto #{$product_id} não existe");
	    }

        if ($product->is_type('variable')) {
            foreach ($product->get_children() as $child_id) {
                $child = wc_get_product($child_id);
                $child->delete(true);
            }
        }
        elseif ($product->is_type('grouped')) {
            foreach ($product->get_children() as $child_id) {
                $child = wc_get_product($child_id);
                $child->set_parent_id(0);
                $child->save();
            }
        }

        $product->delete(true);
        $result = $product->get_id() > 0 ? false : true;

	    if (!$result) {
	        throw new \Exception('Este produto não pode ser deletado');
	    }

	    if ($parent_id = wp_get_post_parent_id($id)) {
	        wc_delete_product_transients($parent_id);
	    }

	    return $result;
	}
}


function wooxcel_search() {
	?>
	<div id="wooxcel">
		<div>
			<input type="text" v-model="productsParams.search" >
			<button type="buttpn" @click="_productSearch();">Go!</button>
		</div>

		<div>
			<button type="buttpn" @click="productsParams.page--; _productSearch();">&laquo;</button>
			<button type="buttpn" @click="productsParams.page++; _productSearch();">&raquo;</button>
		</div>

		<table>
			<colgroup>
				<col width="20px">
				<col width="*">
				<col width="150px">
				<col width="150px">
				<col width="50px">
				<col width="50px">
				<col width="50px">
				<col width="50px">
				<col width="50px">
				<col width="50px">
			</colgroup>
			<thead>
				<tr>
					<th>ID</th>
					<th>images</th>
					<th>SKU</th>
					<th>Name</th>
					<th>price</th>
					<th>regular price</th>
					<th>sale price</th>
					<th>length</th>
					<th>width</th>
					<th>height</th>
				</tr>
			</thead>
			<tbody v-if="productResp">
				<tr v-for="prod in productResp">
					<td>#{{ prod.id }}</td>
					<td><img :src="img.src" alt="" style="height:25px;" v-for="img in prod.images"></td>
					<td><input type="text" v-model="prod.sku"></td>
					<td><input type="text" v-model="prod.name"></td>
					<td><input type="text" v-model="prod.price"></td>
					<td><input type="text" v-model="prod.regular_price"></td>
					<td><input type="text" v-model="prod.sale_price"></td>
					<td><input type="text" v-model="prod.dimensions.length"></td>
					<td><input type="text" v-model="prod.dimensions.width"></td>
					<td><input type="text" v-model="prod.dimensions.height"></td>
					<td><button type="button">save</button></td>
					<td><button type="button" @click="_productDelete(prod);">delete</button></td>
				</tr>
			</tbody>
		</table>
		<pre>$data: {{ $data }}</pre>
	</div>

	<style>
	#wooxcel {}
	#wooxcel table th {text-align:left;}
	#wooxcel table td {}
	#wooxcel table td input {width:100%;}
	</style>

	<script>
	new Vue({
		el: "#wooxcel",
		
		data: {
			productsParams: {
				context: "edit",
				search: "",
				per_page: 50,
				page: 1,
			},
			productResp: false,
		},

		methods: {
			<?php foreach(get_class_methods('WooxcelApi') as $method): ?> 
			__<?php echo $method; ?>: function() {
				var vm=this, $=jQuery, ajax={};
				ajax.response = function() {};
				ajax.then = function(callback) { ajax.response = callback; };
				$.get("<?php echo site_url(); ?>", {wooxcel:"<?php echo $method; ?>", args:arguments}, function(resp) {
					ajax.response(resp.data, resp.error);
				}, "json");
				return ajax;
			},
			<?php endforeach; ?> 

			_productSearch: function() {
				var vm=this, $=jQuery;

				$.ajax({
					url: "<?php echo site_url('/wp-json/wc/v2/products'); ?>",
					method: 'GET',
					beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', "<?php echo wp_create_nonce('wp_rest'); ?>"); },
					data: vm.productsParams,
				}).done(function (resp) {
					Vue.set(vm, "productResp", resp);
				});
			},

			_productDelete: function(prod) {
				var vm=this, $=jQuery;
				if (! confirm(`Tem certeza que deseja deletar o produto ${prod.name}?`)) return;
				vm.__product_delete(prod.id).then(function(data, error) {
					vm._productSearch();
				});
			},
		},

		mounted: function() {
			var vm=this, $=jQuery;
			vm._productSearch();
		},
	});
	</script>
	<?php
}

add_action('admin_menu', function() {
	add_action('admin_enqueue_scripts', function() {
		wp_enqueue_script('my_custom_script', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js');
	});

	add_submenu_page('woocommerce', 'Woo Excel', 'Woo Excel', 'manage_options', 'wooxcel', 'wooxcel_search'); 
	add_submenu_page('edit.php?post_type=product', 'Woo Excel', 'Woo Excel', 'manage_options', 'wooxcel', 'wooxcel_search'); 
}, 99);



if (isset($_GET['wooxcel'])) {
	add_action('init', function() {
		$call = ['WooxcelApi', $_GET['wooxcel']];

		$args = isset($_GET['args'])? $_GET['args']: [];
		$args = isset($_POST['args'])? $_POST['args']: $args;
		$resp = (object) ['data'=>false, 'args'=>$args, 'error'=>[]];

		if (is_callable($call)) {
			try {
				$resp->data = call_user_func_array($call, $args);
			}
			catch(\Exception $e) {
				$resp->error[] = $e->getMessage();
			}
		}
		else {
			$resp->error[] = 'Método inexistente';
		}

		$resp->error = empty($resp->error)? false: $resp->error;
		echo json_encode($resp); die;
	});
}