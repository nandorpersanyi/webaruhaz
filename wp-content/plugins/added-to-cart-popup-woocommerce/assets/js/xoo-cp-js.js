jQuery(document).ready(function($){

	var xoo_cp_text = JSON.parse(xoo_cp_localize.xcp_text);
	var focus_qty;

	//Add to cart ajax function
	function xoo_cp_atc(atc_btn,form_data){
		$.ajax({
				url: xoo_cp_localize.homeurl+'/?wc-ajax=xoo_cp_add_to_cart',
				data: $.param(form_data),
				type: 'POST',
			    success: function(response,status,jqXHR){
			    	
			    	if(!atc_btn.hasClass('.ajax_add_to_cart')){
			   			atc_btn.removeClass('xoo-cp-adding').addClass('xoo-cp-added');
		   			}
		   			
		   			var notice_elem = $('.xoo-cp-atcn');

		   			if(response.error){
		   				notice_elem.html(response.error);
		   			}

		   			else if(response.cart_html){
		   				notice_elem.html('<div class="xoo-cp-success"><i class="fa fa-check" aria-hidden="true"></i> '+response.pname+' '+xoo_cp_text.added+'</div>');
		   				$('.xoo-cp-content').html(response.cart_html);
		   				xoo_cp_ajax_fragm(response.ajax_fragm);
		   			}
		   			else{
		   				console.log(response);
		   				notice_elem.html('Something went wrong , contact administrator');
		   			}

			    	$('.xoo-cp-opac').show();
			    	$('.xoo-cp-modal').addClass('xoo-cp-active');
			    }
			})
	}

	//Refresh ajax fragments
	function xoo_cp_ajax_fragm(ajax_fragm){
		var fragments = ajax_fragm.fragments;
		var cart_hash = ajax_fragm.cart_hash;

		// Block fragments class
		if ( fragments ) {
			$.each( fragments, function( key ) {
				$( key ).addClass( 'updating' );
			});
		}


		// Replace fragments
		if ( fragments ) {
			$.each( fragments, function( key, value ) {
				$( key ).replaceWith( value );
			});
		}

		// Unblock
		$( '.widget_shopping_cart, .updating' ).stop( true ).css( 'opacity', '1' ).unblock();
	}

	//Add to cart on single page
	$(document).on('submit','form.cart',function(e){
		e.preventDefault();
		var form = $(this);
		var atc_btn  = form.find( 'button[type="submit"]');

		atc_btn.removeClass('xoo-cp-added').addClass('xoo-cp-adding');

		var form_data = form.serializeArray();

		// if button as name add-to-cart get it and add to form
        if( atc_btn.attr('name') && atc_btn.attr('name') == 'add-to-cart' && atc_btn.attr('value') ){
            form_data.push({ name: 'add-to-cart', value: atc_btn.attr('value') });
        }

        form_data.push({name: 'action', value: 'xoo_cp_add_to_cart'});

		xoo_cp_atc(atc_btn,form_data);//Ajax add to cart
	})

	//Add to cart on shop page
	if(xoo_cp_localize.enshop){
		$('.add_to_cart_button').on('click',function(e){
			e.preventDefault();
			var atc_btn = $(this);

			if(atc_btn.hasClass('product_type_variable')){return;}
			
			atc_btn.removeClass('xoo-cp-added').addClass('xoo-cp-adding');

			var product_id = atc_btn.data('product_id');
			var quantity = atc_btn.data('quantity');

			//If data-product_id attribute is not set.
			if(product_id === undefined || product_id === null){
				var atc_link = $(this).attr('href');
				if(atc_link.indexOf("?add-to-cart") === -1)
					return;

				var atc_link_params_str = atc_link.substring(atc_link.indexOf("?add-to-cart")+1).split('&');
				var atc_link_params = [];

				$.each(atc_link_params_str,function(key,value){
					atc_link_params.push(value.split('=')); 
				});
				
				$.each(atc_link_params,function(key,value){
					if(value[0] == 'add-to-cart'){
						product_id = value[1];
					}
					else if(value[0] == 'quantity'){
						quantity = value[1];
					}
				})

				if(!product_id)
					return;
			}


			var product_data = {};

			product_data['product_id'] = product_data['add-to-cart'] = product_id;
			product_data['variation_id'] = 0;
			product_data['quantity'] = quantity || 1;
			product_data['action'] = 'xoo_cp_add_to_cart';
			
			xoo_cp_atc(atc_btn,product_data);//Ajax add to cart
		})
	}

	//CLose Popup
	function xoo_cp_close_popup(e){
		$.each(e.target.classList,function(key,value){
			if(value == 'xoo-cp-close' || value == 'xoo-cp-modal'){
				$('.xoo-cp-opac').hide();
				$('.xoo-cp-modal').removeClass('xoo-cp-active');
				$('.xoo-cp-atcn , .xoo-cp-content').html('');
			}
		})
	}

	$(document).on('click','.xoo-cp-close',xoo_cp_close_popup);
	$('.xoo-cp-modal').on('click',xoo_cp_close_popup);

	//Ajax function to update cart (In a popup)
	function xoo_cp_update_ajax(cart_key,new_qty,pid){
		return $.ajax({
				url: xoo_cp_localize.adminurl,
				type: 'POST',
				data: {action: 'xoo_cp_change_ajax',
					   cart_key: cart_key, 
					   new_qty: new_qty,
					   pid: pid
					}
			})
	}

	//Update cart (In a popup)
	function xoo_cp_update_cart(_this,new_qty){
		$('.xoo-cp-outer').show();
		var pmain	 = _this.parents('.xoo-cp-pdetails');
		var pdata	 = pmain.data('cp');
		var cart_key = pdata.key;
		var pname 	 = pdata.pname;
		var qty_field= pmain.find('.xoo-cp-qty'); 

		xoo_cp_update_ajax(cart_key,new_qty).done(function(response,status,jqXHR){
		 		$('.xoo-cp-outer').hide();
		 		if(jqXHR.getResponseHeader('content-type').indexOf('text/html') >= 0){
		   				$('.xoo-cp-atcn').html(response);
		   				qty_field.val(focus_qty);
		   		}
		   		else{
		   			$('.xoo-cp-atcn').html('<div class="xoo-cp-success"><i class="xcp-icon xcp-icon-checkmark"></i> '+pname+' '+xoo_cp_text.updated+'</div>');
		   			$('.xcp-ptotal').html(response.ptotal);
		   			qty_field.val(new_qty);
		   			xoo_cp_ajax_fragm(response.ajax_fragm);
		   		}
		})
	}

	//Save Quantity on focus
	$(document).on('focusin','.xoo-cp-qty',function(){
		focus_qty = $(this).val();
	})


	//Qty input on change
	$(document).on('change','.xoo-cp-qty',function(e){
		var _this = $(this);
		var new_qty = parseInt($(this).val());
		var step = parseInt($(this).attr('step'));
		var min_value = parseInt($(this).attr('min'));
		var max_value = parseInt($(this).attr('max'));
		var invalid  = false;

	
		if(new_qty === 0){
			_this.parents('.xoo-cp-pdetails').find('.xoo-cp-remove .xcp-icon').trigger('click');
			return;
		}
		//Check If valid number
		else if(isNaN(new_qty)  || new_qty < 0){
			invalid = true;
		}

		//Check maximum quantity
		else if(new_qty > max_value && max_value > 0){
			alert('Maximum Quantity: '+max_value);
			invalid = true;
		}

		//Check Minimum Quantity
		else if(new_qty < min_value){
			invalid = true;
		}

		//Check Step
		else if((new_qty % step) !== 0){
			alert('Quantity can only be purchased in multiple of '+step);
			invalid = true;
		}

		//Update if everything is fine.
		else{
			xoo_cp_update_cart(_this,new_qty);
		}

		if(invalid === true){
			$(this).val(focus_qty);
		}
		
	})

	//Plus minus buttons
	$(document).on('click', '.xcp-chng' ,function(){
		var _this = $(this);
		var qty_element = _this.siblings('.xoo-cp-qty');
		qty_element.trigger('focusin');
		var input_qty = parseInt(qty_element.val());

		var step = parseInt(qty_element.attr('step'));
		var min_value = parseInt(qty_element.attr('min'));
		var max_value = parseInt(qty_element.attr('max'));

		if(_this.hasClass('xcp-plus')){
			var new_qty	  = input_qty + step;
		
			if(new_qty > max_value && max_value > 0){
				alert('Maximum Quantity: '+max_value);
				return;
			}
		}
		else if(_this.hasClass('xcp-minus')){
			
			var new_qty = input_qty - step;
			if(new_qty === 0){
				_this.parents('.xoo-cp-pdetails').find('.xoo-cp-remove .xcp-icon').trigger('click');
				return;
			}
			else if(new_qty < min_value){
				return;
			} 
			else if(input_qty < 0){
				alert('Invalid');
				return;
			}
		}
		xoo_cp_update_cart(_this,new_qty);
	})



	//Remove item from cart
	$(document).on('click','.xoo-cp-remove .xcp-icon',function(){
		$('.xoo-cp-outer').show();
		var _this 	 = $(this);
		var pdata	 = _this.parents('.xoo-cp-pdetails').data('cp');
		var cart_key = pdata.key;
		var new_qty	 = 0;
		var pname  	 = pdata.pname; 

		xoo_cp_update_ajax(cart_key,0).done(function(response){
			$('.xoo-cp-content').html('');
			$('.xoo-cp-atcn').html('<div class="xoo-cp-success"><i class="fa fa-check" aria-hidden="true"></i> '+pname+' '+xoo_cp_text.removed+'</div>');
			$('.xoo-cp-outer').hide();
			xoo_cp_ajax_fragm(response.ajax_fragm);
		})
	})

})