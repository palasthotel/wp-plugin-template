<?php
/**
 * Example template. A theme overrides it with
 * <theme>/plugin-parts/my-plugin-example.php
 *
 * @var array $args
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="my-plugin-example">
	<?php echo esc_html( $args["text"] ?? "" ); ?>
</div>
