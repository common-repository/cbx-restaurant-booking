<?php
	/**
	 * Provide a booking widget view for the plugin
	 *
	 * This file is used to markup booking widget form
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    cbxrbooking
	 * @subpackage cbxrbooking/widgets/views
	 */

	if ( ! defined( 'WPINC' ) ) {
		die;
	}
?>

	<!-- Custom  Title Field -->
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'cbxrbooking' ); ?></label>

		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php esc_html_e( 'Select Form', 'cbxrbooking' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>"
				name="<?php echo $this->get_field_name( 'id' ); ?>">

			<?php
				echo '<option value="0" >' . esc_html__( 'Select Form', 'cbxrbooking' ) . '</option>';

				global $post;
				$args = array(
					'post_type'      => 'cbxrbooking',
					'orderby'        => 'ID',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
					'meta_query'     => array(
						array(
							'key'     => '_cbxrbookingformmeta_status',
							'value'   => 1,
							'compare' => '=',
						),
					),
				);

				$all_posts = get_posts( $args );
				foreach ( $all_posts as $post ):
					CBXRBookingHelper::setup_postdata( $post );
					$form_id    = get_the_ID();
					$form_title = get_the_title();
					echo '<option value="' . $form_id . '" ' . selected( $form_id, $id, false ) . '>' . sprintf( __( '%s (ID: %d)', 'cbxrbooking' ), $form_title, $form_id ) . '</option>';
				endforeach;
				CBXRBookingHelper::wp_reset_postdata();

			?>
		</select>
	</p>
<?php if ( intval( ( $id ) > 0 ) ): ?>
	<p>
		<a target="_blank"
		   href="<?php echo admin_url( 'post.php?post=' . $id . '&action=edit' ); ?>"><?php esc_html_e( 'Edit Form Setting', 'cbxrbooking' ) ?></a>
		<a class="alignright" target="_blank"
		   href="<?php echo admin_url( 'edit.php?post_type=cbxrbooking' ); ?>"><?php esc_html_e( 'View All Forms', 'cbxrbooking' ) ?></a>
	</p>
<?php else: ?>
	<p>
		<a target="_blank"
		   href="<?php echo admin_url( 'post-new.php?post_type=cbxrbooking' ); ?>"><?php esc_html_e( 'Create New Form', 'cbxrbooking' ) ?></a>
	</p>
<?php endif; ?>

	<input type="hidden" id="<?php echo $this->get_field_id( 'submit' ); ?>" name="<?php echo $this->get_field_name( 'submit' ); ?>" value="1" />
<?php
	do_action( 'cbxformsinglewidget_form_admin', $instance, $this );