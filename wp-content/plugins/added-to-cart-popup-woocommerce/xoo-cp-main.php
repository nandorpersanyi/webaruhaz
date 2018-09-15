<?php
/**
* Plugin Name: WooCommerce added to cart popup (Ajax) 
* Plugin URI: http://xootix.com
* Author: XootiX
* Version: 1.3
* Text Domain: added-to-cart-popup-woocommerce
* Domain Path: /languages
* Author URI: http://xootix.com
* Description: WooCommerce add to cart popup displays popup when item is added to cart without refreshing page.
**/

//Exit if accessed directly
if(!defined('ABSPATH')){
	return; 	
}

$xoo_cp_version = 1.3;

define("XOO_CP_PATH", plugin_dir_path(__FILE__));

//Load plugin text domain
function xoo_cp_load_txtdomain() {
	$domain = 'added-to-cart-popup-woocommerce';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	load_textdomain( $domain, WP_LANG_DIR . '/'.$domain.'-' . $locale . '.mo' ); //wp-content languages
	load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' ); // Plugin Languages
}
add_action('plugins_loaded','xoo_cp_load_txtdomain');


//Admin Settings
include(plugin_dir_path(__FILE__).'/inc/xoo-cp-admin.php');


//Activation on mobile devices
if(!$xoo_cp_gl_atcem_value){
	if(wp_is_mobile()){
		return;
	}
}

function xoo_cp_enqueue_scripts(){
	global $xoo_cp_version,$xoo_cp_gl_atces_value;
	wp_enqueue_style('xoo-cp-style',plugins_url('/assets/css/xoo-cp-style.css',__FILE__),null,$xoo_cp_version);
	wp_enqueue_style('font-awesome','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	wp_enqueue_script('xoo-cp-js',plugins_url('/assets/js/xoo-cp-js.min.js',__FILE__),array('jquery'),$xoo_cp_version,true);

	if($xoo_cp_gl_atces_value)
		wp_dequeue_script('wc-add-to-cart');

	//i8n javascript
	$xoo_cp_js_text = array(
		'added' 	=> __('added successfully.','added-to-cart-popup-woocommerce'),
		'updated'	=> __('updated successfully.','added-to-cart-popup-woocommerce'),
		'removed'	=> __('removed from cart.','added-to-cart-popup-woocommerce'),
		'undo'		=> __('Undo?','added-to-cart-popup-woocommerce')
	);

	wp_localize_script('xoo-cp-js','xoo_cp_localize',array(
		'adminurl'     		=> admin_url().'admin-ajax.php',
		'homeurl' 			=> get_bloginfo('url'),
		'enshop'			=> $xoo_cp_gl_atces_value,
		'xcp_text'			=> json_encode($xoo_cp_js_text)
		));
}

add_action('wp_enqueue_scripts','xoo_cp_enqueue_scripts',500);


//Get rounded total
function xoo_cp_round($number){
	$thous_sep = get_option( 'woocommerce_price_thousand_sep' );
	$dec_sep   = get_option( 'woocommerce_price_decimal_sep' );
	$decimals  = get_option( 'woocommerce_price_num_decimals' );
	return number_format( $number, $decimals, $dec_sep, $thous_sep );
}


//Get price with currency
function xoo_cp_with_currency($price){
	$price 	  = xoo_cp_round($price);
	$format   = get_option( 'woocommerce_currency_pos' );
	$csymbol  = get_woocommerce_currency_symbol();

	switch ($format) {
		case 'left':
			$currency = $csymbol.$price;
			break;

		case 'left_space':
			$currency = $csymbol.' '.$price;
			break;

		case 'right':
			$currency = $price.$csymbol;
			break;

		case 'right_space':
			$currency = $price.' '.$csymbol;
			break;

		default:
			$currency = $csymbol.$price;
			break;
	}
	return $currency;
}


//Popup HTML
function xoo_cp_popup(){
	$args = array(
		'cart_url' => WC()->cart->get_cart_url(),
		'checkout_url' => WC()->cart->get_checkout_url()
	);

	wc_get_template('xoo-cp-popup-template.php',$args,'',XOO_CP_PATH.'/templates/');

	?>

	<?php
}
add_action('wp_footer','xoo_cp_popup');

// Quantity Input
function xoo_cp_qty_input($input_value,$product){

	$max_value = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
	$min_value = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
	$step      = apply_filters( 'woocommerce_quantity_input_step', 1, $product );
	$pattern   = apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' );
		
	return '<input type="number" class="xoo-cp-qty" max="'.esc_attr( 0 < $max_value ? $max_value : '' ).'" min="'.esc_attr($min_value).'" step="'.esc_attr( $step ).'" value="'.$input_value.'" pattern="'.esc_attr( $pattern ).'" >';
}

// Ajax Add to cart 
function xoo_cp_add_to_cart_ajax(){

	global $woocommerce,$xoo_cp_gl_qtyen_value,$xoo_cp_gl_ibtne_value;

	if(!isset($_POST['action']) || $_POST['action'] != 'xoo_cp_add_to_cart' || !isset($_POST['add-to-cart'])){
		die();
	}
	
	// get woocommerce error notice
	$error = wc_get_notices( 'error' );
	$html = '';

	if( $error ){
		// print notice
		ob_start();
		foreach( $error as $value ) {
			wc_print_notice( $value, 'error' );
		}

		$js_data =  array(
			'error' => ob_get_clean()
		);
	}
	else {
		// trigger action for added to cart in ajax
		do_action( 'woocommerce_ajax_added_to_cart', intval( $_POST['add-to-cart'] ) );

		$js_data = (array) xoo_cp_cart_markup();
		$js_data['ajax_fragm'] = xoo_cp_ajax_fragments();
	}

	// clear other notice
	wc_clear_notices();

	wp_send_json($js_data);

}
add_action('wc_ajax_xoo_cp_add_to_cart','xoo_cp_add_to_cart_ajax');

//Get Cart Popup content
function xoo_cp_cart_markup(){

	//Get last cart item key
	$cart_item_key = get_option('xoo_cp_added_cart_key');

	if(!isset($cart_item_key))
		return;

	//Remove from the database
	delete_option('xoo_cp_added_cart_key');

	$cart = WC()->cart->get_cart();

	$cart_item = $cart[$cart_item_key];


	$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

	$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

	$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );



	$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );



	
	$product_name =  apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';
						

	$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

	$product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );

	// Meta data
	$attributes  = wc_get_formatted_variation($_product);
	$attributes .=  WC()->cart->get_item_data( $cart_item );

	//Get cart HTML
	ob_start();
	include(XOO_CP_PATH.'/templates/xoo-cp-cart-template.php');
	$cart_html = ob_get_clean();

	return array(
		'pname' => $product_name,
		'cart_html' => $cart_html
	);
}

