<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version' => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce = require 'inc/woocommerce/class-storefront-woocommerce.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		require 'inc/nux/class-storefront-nux-starter-content.php';
	}
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

add_action( 'wp_footer', 'cart_update_qty_script' );
function cart_update_qty_script() {
    if (is_cart()) :
        ?>
        <script type="text/javascript">
            (function($){
                $(function(){
                    $('div.woocommerce').on( 'change', '.qty', function(){
                        $("[name='update_cart']").trigger('click');
                    });
                });
            })(jQuery);
        </script>
        <?php
    endif;
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15 );

add_action( 'woocommerce_after_shop_loop', 'tutsplus_product_subcategories', 50 );
function tutsplus_product_subcategories( $args = array() ) {
	$parentid = get_queried_object_id();   
	$args = array(
	    'parent' => $parentid
	);
	$terms = get_terms( 'product_cat', $args );
	if ( $terms ) {
	    echo '<ul class="product-cats">';
	        foreach ( $terms as $term ) { 
	            echo '<li class="category">';                 
	                echo '<a href="' .  esc_url( get_term_link( $term ) ) . '" class="' . $term->slug . '">';
	                woocommerce_subcategory_thumbnail( $term );
	                echo '<h2>';
	                        echo $term->name;
	                echo '</h2>';
                    echo '</a>';                                                 
	            echo '</li>';
	    }
	    echo '</ul>';
	    if(is_front_page()){
	    	echo '<div id="hp-description">
<h1>Kertlap Kertészeti Webáruház</h1>
<p>Vásárolj online kertészeti termékeket egyszerűen és biztonságosan a <a href="http://kertlap.hu/" target="_blank" rel="noopener">Kertlap</a> korszerű webáruházában.
<a href="https://webaruhaz.kertlap.hu/termekkategoria/kerti-szerszamok-kerti-kellekek/" target="_blank" rel="noopener">Kerti kellékek és kerti szerszámok</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/palantazas-csiraztatas-termesztes/" target="_blank" rel="noopener">csíráztatás &amp; palántázás &amp; termesztés</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/ontozes/" target="_blank" rel="noopener">praktikus öntözők &amp; automata szobanövény öntözők</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/led-noveny-nevelo-izzok/" target="_blank" rel="noopener">LED növénynevelő izzók és lámpák</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/profi-metszoeszkozok/" target="_blank" rel="noopener">profi metszőeszközök</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/egzotikus-paprika-paradicsom-magok/" target="_blank" rel="noopener">egzotikus paprika &amp; paradicsom magok</a>, <a href="https://webaruhaz.kertlap.hu/termekkategoria/kaspo/" target="_blank" rel="noopener">divatos kaspók</a>.<br>A kertészeti webáruházunkban egyszerűen, regisztráció nélkül vásárolhatsz hasznos és ötletes termékeket!</p>
<p>A Kertlap kertészkedés iránt elkötelezett csapata válogatta össze ezt különleges termék palettát kifejezetten hobbi kertészek számára.<br>Praktikus és rafinált megoldások zöldségeid, gyümölcseid és a dísznövényeid számára!</p>
<p>Az OTP Bank segítségével olyan online fizetési lehetőséget kínálunk, amely ugyanolyan biztonságos, mintha saját bankod honlapján intéznéd a pénzügyeidet.</p>
</div>';
		}
	}
}

// Remove footer default credentials
function storefront_credit() {
   
}

// Set no. of products of shop page to 30
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );
function new_loop_shop_per_page( $cols ) {
  // $cols contains the current number of products per page based on the value stored on Options -> Reading
  // Return the number of products you wanna show per page.
  $cols = 18;
  return $cols;
}

/**
* @snippet Add "Confirm Email Address" Field @ WooCommerce Checkout
* @how-to Watch tutorial @ https://businessbloomer.com/?p=19055
* @sourcecode https://businessbloomer.com/?p=72602
* @author Rodolfo Melogli
* @testedwith WooCommerce 3.0.7
*/
 
// ---------------------------------
// 1) Make original email field half width
// 2) Add new confirm email field
 
add_filter( 'woocommerce_checkout_fields' , 'bbloomer_add_email_verification_field_checkout' );
function bbloomer_add_email_verification_field_checkout( $fields ) {
	$fields['billing']['billing_email']['class'] = array('form-row-first');
	$fields['billing']['billing_em_ver'] = array(
	    'label'     => __('E-mail cím megerősítés', 'bbloomer'),
	    'required'  => true,
	    'class'     => array('form-row-last'),
	    'clear'     => true
	);
	return $fields;
}
 
// ---------------------------------
// 3) Generate error message if field values are different
 
