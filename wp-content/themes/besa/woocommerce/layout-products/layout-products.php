<?php 

	if( explode('-', $product_style)[0] !== 'vertical' ) {
		$product_style = 'inner-'. $product_style;
	}

	$flash_sales 		= isset($flash_sales) ? $flash_sales : false;
	$end_date 			= isset($end_date) ? $end_date : '';

	$countdown_title 	= isset($countdown_title) ? $countdown_title : '';
	$countdown 			= isset($countdown) ? $countdown : false;

	$classes = array('products-grid', 'product');
	if( besa_woocommerce_quantity_mode_active() ) {
		$classes[] = 'product-quantity-mode';
	}  
?> 
<div <?php echo trim($attr_row); ?>>

    <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>

        <div class="item">
			<?php 
				$post_object = get_post( $loop->get_id() );
        	?> 
            <div <?php wc_product_class( $classes, $post_object ); ?>>
				<?php wc_get_template( 'item-product/'. $product_style .'.php', array('flash_sales' => $flash_sales, 'end_date' => $end_date, 'countdown_title' => $countdown_title, 'countdown' => $countdown, 'product_style' => $product_style ) ); ?>
			</div>
        </div>

    <?php endwhile; ?> 
</div>

<?php wp_reset_postdata(); ?>