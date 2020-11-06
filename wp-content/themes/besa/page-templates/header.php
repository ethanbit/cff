<?php 

$header 	= apply_filters( 'besa_tbay_get_header_layout', 'header_default' );

?>

<header id="tbay-header" class="tbay_header-template site-header">

	<?php if ( $header != 'header_default' ) : ?>	

		<?php besa_tbay_display_header_builder(); ?> 

	<?php else : ?>
	
	<?php get_template_part( 'page-templates/header-default' ); ?>

	<?php endif; ?>
	<div id="nav-cover"></div>
</header>