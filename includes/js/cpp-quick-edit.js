/**
 * Script to show the correct prices on quick edit tab on admin products tab.
 */
jQuery(
	function($) {
		$("#the-list tr").each(function(){
            var id = $(this).attr('id');
            var id = id.replace('post-','');
            var x = $(this).children().children('.amount').last().clone();
            x.children('.woocommerce-Price-currencySymbol').remove(); 
            price = x.text();
            var new_id = '#woocommerce_inline_' + id + ' .regular_price';
            $( new_id ).html(price);
        });
    }
);