add_action('woocommerce_checkout_process', 'bbloomer_matching_email_addresses');
function bbloomer_matching_email_addresses() {
	$email1 = $_POST['billing_email'];
	$email2 = $_POST['billing_em_ver'];
	if ( $email2 !== $email1 ) {
		wc_add_notice( __( 'A megadott email címek nem egyeznek', 'bbloomer' ), 'error' );
	}
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
	unset($fields['billing']['billing_state']);
	unset($fields['billing']['billing_address_2']);
	unset($fields['shipping']['shipping_state']);
	unset($fields['shipping']['shipping_address_2']);
	unset($fields['shipping']['shipping_phone']);
	unset($fields['shipping']['shipping_email']);
    return $fields;
}

wp_enqueue_style( 'style-mod', get_template_directory_uri() . '/style-mod.css',array('storefront-style'),'1.1','all');

add_action( 'init', 'my_theme_remove_storefront_standard_functionality' );
function my_theme_remove_storefront_standard_functionality() {
	//remove customizer inline styles from parent theme as I don't need it.
	set_theme_mod('storefront_styles', '');
	set_theme_mod('storefront_woocommerce_styles', '');
}

remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
remove_action( 'wp_head', 'index_rel_link' ); // index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version

// Code to clear default shipping option.
/*add_filter( 'woocommerce_shipping_chosen_method', '__return_false', 99);*/

// Code to clear default payment option.
/*add_filter( 'pre_option_woocommerce_default_gateway' . '__return_false', 99 );*/

add_filter( 'woocommerce_available_payment_gateways', 'bbloomer_gateway_disable_shipping_30' );
function bbloomer_gateway_disable_shipping_30( $available_gateways ) {
	global $woocommerce;
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_shipping = $chosen_methods[0];
	if ( isset( $available_gateways['bacs'] ) && 0 === strpos( $chosen_shipping, 'flat_rate' ) ) {
		unset( $available_gateways['bacs'] );
	}
	return $available_gateways;
}

add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );
function disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}

//add back to store button after cart
add_action('woocommerce_after_cart_totals', 'themeprefix_back_to_store');
add_action('woocommerce_before_checkout_form', 'themeprefix_back_to_cart');
function themeprefix_back_to_store() { ?>
<a class="button wc-backward woocommerce-Button--previous" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">Vissza a termékekhez</a>

<?php
}
function themeprefix_back_to_cart() { ?>
<div id="backtocart-outer"><a class="button wc-backward woocommerce-Button--previous" href="https://webaruhaz.kertlap.hu/kosaram/" title="Ha meggondoltad magad, még bármikor visszaléphetsz a Kosarad tartalmának szerkesztéséhez!">Vissza a kosaramhoz</a></div>

<?php
}

add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args' );
add_filter( 'woocommerce_upsell_display_args', 'jk_related_products_args' );
function jk_related_products_args( $args ) {
	$args['posts_per_page'] = 6; // 4 related products
	$args['columns'] = 2; // arranged in 2 columns
	return $args;
}

add_filter( 'woocommerce_product_query_meta_query', 'shop_only_instock_products', 10, 2 );
function shop_only_instock_products( $meta_query, $query ) {
    // Only on shop archive pages
    if( is_admin() || is_search() || ! is_shop() ) return $meta_query;
    $meta_query[] = array(
        'key'     => '_stock_status',
        'value'   => 'outofstock',
        'compare' => '!='
    );
    return $meta_query;
}

add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
function custom_override_default_locale_fields( $fields ) {
	$fields['last_name']['priority'] = 1;
    $fields['first_name']['priority'] = 2;
    $fields['company']['priority'] = 3;
    $fields['country']['priority'] = 4;
    $fields['city']['priority'] = 5;
    $fields['postcode']['priority'] = 6;
    $fields['address_1']['priority'] = 7;
    $fields['address_2']['priority'] = 8;

	$fields['last_name']['placeholder'] = 'Vezetéknév';
    $fields['first_name']['placeholder'] = 'Keresztnév';
    $fields['company']['placeholder'] = 'Cégnév (nem kötelező)';
    $fields['city']['placeholder'] = 'Város';
    $fields['postcode']['placeholder'] = 'Irányítószám';
    $fields['address_1']['placeholder'] = 'Utca, házszám';

    $fields['last_name']['autofocus'] = true;
    $fields['first_name']['autofocus'] = false;

    return $fields;
}

add_filter( 'woocommerce_checkout_fields' , 'override_billing_checkout_fields', 20, 1 );
function override_billing_checkout_fields( $fields ) {
    $fields['billing']['billing_phone']['placeholder'] = 'Telefonszám';
    $fields['billing']['billing_email']['placeholder'] = 'E-mail cím';
    return $fields;
}

add_filter( 'woocommerce_localisation_address_formats', 'woocommerce_custom_address_format', 20 );
function woocommerce_custom_address_format( $formats ) {
    $formats[ 'HU' ]  = "{last_name} {first_name}\n{company}\n{city}\n{postcode}\n{address_1}\n{country}";
    return $formats;
}
