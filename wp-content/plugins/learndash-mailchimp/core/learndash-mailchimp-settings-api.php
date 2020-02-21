<?php
/**
 * The Settings API Functions for LearnDash_MailChimp
 *
 * @since		1.0.0
 *
 * @package LearnDash_MailChimp
 * @subpackage LearnDash_MailChimp/core
 */

defined( 'ABSPATH' ) || die();

/**
 * Quick access to plugin field helpers.
 *
 * @since 1.0.4
 *
 * @return RBM_FieldHelpers
 */
function ld_mailchimp_fieldhelpers() {
	return LDMAILCHIMP()->field_helpers;
}

/**
 * Initializes a field group for automatic saving.
 *
 * @since 1.0.4
 *
 * @param $group
 */
function ld_mailchimp_init_field_group( $group ) {
	ld_mailchimp_fieldhelpers()->fields->save->initialize_fields( $group );
}

/**
 * Gets a meta field helpers field.
 *
 * @since 1.0.4
 *
 * @param string $name Field name.
 * @param string|int $post_ID Optional post ID.
 * @param mixed $default Default value if none is retrieved.
 * @param array $args
 *
 * @return mixed Field value
 */
function ld_mailchimp_get_field( $name, $post_ID = false, $default = '', $args = array() ) {
    $value = ld_mailchimp_fieldhelpers()->fields->get_meta_field( $name, $post_ID, $args );
    return $value !== false ? $value : $default;
}

/**
 * Gets a option field helpers field.
 *
 * @since 1.0.4
 *
 * @param string $name Field name.
 * @param mixed $default Default value if none is retrieved.
 * @param array $args
 *
 * @return mixed Field value
 */
function ld_mailchimp_get_option_field( $name, $default = '', $args = array() ) {
	$value = ld_mailchimp_fieldhelpers()->fields->get_option_field( $name, $args );
	return $value !== false ? $value : $default;
}

/**
 * Outputs a text field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_text_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_text( $args['name'], $args );
}

/**
 * Outputs a password field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_password_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_password( $args['name'], $args );
}

/**
 * Outputs a textarea field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_textarea_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_textarea( $args['name'], $args );
}

/**
 * Outputs a checkbox field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_checkbox_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_checkbox( $args['name'], $args );
}

/**
 * Outputs a toggle field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_toggle_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_toggle( $args['name'], $args );
}

/**
 * Outputs a radio field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_radio_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_radio( $args['name'], $args );
}

/**
 * Outputs a select field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_select_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_select( $args['name'], $args );
}

/**
 * Outputs a number field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_number_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_number( $args['name'], $args );
}

/**
 * Outputs an image field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_media_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_media( $args['name'], $args );
}

/**
 * Outputs a datepicker field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_datepicker_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_datepicker( $args['name'], $args );
}

/**
 * Outputs a timepicker field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_timepicker_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_timepicker( $args['name'], $args );
}

/**
 * Outputs a datetimepicker field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_datetimepicker_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_datetimepicker( $args['name'], $args );
}

/**
 * Outputs a colorpicker field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_colorpicker_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_colorpicker( $args['name'], $args );
}

/**
 * Outputs a list field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_list_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_list( $args['name'], $args );
}

/**
 * Outputs a hidden field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_hidden_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_hidden( $args['name'], $args );
}

/**
 * Outputs a table field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_table_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_table( $args['name'], $args );
}

/**
 * Outputs a HTML field.
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_html_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_html( $args['name'], $args );
}

/**
 * Outputs a repeater field.
 *
 * @since 1.0.4
 *
 * @param mixed $values
 */
function ld_mailchimp_repeater_callback( $args = array() ) {
	ld_mailchimp_fieldhelpers()->fields->do_field_repeater( $args['name'], $args );
}

/**
 * Outputs a hook
 *
 * @since 1.0.4
 *
 * @param mixed $values
 */
function ld_mailchimp_hook_callback( $args = array() ) {
	do_action( 'ld_mailchimp_' . $args['name'], $args );
}

/**
 * Outputs a String if a Callback Function does not exist for an Options Page Field
 *
 * @since 1.0.4
 *
 * @param array $args
 */
function ld_mailchimp_missing_callback( $args ) {
	
	printf( 
		_x( 'A callback function called "ld_mailchimp_%s" does not exist.', '%s is the Field Type', 'learndash-mailchimp' ),
		$args['type']
	);
		
}