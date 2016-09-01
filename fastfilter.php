<?php
/*
 * Plugin Name: FastFilter
 * Version: 1.0
 * Plugin URI: http://durrantlab.com/
 * Description: Quickly display a filter a category's posts.'
 * Author: Jacob Durrant
 * Author URI: http://durrantlab.com/
 *
 * Text Domain: fastfilter
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Jacbb Durrant
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-fastfilter.php' );
require_once( 'includes/class-fastfilter-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-fastfilter-admin-api.php' );

/**
 * Returns the main instance of FastFilter to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object FastFilter
 */
function FastFilter () {
	$instance = FastFilter::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = FastFilter_Settings::instance( $instance );
	}

	return $instance;
}

FastFilter();
