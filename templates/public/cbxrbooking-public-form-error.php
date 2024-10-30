<?php
	/**
	 * Provide a public view for the plugin
	 *
	 * This file is used to markup the public facing error
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    cbxrbooking
	 * @subpackage cbxrbooking/public/templates
	 */

	if ( ! defined( 'WPINC' ) ) {
		die;
	}
?>

<div class="cbxrbookingcustombootstrap cbxrbooking_wrapper cbxrbookingform_wrapper cbxrbooking_wrapper_<?php echo esc_attr($scope); ?>" data-form-id="<?php echo intval($form_id); ?>">
    <div class="alert alert-danger" role="alert">
        <p><?php echo $error_text; ?></p>
    </div>
</div>