//Store Cart item key
function xoo_cp_added_item_cart_key($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ){

	if(!isset($_POST['action']) || $_POST['action'] != 'xoo_cp_add_to_cart')
		return;

	update_option('xoo_cp_added_cart_key',$cart_item_key);
}
add_action('woocommerce_add_to_cart','xoo_cp_added_item_cart_key',10,6);


//Ajax change in cart
function xoo_cp_change_ajax(){
	global $woocommerce;
	$cart_item_key = sanitize_text_field($_POST['cart_key']);
	$new_qty = (int) $_POST['new_qty'];

	if($new_qty === 0){
		$removed = $woocommerce->cart->remove_cart_item($cart_item_key);
	}
	else{
		$updated = WC()->cart->set_quantity($cart_item_key,$new_qty,true);
		$cart_data = WC()->cart->get_cart();
		$cart_item = $cart_data[$cart_item_key];
		$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$ptotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );	
	}


	if($removed || $updated){
		$ajax_fragm     = xoo_cp_ajax_fragments();
		$data 			= array('ptotal' => $ptotal ,'ajax_fragm' => $ajax_fragm);
		wp_send_json($data);
	}
	else{
		if(wc_notice_count('error') > 0){
    		echo wc_print_notices();
		}
	}
	die();
}
add_action('wp_ajax_xoo_cp_change_ajax','xoo_cp_change_ajax');
add_action('wp_ajax_nopriv_xoo_cp_change_ajax','xoo_cp_change_ajax');

//Get Ajax refreshed fragments
function xoo_cp_ajax_fragments(){

  	// Get mini cart
    ob_start();

    woocommerce_mini_cart();

    $mini_cart = ob_get_clean();

    // Fragments and mini cart are returned
    $data = array(
        'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
                'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
            )
        ),
        'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
    );
    return $data;
}

//Options Styles
function xoo_cp_styles(){
	global $xoo_cp_sy_pw_value,$xoo_cp_sy_imgw_value,$xoo_cp_sy_btnbg_value,$xoo_cp_sy_btnc_value,$xoo_cp_sy_btns_value,$xoo_cp_sy_btnbr_value,$xoo_cp_sy_tbc_value,$xoo_cp_sy_tbs_value,$xoo_cp_gl_ibtne_value,$xoo_cp_gl_vcbtne_value,$xoo_cp_gl_chbtne_value,$xoo_cp_gl_qtyen_value,$xoo_cp_gl_spinen_value;

	$style = '';

	if(!$xoo_cp_gl_vcbtne_value){
		$style .= 'a.xoo-cp-btn-vc{
			display: none;
		}';
	}

	if(!$xoo_cp_gl_ibtne_value){
		$style .= 'span.xcp-chng{
			display: none;
		}';
	}

	if(!$xoo_cp_gl_chbtne_value){
		$style .= 'a.xoo-cp-btn-ch{
			display: none;
		}';
	}

	if($xoo_cp_gl_qtyen_value && $xoo_cp_gl_ibtne_value){
		$style .= 'td.xoo-cp-pqty{
		    min-width: 120px;
		}';
	}
	else{
		
	}

	if($xoo_cp_gl_spinen_value){
		$style .= '.xoo-cp-adding:after,.xoo-cp-added:after{
		    font-family: "Xoo-Cart-PopUp" !important;
		    margin-left: 5px;
		    display: inline-block;
		}

		.xoo-cp-adding:after{
		    animation: xoo-cp-spin 575ms infinite linear;
		    content: "\e97b";
		}

		.xoo-cp-added:after{
		    content: "\ea10";;
		}';
	}

	echo "<style>
		.xoo-cp-container{
			max-width: {$xoo_cp_sy_pw_value}px;
		}
		.xcp-btn{
			background-color: {$xoo_cp_sy_btnbg_value};
			color: {$xoo_cp_sy_btnc_value};
			font-size: {$xoo_cp_sy_btns_value}px;
			border-radius: {$xoo_cp_sy_btnbr_value}px;
			border: 1px solid {$xoo_cp_sy_btnbg_value};
		}
		.xcp-btn:hover{
			color: {$xoo_cp_sy_btnc_value};
		}
		td.xoo-cp-pimg{
			width: {$xoo_cp_sy_imgw_value}%;
		}
		table.xoo-cp-pdetails , table.xoo-cp-pdetails tr{
			border: 0!important;
		}
		table.xoo-cp-pdetails td{
			border-style: solid;
			border-width: {$xoo_cp_sy_tbs_value}px;
			border-color: {$xoo_cp_sy_tbc_value};
		}
		{$style}
	</style>";
}	
add_action('wp_head','xoo_cp_styles');


