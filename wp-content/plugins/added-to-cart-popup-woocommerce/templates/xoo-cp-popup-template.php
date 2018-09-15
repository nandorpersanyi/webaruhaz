<?php

//Exit if accessed directly
if(!defined('ABSPATH')){
	return; 	
}

?>

<div class="xoo-cp-opac"></div>
<div class="xoo-cp-modal">
	<div class="xoo-cp-container">
		<div class="xoo-cp-outer">
			<div class="xoo-cp-cont-opac"></div>
			<i class="xcp-icon xcp-icon-spinner2 xcp-outspin"></i>
		</div>
		<i class="xcp-icon-cross xcp-icon xoo-cp-close"></i>

		<div class="xoo-cp-atcn"></div>
		<div class="add-to-cart-popup-extra-message">Minden 40.000 Ft-os rendelés felett ingyenes a házhozszállítás!</div>
		<div class="xoo-cp-content"></div>
			
		<?php do_action('xoo_cp_before_btns'); ?>	
		<div class="xoo-cp-btns">
			<a class="xoo-cp-btn-vc xcp-btn" href="<?php echo $cart_url; ?>"><?php _e('View Cart','added-to-cart-popup-woocommerce'); ?></a>
			<a class="xoo-cp-btn-ch xcp-btn" href="<?php echo $checkout_url; ?>"><?php _e('Checkout','added-to-cart-popup-woocommerce'); ?></a>
			<a class="xoo-cp-close xcp-btn" href="<?php echo apply_filters('xoo_cp_continue_shopping_url','#'); ?>"><?php _e('Continue Shopping','added-to-cart-popup-woocommerce'); ?></a>
		</div>
		<?php do_action('xoo_cp_after_btns'); ?>
	</div>
</div>