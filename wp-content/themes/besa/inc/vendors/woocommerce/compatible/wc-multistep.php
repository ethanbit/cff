<?php

if( besa_woocommerce_is_multistep_checkout() ) return;

/*Page check out*/
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20 );