<?php
// Notice is SMTP module is disabled.
$notice = static::maybe_add_smtp_notice();
?>
<div class="sui-box-body sui-message sui-message-lg">
	<img
		src="<?php echo esc_html( branda_url( 'assets/images/branda/confused@1x.png' ) ); ?>"
		srcset="<?php echo esc_html( branda_url( 'assets/images/branda/confused@2x.png' ) ); ?> 2x"
		class="sui-image"
		aria-hidden="true"
	/>
	<h2><?php esc_html_e( 'No log history yet!', 'ub' ); ?></h2>
	<?php if ( $notice ) { ?>
		<div style="text-align: left;">
			<?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } else { ?>
		<p><?php esc_html_e( 'You don’t have any logs yet. When you do, you’ll be able to view all the logs here.', 'ub' ); ?></p>
	<?php } ?>
</div>
