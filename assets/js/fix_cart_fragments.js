/* global wc_cart_fragments_params, fix_cart_fragments_params */
jQuery( function ( $ ) {

    // Check if Session Storage is available in the browser.
    let is_session_storage_available = true;

    try {
        is_session_storage_available = ( 'sessionStorage' in window && window.sessionStorage !== null );
        window.sessionStorage.setItem( 'alg-wc-cpp', 'test' );
        window.sessionStorage.removeItem( 'alg-wc-cpp' );
    } catch (err) {
        is_session_storage_available = false;
    }
    
    if ( typeof wc_cart_fragments_params != 'undefined' ) {

    let cart_hash_key = wc_cart_fragments_params.cart_hash_key;
    let fragments     = wc_cart_fragments_params.fragment_name;

        if ( is_session_storage_available ) {
            $( document ).ready( function () {

                if ( fix_cart_fragments_params.do_run === 'yes' ) {
                    let fragmentObj = JSON.parse( sessionStorage.getItem( fragments ) );

                    if (fragmentObj && fragmentObj["a.cart-contents"]) {
                        let cart_contents = fragmentObj["a.cart-contents"];
                        let currency      = '&' + fix_cart_fragments_params.currency_symbol;

                        if ( ! cart_contents.includes( currency ) ) {
                            sessionStorage.removeItem( cart_hash_key );
                            sessionStorage.removeItem( fragments );
                            $( document.body ).trigger( 'wc_fragment_refresh' );
                        }
                    }
                } else if ( fix_cart_fragments_params.do_run == 'no' ) {
                    // Page here is checkout page. So reset whatever we did for Product Page and Cart Page.
                    sessionStorage.removeItem( cart_hash_key );
                    sessionStorage.removeItem( fragments );
                    $( document.body ).trigger( 'wc_fragment_refresh' );
                }
            });
        } 
    }
});
