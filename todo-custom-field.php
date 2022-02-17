<?php
/*
 * Plugin Name: Todo Custom Field
 * Plugin URI: https://code.recuweb.com/download/todo-custom-field/
 * Description: Add a Todo Custom Field to Post Types and Taxonomies, create Tasks lists and monitor the list of Tasks
 * Version: 3.0.4
 * Author: Rafasashi
 * Author URI: https://code.recuweb.com/about-us/
 * Requires at least: 4.6
 * Tested up to: 5.9
 *
 * Text Domain: todo-custom-field
 * Domain Path: /lang/
 * 
 * Copyright: © 2018 Recuweb.
 * License: GNU General Public License v3.0
 * License URI: https://code.recuweb.com/product-licenses/
 */

	if(!defined('ABSPATH')) exit; // Exit if accessed directly
 
	/**
	* Minimum version required
	*
	*/
	if ( get_bloginfo('version') < 3.3 ) return;
	
	// Load plugin class files
	require_once( 'includes/class-todo-custom-field.php' );
	require_once( 'includes/class-todo-custom-field-settings.php' );
	
	// Load plugin libraries
	require_once( 'includes/lib/class-todo-custom-field-admin-api.php' );
	require_once( 'includes/lib/class-todo-custom-field-admin-notices.php' );
	require_once( 'includes/lib/class-todo-custom-field-post-type.php' );
	require_once( 'includes/lib/class-todo-custom-field-taxonomy.php' );		
	
	/**
	 * Returns the main instance of Todo_Custom_Field to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object Todo_Custom_Field
	 */
	function Todo_Custom_Field() {
				
		$instance = Todo_Custom_Field::instance( __FILE__, time() );	
		
		if ( is_null( $instance->notices ) ) {
			
			$instance->notices = Todo_Custom_Field_Admin_Notices::instance( $instance );
		}
		
		if ( is_null( $instance->settings ) ) {
			
			$instance->settings = Todo_Custom_Field_Settings::instance( $instance );
		}

		return $instance;
	}	

	Todo_Custom_Field();
