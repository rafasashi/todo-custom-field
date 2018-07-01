<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Todo_Custom_Field_Settings {

	/**
	 * The single instance of Todo_Custom_Field_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;

		$this->base = 'tcf_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
		add_action( 'admin_menu' , array( $this, 'add_menu_items' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add Setting SubPage
	 *
	 * add Setting SubPage to wordpress administrator
	 *
	 * @return array validate input fields
	 */
	public function add_setting_page() {

		// todo panel
		
		$logo = $this->parent->assets_url . 'images/todo-icon.png';
		
		$position = apply_filters( 'tcf_plugins_menu_item_position', '30' );
		
		add_menu_page( 'tcf_plugin_panel', 'TODO', 'nosuchcapability', 'tcf_plugin_panel', NULL, $logo, $position );	
		
		remove_submenu_page( 'tcf_plugin_panel', 'tcf_plugin_panel' );

		add_submenu_page(
			'tcf_plugin_panel',
			__( 'All tasks', 'todo-custom-field' ),
			__( 'All tasks', 'todo-custom-field' ),
			'edit_pages',
			'edit.php?post_type=tcf-todo-task'
		);
		
		add_submenu_page(
			'tcf_plugin_panel',
			__( 'Task lists', 'todo-custom-field' ),
			__( 'Task lists', 'todo-custom-field' ),
			'edit_pages',
			'edit.php?post_type=tcf-todo-list'
		);
		
		add_submenu_page( 'tcf_plugin_panel', 'Settings', 'Settings', 'manage_options', 'todo-custom-field', array( $this, 'settings_page' ) );		
		
		// code market panel
		/*
		global $admin_page_hooks;
		
		$logo = $this->parent->assets_url . 'images/recuweb-icon.png';

		if( ! isset( $admin_page_hooks['rew_plugin_panel'] ) ) {
			
			$position = apply_filters( 'rew_plugins_menu_item_position', '62.31' );
			add_menu_page( 'rew_plugin_panel', 'Code Market', 'nosuchcapability', 'rew_plugin_panel', NULL, $logo, $position );
		}

		add_submenu_page( 'rew_plugin_panel', 'Todo Custom Field', 'Todo Custom Field', 'manage_options', 'todo-custom-field', array( $this, 'settings_page' ) );
		remove_submenu_page( 'rew_plugin_panel', 'rew_plugin_panel' );
		*/
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
	
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		
		$settings_link = '<a href="admin.php?page=' . $this->parent->_token . '">' . __( 'Settings', 'todo-custom-field' ) . '</a>';
  		array_push( $links, $settings_link );
		
		$addon_link = '<a href="admin.php?page=' . $this->parent->_token . '&tab=addons">' . __( 'Addons', 'todo-custom-field' ) . '</a>';
  		array_push( $links, $addon_link );
		
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['settings'] = array(
			'title'					=> __( 'Settings', 'todo-custom-field' ),
			'description'			=> '',
			'fields'				=> array(
				array(
					'id' 			=> 'todo_post_types',
					'label'			=> __( 'Post Types' , 'todo-custom-field' ),
					'description'	=> '',
					'type'			=> 'todo_checkbox_multi',
					'options'		=> $this->parent->get_post_types(),
					'object'		=> 'post_types',
					'default'		=> '',
				),		
				array(
					'id' 			=> 'todo_taxonomies',
					'label'			=> __( 'Taxonomies' , 'todo-custom-field' ),
					'description'	=> '',
					'type'			=> 'todo_checkbox_multi',
					'options'		=> $this->parent->get_taxonomies(),
					'object'		=> 'taxonomies',
					'default'		=> '',
				),
			) 
		);
		
		$settings['addons'] = array(
			'title'					=> __( 'Addons', 'todo-custom-field' ),
			'description'			=> '',
			'class'					=> 'pull-right',
			'logo'					=> $this->parent->assets_url . '/images/recuweb-icon.png',
			'fields'				=> array(
				array(
					'id' 			=> 'addon_plugins',
					'label' 		=> '',
					'type'			=> 'addon_plugins',
					'description'	=> ''
				)				
			),
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;	
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				
				$current_section = $_POST['tab'];
			} 
			else {
				
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {
					
					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
		
		
		//get addons
	
		$this->addons = array(
			
			'todo-everywhere' 		=> array(
			
				'title' 		=> 'TCF Everywhere',
				'addon_link' 	=> 'https://code.recuweb.com/download/todo-custom-field-everywhere/',
				'addon_name' 	=> 'todo-custom-field-everywhere',
				'source_url' 	=> '',
				'logo_url' 		=> 'https://code.recuweb.com/c/u/3a09f4cf991c32bd735fa06db67889e5/2018/06/todo-custom-field-everywhere-squared-300x300.png',
				'description'	=> 'Extends Todo Custom Field to add tasks to custom post types and taxonomies like WooCommerce products or tags',
				'author' 		=> 'Code Market',
				'author_link' 	=> 'https://code.recuweb.com/about-us/',
			),
		);
	}

	public function settings_section ( $section ) {
		
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
		
		$plugin_data = get_plugin_data( $this->parent->file );
		
		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			
			$html .= '<h1>' . __( $plugin_data['Name'] , 'todo-custom-field' ) . '</h1>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . ( !empty($data['logo']) ? '<img src="'.$data['logo'].'" alt="" style="margin-top: 4px;margin-right: 7px;float: left;">' : '' ) . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}
			
			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				
				ob_start();
				
				settings_fields( $this->parent->_token . '_settings' );
				
				if( isset($_GET['tab']) && $_GET['tab'] == 'addons' ){
					
					$this->do_settings_sections( $this->parent->_token . '_settings' );
				}
				else{
					
					do_settings_sections( $this->parent->_token . '_settings' );
				}
				
				$html .= ob_get_clean();
				
				if( isset($_GET['tab']) && $_GET['tab'] == 'addons' ){
					
					//do nothing
				}
				elseif( count($this->settings) > 1 ){

					$html .= '<p class="submit">' . "\n";
						$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
						$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'user-session-synchronizer' ) ) . '" />' . "\n";
					$html .= '</p>' . "\n";
				}
				
			$html .= '</form>' . "\n";
		
		$html .= '</div>';

		echo $html;
	}

	public function do_settings_sections($page) {
		
		global $wp_settings_sections, $wp_settings_fields;

		if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
			return;

		foreach( (array) $wp_settings_sections[$page] as $section ) {
			
			echo '<h3 style="margin-bottom:25px;">' . $section['title'] . '</h3>'.PHP_EOL;
			
			call_user_func($section['callback'], $section);
			
			if ( !isset($wp_settings_fields) ||
				 !isset($wp_settings_fields[$page]) ||
				 !isset($wp_settings_fields[$page][$section['id']]) )
					continue;
					
			echo '<div class="settings-form-wrapper" style="margin-top:25px;">';

				$this->do_settings_fields($page, $section['id']);
			
			echo '</div>';
		}
	}

	public function do_settings_fields($page, $section) {
		
		global $wp_settings_fields;

		if ( !isset($wp_settings_fields) ||
			 !isset($wp_settings_fields[$page]) ||
			 !isset($wp_settings_fields[$page][$section]) )
			return;

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			
			echo '<div class="settings-form-row row">';

				if ( !empty($field['title']) ){
			
					echo '<div class="col-xs-3" style="margin-bottom:15px;">';
					
						if ( !empty($field['args']['label_for']) ){
							
							echo '<label style="font-weight:bold;" for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
						}
						else{
							
							echo '<b>' . $field['title'] . '</b>';		
						}
					
					echo '</div>';
					echo '<div class="col-xs-9" style="margin-bottom:15px;">';
						
						call_user_func($field['callback'], $field['args']);
							
					echo '</div>';
				}
				else{
					
					echo '<div class="col-xs-12" style="margin-bottom:15px;">';
						
						call_user_func($field['callback'], $field['args']);
							
					echo '</div>';					
				}
					
			echo '</div>';
		}
	}

	/**
	 * Main Todo_Custom_Field_Settings Instance
	 *
	 * Ensures only one instance of Todo_Custom_Field_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Todo_Custom_Field()
	 * @return Main Todo_Custom_Field_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
