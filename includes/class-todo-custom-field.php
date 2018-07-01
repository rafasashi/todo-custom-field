<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Todo_Custom_Field {

	/**
	 * The single instance of Todo_Custom_Field.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	public $_dev = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $filesystem = null;
	public $notices = null;
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;
	public $views;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	public static $plugin_prefix;
	public static $plugin_url;
	public static $plugin_path;
	public static $plugin_basefile;
	
	public $items;
	public $labels;
	public $post_types;
	public $taxonomies;
	public $active_post_types;
	public $active_taxonomies;
	public $everywhere;
	 
	public function __construct ( $file = '', $version = '1.0.0' ) {
	
		$this->_version = $version;
		$this->_token 	= 'todo-custom-field';
		$this->_base 	= 'tcf_';
		
		// Load plugin environment variables
		
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		Todo_Custom_Field::$plugin_prefix 		= $this->_base;
		Todo_Custom_Field::$plugin_basefile 	= $this->file;
		Todo_Custom_Field::$plugin_url 		= plugin_dir_url($this->file); 
		Todo_Custom_Field::$plugin_path 		= trailingslashit($this->dir);

		// register plugin activation hook
		
		//register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		
		$this->admin = new Todo_Custom_Field_Admin_API($this);

		/* Localisation */
		
		$locale = apply_filters('plugin_locale', get_locale(), 'todo-custom-field');
		load_textdomain('todo_custom_field', WP_PLUGIN_DIR . "/".plugin_basename(dirname(__FILE__)).'/lang/todo_custom_field-'.$locale.'.mo');
		load_plugin_textdomain('todo_custom_field', false, dirname(plugin_basename(__FILE__)).'/lang/');
		
		$this->register_post_type( 'tcf-todo-task', __( 'Todo Tasks', 'todo-custom-field' ), __( 'Todo Task', 'todo-custom-field' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'tcf-todo-task',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'page-attributes', 'author', 'comments' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->register_post_type( 'tcf-todo-list', __( 'Todo Lists', 'todo-custom-field' ), __( 'Todo List', 'todo-custom-field' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'tcf-todo-list',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		if( in_array( basename($_SERVER['SCRIPT_FILENAME']), array('post.php','edit.php') ) ){
		
			add_action( 'add_meta_boxes', function(){
				
				foreach($this->get_active_post_types() as $post_type){
					
					$this->admin->add_meta_box (
					
						'todo_custom_field',
						__( 'Todo Tasks', 'todo-custom-field' ), 
						array($post_type),
						( $post_type == 'tcf-todo-list' ? 'advanced' : 'side' )
					);
				}
			});
			
			if( !empty($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'tcf-todo-task' ){
				
				add_filter( 'pre_get_posts', array( $this, 'query_todo_tasks') );
			}
		}
		
		add_action('admin_init', array($this, 'init_backend'));

		add_action( 'wp_delete_post', array( $this, 'delete_todo_task') );
		
		add_action( 'wp_trash_post', array( $this, 'delete_todo_task') );	

	} // End __construct ()
	
	
	/**
	 * Init Todo Custom Field extension once we know WooCommerce is active
	 */
	public function init_backend(){
		
		//add dashboard columns
		
		add_filter( 'manage_tcf-todo-task_posts_columns', array( $this, 'set_custom_edit_todo_list_columns' ) );
		
		add_action( 'manage_tcf-todo-task_posts_custom_column' , array( $this, 'custom_todo_list_column' ), 10, 2 );
		
		if( in_array( basename($_SERVER['SCRIPT_FILENAME']), array('post.php','edit.php') ) ){
			
			//add todo in post types
			
			$post_types = $this->get_active_post_types();
			
			foreach( $post_types as $post_type ){
			
				add_filter( $post_type . '_custom_fields', array( $this, 'add_todo_custom_field_custom_fields' ));
			
				add_action( 'save_post_' . $post_type, array( $this, 'save_todo_custom_field' ), 10, 3 );
			}
		}
		
		if( in_array( basename($_SERVER['SCRIPT_FILENAME']), array('term.php','edit-tags.php') ) ){
		
			//add todo in taxonomies
			
			$taxonomies = $this->get_active_taxonomies();
			
			foreach( $taxonomies as $taxonomy ){
			
				add_action( $taxonomy . '_edit_form_fields', array($this, 'add_todo_taxonomy_custom_fields'), 10, 2 );
			
				add_action( 'edited_' . $taxonomy, array($this, 'save_todo_taxonomy_custom_fields'), 10, 2 );
			}	
		}
	
		//add todo in projects
	}
	
	public function query_todo_tasks( $query ){
		
		if( $query->query['post_type'] == 'tcf-todo-task' ){
			
			$compare = 'IN';
			
			if( !empty($_REQUEST['object']) ){
				
				$objects = array($_REQUEST['object']);
			}
			else{
				
				$objects = array_merge($this->get_active_post_types(),$this->get_active_taxonomies());
			}
			
			if( !empty($_REQUEST['checked']) ){
				
				$checked = array($_REQUEST['checked']);
			}
			else{
				
				$checked = array('on','off','in');
			}
			
			$query->set( 'meta_query', array(
				'relation' => 'AND',
				array(
					  'key' 	=> 'tcf_task_object',
					  'value' 	=> $objects,
					  'compare' => $compare,
				),
				array(
					  'key' 	=> 'tcf_task_checked',
					  'value' 	=> $checked,
					  'compare' => $compare,
				)
			));		
		}
	}
	
	public function delete_todo_task( $post_id ){

		global $post_type;  

		if ( $post_type != 'tcf-todo-task' ){
			
			$data = get_post_meta($post_id,'todo_custom_field',true);
			
			if( !empty($data['ids']) && is_array($data['ids']) ){
				
				foreach( $data['ids'] as $id ){
					
					$id = intval($id);
					
					if( $id > 0 ){
						
						wp_delete_post( $id, true );
					}
				}
			}
		}
	}	
	
	public function add_todo_taxonomy_custom_fields($term){
		
	   // Check for existing taxonomy meta for the term you're editing  
		
		?>  
		  
		<tr class="form-field">  
			<th scope="row" valign="top">  
				<label for="presenter_id"><?php _e('Todo'); ?></label>  
			</th>  
			<td>  
				<?php  
				echo $this->admin->display_field( array(
				
					'type'				=> 'todo_custom_field',
					'id'				=> 'todo_custom_field',
					'name'				=> 'todo_custom_field',
					'placeholder'		=> 'new task to do',
					'data'				=> get_term_meta( $term->term_id, 'todo_custom_field', true),
					'description'		=> '',
					
				), false );				 
				?>
			</td>  
		</tr>  
		  
		<?php 		
	}
	
	public function set_custom_edit_todo_list_columns($columns) {
		
		$new_columns = array();
		
		$new_columns['cb']		= '<input type="checkbox" />';
		$new_columns['todo']	= __( 'Todo', 'todo-custom-field' );
		$new_columns['in'] 		= __( 'In', 'todo-custom-field' );
		$new_columns['type'] 	= __( 'Type', 'todo-custom-field' );
		$new_columns['progress']= __( 'Progress', 'todo-custom-field' );
		$new_columns['comments']= '<span class="vers comment-grey-bubble" title="'.__( 'Comments', 'todo-custom-field' ).'"><span class="screen-reader-text">'.__( 'Comments', 'todo-custom-field' ).'</span></span>';
		$new_columns['author'] 	= __( 'Author', 'todo-custom-field' );
		$new_columns['date'] 	= __( 'Date', 'todo-custom-field' );

		return $new_columns;
	}
	
	public function get_labels(){
		
		if(	is_null( $this->labels ) ){
			
			//post types
			
			$post_types = $this->get_post_types();
			
			foreach( $post_types as $post_type ){
				
				$obj = get_post_type_object( $post_type );

				$this->labels['post_types'][$post_type] = $obj->labels->singular_name;
			}
			
			//taxonomies
			
			$taxonomies = $this->get_taxonomies();
			
			foreach( $taxonomies as $taxonomy ){
				
				$obj = get_taxonomy( $taxonomy );
			
				$this->labels['taxonomies'][$taxonomy] = $obj->labels->singular_name;
			}
		}
		
		return $this->labels;		
	}
	
	public function get_post_types(){

		if(	is_null( $this->post_types ) ){
			
			$post_types = get_post_types('', '');
			
			$this->post_types = array();
			
			foreach( $post_types as $post_type){

				if( $post_type->name != 'tcf-todo-task' && $post_type->show_ui === true ){
					
					$this->post_types[] = $post_type->name;
				}
			}
		}
		
		return $this->post_types;
	}
	
	public function get_taxonomies(){
		
		if(	is_null( $this->taxonomies ) ){
		
			$taxonomies = get_taxonomies('', '');

			$this->taxonomies = array();
			
			foreach( $taxonomies as $taxonomy){
				
				if( $taxonomy->show_ui === true ){
				
					$this->taxonomies[] = $taxonomy->name;
				}
			}
		}
		
		return $this->taxonomies;
	}
	
	public function get_active_taxonomies(){
		
		if( is_null($this->active_taxonomies) ){
			
			$valid = get_option('tcf_todo_taxonomies');
			
			foreach( $valid as  $e => $taxonomy ){
				
				if( !$this->is_valid_taxonomy($taxonomy) ){
					
					unset( $valid[$e] );
				}
			}
			
			$this->active_taxonomies = $valid;
		}
		
		return $this->active_taxonomies;
	}
	
	public function get_active_post_types(){
		
		if( is_null($this->active_post_types) ){
			
			$valid = get_option('tcf_todo_post_types');

			foreach( $valid as  $e => $post_type ){
				
				if( !$this->is_valid_post_type($post_type) ){
					
					unset( $valid[$e] );
				}
			}
			
			$valid[] = 'tcf-todo-list';
			
			$this->active_post_types = $valid;
		}
		
		return $this->active_post_types;
	}
	
	public function is_valid_taxonomy($taxonomy){
		
		if( !empty( $this->everywhere->valid_taxonomies ) ){
			
			$valid = $this->everywhere->valid_taxonomies;
		}
		else{
			
			$valid = array('category','post_tag','link_category');
		}
		
		if( in_array($taxonomy,$valid) ){
			
			return true;
		}
		
		return false;
	}
	
	public function is_valid_post_type($post_type){
		
		if( !empty( $this->everywhere->valid_post_types ) ){
			
			$valid = $this->everywhere->valid_post_types;
		}
		else{
			
			$valid = array('post','page','attachment','revision');
		}

		if( $post_type == 'tcf-todo-list' || in_array($post_type,$valid) ){
			
			return true;
		}
		
		return false;
	}
	
	public function is_valid_object($object,$value){
		
		if( $object == 'taxonomy' || $object == 'taxonomies' ){
			
			return $this->is_valid_taxonomy($value);
		}
		elseif( $object == 'post_type' || $object == 'post_types' ){
			
			return $this->is_valid_post_type($value);
		}
		
		return false;
	}
	
	public function custom_todo_list_column( $column, $post_id ) {
		
		if( !isset($this->items[$post_id])  ){
			
			$this->items[$post_id] = get_post_meta($post_id);
			
			if( isset($this->items[$post_id]['tcf_task_post_id']) ){

				if( !isset($this->items[$post_id]['post']) ){
					
					$this->items[$post_id]['post'] = get_post($this->items[$post_id]['tcf_task_post_id'][0]);
				}					
			}
			elseif( isset($this->items[$post_id]['tcf_task_tax_id']) ){

				if( !isset($this->items[$post_id]['term']) ){
					
					$this->items[$post_id]['term'] = get_term($this->items[$post_id]['tcf_task_tax_id'][0]);
				}					
			}
			
			if( isset($this->items[$post_id]['tcf_task_checked']) ){
				
				if( $this->items[$post_id]['tcf_task_checked'][0] == 'on'  ){
					
					$this->items[$post_id]['progress'] = 'done';
				}
				else{
					
					$this->items[$post_id]['progress'] = 'pending';
				}
			}
		}
		
		switch ( $column ) {
			
			case 'todo' :
				
				global $post;
				
				echo '<a' .( isset($this->items[$post_id]['progress']) && $this->items[$post_id]['progress'] == 'done' ? ' style="text-decoration:line-through;"' : '' ) .' href="' . get_admin_url('','post.php?post=' . $post_id . '&action=edit') . '">' . $post->post_title . '</a>';
				
			break;
			
			case 'in' :
				
				if( isset($this->items[$post_id]['post']) ){
				
					echo '<a href="' . get_admin_url('','post.php?post=' . $this->items[$post_id]['post']->ID . '&action=edit') . '">' . $this->items[$post_id]['post']->post_title . '</a>';
				}
				elseif( isset($this->items[$post_id]['term']) ){

					echo '<a href="' . get_admin_url('','term.php?tag_ID=' . $this->items[$post_id]['term']->term_id . '') . '">' . $this->items[$post_id]['term']->name . '</a>';
				}
				//echo'<pre>';var_dump($this->items);exit;

				break;

			case 'type' :
				
				global $wp;
				
				$url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
				
				if( isset($this->items[$post_id]['post']) ){
					
					$post_type = $this->items[$post_id]['post']->post_type;
					
					$labels = $this->get_labels();
					
					$url = add_query_arg( 'object', $post_type, $url);
					
					echo '<a href="' . $url .'">' . $labels['post_types'][$post_type] . ' <i style="font-size:10px;">'.$post_type.'</i>'. '</a>';
				}
				elseif( isset($this->items[$post_id]['term']) ){
					
					$taxonomy = $this->items[$post_id]['term']->taxonomy;
					
					$labels = $this->get_labels();
					
					$url = add_query_arg( 'object', $taxonomy, $url);
					
					echo '<a href="' . $url .'">' . $labels['taxonomies'][$taxonomy] . ' <i style="font-size:10px;">'.$taxonomy.'</i>'. '</a>';
				}
				
			break;
			
			case 'progress' :
			
				if( isset($this->items[$post_id]['progress']) ){

					global $wp;
				
					$url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
									
					$url = add_query_arg( 'checked', $this->items[$post_id]['tcf_task_checked'][0], $url);
					
					echo '<a href="' . $url .'">' . $this->items[$post_id]['progress']. '</a>';
				}
				
			break;
		}
	}	
	
	function add_todo_custom_field_custom_fields($fields){
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "todo_custom_field"),
				'type'				=> 'todo_custom_field',
				'id'				=> 'todo_custom_field',
				'label'				=> '',
				'description'		=> '',
				'placeholder'		=> 'new task to do',
		);
		
		return $fields;	
	}	
	
	public function save_todo_taxonomy_custom_fields($term_id){
		
		// remove tasks
		
		$old_tasks = get_term_meta( $term_id, 'todo_custom_field', true);

		foreach( $old_tasks['ids']  as $e => $id ){
			
			if( !isset($_REQUEST['todo_custom_field']['ids']) || empty($_REQUEST['todo_custom_field']['ids']) || !in_array($id, $_REQUEST['todo_custom_field']['ids']) ){
				
				wp_delete_post( $id, false );
			}
		}
		
		// add new tasks
			
		if( isset($_REQUEST['todo_custom_field']['tasks']) ){
		
			$new_tasks = $_REQUEST['todo_custom_field']['tasks'];
			
			$term = get_term( $term_id );
			
			foreach( $new_tasks as $e => $task ){
				
				if( $e > 0 && isset($_REQUEST['todo_custom_field']['ids'][$e]) ){
					
					$id = intval($_REQUEST['todo_custom_field']['ids'][$e]);

					if( $id > 0 && get_post($id) ){
						
						//update task
						
						$task_id = $_REQUEST['todo_custom_field']['ids'][$e];
						
						wp_update_post(array(
							'ID' 			=> $task_id,
							'post_author' 	=> get_current_user_id(),
							'post_title' 	=> $task,
							'post_status' 	=> 'publish',
							'post_type' 	=> 'tcf-todo-task',
							'post_parent' 	=> 0,
							'menu_order' 	=> 0,
						));
					}
					else{
						
						//insert task
						
						$task_id = wp_insert_post(array(
						
							'post_author' 	=> get_current_user_id(),
							'post_title' 	=> $task,
							'post_status' 	=> 'publish',
							'post_type' 	=> 'tcf-todo-task',
							'post_parent' 	=> 0,
							'menu_order' 	=> 0,
						));

						$_REQUEST['todo_custom_field']['ids'][$e] = $task_id;
					}
					
					// update post meta
					
					update_term_meta($term_id,'todo_custom_field',$_REQUEST['todo_custom_field']);
					
					// update task post id
					
					update_post_meta($task_id,'tcf_task_tax_id',$term_id);
					
					// update task object
					
					update_post_meta($task_id,'tcf_task_object',$term->taxonomy);								
					
					// update task checked
					
					$checked = ( !empty($_REQUEST['todo_custom_field']['checked'][$e]) ? $_REQUEST['todo_custom_field']['checked'][$e] : 'off' );
					
					update_post_meta($task_id,'tcf_task_checked',$checked);
				}
			}
		}		
	}	
	
	function save_todo_custom_field( $post_id, $post, $update ) {
		
		// remove tasks
		
		$old_tasks = get_post_meta( $post_id, 'todo_custom_field', true);

		foreach( $old_tasks['ids']  as $e => $id ){
			
			if( !isset($_REQUEST['todo_custom_field']['ids']) || empty($_REQUEST['todo_custom_field']['ids']) || !in_array($id, $_REQUEST['todo_custom_field']['ids']) ){
				
				wp_delete_post( $id, false );
			}
		}
		
		// add new tasks
			
		if( isset($_REQUEST['todo_custom_field']['tasks']) ){
		
			$new_tasks = $_REQUEST['todo_custom_field']['tasks'];
			
			$post_type = get_post_type($post_id);
			
			foreach( $new_tasks as $e => $task ){
				
				if( $e > 0 && isset($_REQUEST['todo_custom_field']['ids'][$e]) ){
					
					$id = intval($_REQUEST['todo_custom_field']['ids'][$e]);

					if( $id > 0 && get_post($id) ){
						
						//update task
						
						$task_id = $_REQUEST['todo_custom_field']['ids'][$e];
						
						wp_update_post(array(
							'ID' 			=> $task_id,
							'post_author' 	=> get_current_user_id(),
							'post_title' 	=> $task,
							'post_status' 	=> 'publish',
							'post_type' 	=> 'tcf-todo-task',
							'post_parent' 	=> 0,
							'menu_order' 	=> 0,
						));
					}
					else{
						
						//insert task
						
						$task_id = wp_insert_post(array(
						
							'post_author' 	=> get_current_user_id(),
							'post_title' 	=> $task,
							'post_status' 	=> 'publish',
							'post_type' 	=> 'tcf-todo-task',
							'post_parent' 	=> 0,
							'menu_order' 	=> 0,
						));

						$_REQUEST['todo_custom_field']['ids'][$e] = $task_id;
					}
					
					// update post meta
					
					update_post_meta($post_id,'todo_custom_field',$_REQUEST['todo_custom_field']);
					
					// update task post id
					
					update_post_meta($task_id,'tcf_task_post_id',$post_id);
			
					// update task object
					
					update_post_meta($task_id,'tcf_task_object',$post_type);			
			
					// update task checked
					
					$checked = ( !empty($_REQUEST['todo_custom_field']['checked'][$e]) ? $_REQUEST['todo_custom_field']['checked'][$e] : 'off' );
					
					update_post_meta($task_id,'tcf_task_checked',$checked);
				}
			}
		}
	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new Todo_Custom_Field_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new Todo_Custom_Field_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}
	
	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		//wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend-1.0.1.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-frontend' );		

	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		//wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-frontend' );	
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );	

	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		
		load_plugin_textdomain( 'todo-custom-field', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = 'todo-custom-field';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()
	
	/**
	 * Main Todo_Custom_Field Instance
	 *
	 * Ensures only one instance of Todo_Custom_Field is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Todo_Custom_Field()
	 * @return Main Todo_Custom_Field instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $file, $version );
		}
		
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}
