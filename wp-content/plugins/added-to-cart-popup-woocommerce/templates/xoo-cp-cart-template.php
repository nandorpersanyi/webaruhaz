<?php 

//Exit if accessed directly
if(!defined('ABSPATH')){
	return; 	
}

global $xoo_cp_gl_qtyen_value; 

?>
<table class="xoo-cp-pdetails clearfix" data-cp="<?php echo htmlentities(json_encode(array('key' => $cart_item_key, 'pname' => $product_name))); ?>">
	<tr>
		<td class="xoo-cp-remove"><i class="xcp-icon-cancel-circle xcp-icon"></i></td>
		<td class="xoo-cp-pimg"><a href="<?php echo  $product_permalink; ?>"><?php echo $thumbnail; ?></a></td>
		<td class="xoo-cp-ptitle"><a href="<?php echo  $product_permalink; ?>"><?php echo $product_name; ?></a>

		<?php if($attributes): ?>
			<div class="xoo-cp-variations"><?php echo $attributes; ?></div>
		<?php endif; ?>

		<td class="xoo-cp-pprice"><?php echo  $product_price; ?></td>


		<td class="xoo-cp-pqty">
			<?php if ( $_product->is_sold_individually() || !$xoo_cp_gl_qtyen_value ): ?>
				<span><?php echo $cart_item['quantity']; ?></span>				
			<?php else: ?>
				<div class="xoo-cp-qtybox">
				<span class="xcp-minus xcp-chng">-</span>
				<?php echo xoo_cp_qty_input($cart_item['quantity'],$_product); ?>
				<span class="xcp-plus xcp-chng">+</span></div>
			<?php endif; ?>
		</td>
	</tr>
</table>
<div class="xoo-cp-ptotal"><span class="xcp-totxt"><?php _e('Total','added-to-cart-popup-woocommerce');?> : </span><span class="xcp-ptotal"><?php echo $product_subtotal; ?></span></div>

