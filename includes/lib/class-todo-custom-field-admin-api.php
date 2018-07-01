<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Todo_Custom_Field_Admin_API {

	/**
	 * Constructor function
	 */
	public function __construct ($parent) {
		
		$this->parent = $parent;
		
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array   $field Field data
	 * @param  boolean $echo  Whether to echo the field HTML or return it
	 * @return void
	 */
	public function display_field ( $data = array(), $post = false, $echo = true ) {

		// Get field info
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data
		$data = '';
		if ( $post ) {

			// Get saved field data
			$option_name .= $field['id'];
			$option = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		}
		elseif( isset($field['data']) ) {
		
			$option_name .= $field['id'];
			$data = $field['data'];
		}
		else {

			// Get saved option
			$option_name .= $field['id'];
			$option = get_option( $option_name );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}
		}

		// Show default data if no option saved and default is supplied
		if ( $data === false && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( $data === false ) {
			$data = '';
		}

		$html = '';

		switch( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
			break;

			case 'password':
			case 'number':
			case 'hidden':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ' />' . "\n";
			break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;
			
			case 'addon_plugins':
				
				$html .= '<div id="the-list">';
				
					foreach( $this->parent->settings->addons as $addon ){
				
						$html .= '<div class="panel panel-default plugin-card plugin-card-akismet">';
						
							$html .= '<div class="panel-body plugin-card-top">';
								
								$html .= '<div class="name column-name">';
								
									$html .= '<h3>';
									
										$html .= '<a href="'.$addon['addon_link'].'" class="thickbox open-plugin-details-modal" style="text-decoration:none;">';
											
											if( !empty($addon['logo_url']) ){
												
												$html .= '<img class="plugin-icon" src="'.$addon['logo_url'].'" />';
											}
											
											$html .= $addon['title'];	
											
										$html .= '</a>';
										
									$html .= '</h3>';
									
								$html .= '</div>';
								
								$html .= '<div class="desc column-description">';
							
									$html .= '<p>'.$addon['description'].'</p>';
									$html .= '<p class="authors"> <cite>By <a target="_blank" href="'.$addon['author_link'].'">'.$addon['author'].'</a></cite></p>';
								
								$html .= '</div>';
								
							$html .= '</div>';
							
							$html .= '<div class="panel-footer plugin-card-bottom text-right">';
								
								$plugin_file = $addon['addon_name'] . '/' . $addon['addon_name'] . '.php';
								
								if( !file_exists( WP_PLUGIN_DIR . '/' . $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){
									
									if( !empty($addon['source_url']) ){
									
										$url = $addon['source_url'];
									}
									else{
										
										$url = $addon['addon_link'];
									}
									
									$html .= '<a href="' . $url . '" class="button install-now" aria-label="Install">Install Now</a>';
								}
								else{
									
									if( !empty($_GET['action']) && !empty($_GET['plugin']) && file_exists( WP_PLUGIN_DIR . '/' . $_GET['plugin'] ) ){
										
										// do activation deactivation

										$is_activate = is_plugin_active( $_GET['plugin'] );
										
										if( $_GET['action'] == 'activate' && !$is_activate ){
											
											activate_plugin($_GET['plugin']);
										}
										elseif( $_GET['action'] == 'deactivate' && $is_activate ){
											
											deactivate_plugins($_GET['plugin']);
										}
									}
									
									// output button
									
									if( is_plugin_active( $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){

										$url = add_query_arg( array(
											'action' => 'deactivate',
											'plugin' => urlencode( $plugin_file ),
										), home_url( $_SERVER['REQUEST_URI'] ) );
											
										$html .= '<a href="'.$url.'" class="button deactivate-now" aria-label="Deactivate">Deactivate</a>';
									}
									else{

										$url = add_query_arg( array(
											'action' => 'activate',
											'plugin' => urlencode( $plugin_file ),
										), home_url( $_SERVER['REQUEST_URI'] ) );

										$html .= '<a href="'.$url.'" class="button activate-now" aria-label="Activate">Activate</a>';
									}
								}
							
							$html .= '</div>';
						
						$html .= '</div>';
					}
				
				$html .= '</div>';
			
			break;

			case 'todo_checkbox_multi':
			
				$labels = $this->parent->get_labels();

				foreach ( $field['options'] as $k => $v ) {
					
					if( $v != 'tcf-todo-list' ){
						
						$disabled = true;
						
						if( $this->parent->is_valid_object($field['object'],$v) ){
							
							$disabled = false;
						}
						
						$checked = false;
						
						if ( !$disabled && in_array( $v, (array) $data ) ) {
							
							$checked = true;
						}
						
						$html .= '<label style="display:block;" for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi">';
							
							$html .= '<input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $v ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" ' . disabled( $disabled, true, false ) . ' /> ' . $labels[$field['object']][$v] . ' <i style="font-size:10px;">' . $v . '</i>';
						
						$html .= '</label> ';
					}
				}
				
			break;			

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'todo-custom-field' ) . '" data-uploader_button_text="' . __( 'Use image' , 'todo-custom-field' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'todo-custom-field' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'todo-custom-field' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;
		
			case 'todo_custom_field':
					
				if( !isset($data['tasks'][0]) || !empty($data['tasks'][0]) || !isset($data['ids'][0]) || !empty($data['ids'][0]) || !isset($data['checked'][0]) || !empty($data['checked'][0]) ){
					
					$arr = ['tasks' => [ 0 => '' ], 'ids' => [ 0 => 0], 'checked' => [ 0 => 'off' ]];

					if( isset($data['tasks']) ){

						$arr['tasks'] = array_merge($arr['tasks'],$data['tasks']);
					}
					
					if( isset($data['ids']) ){

						$arr['ids'] = array_merge($arr['ids'],$data['ids']);
					}
					
					if( isset($data['checked']) ){

						$arr['checked'] = array_merge($arr['checked'],$data['checked']);
					}

					$data = $arr;
				}				
				
				$html .= '<div style="display:inline-block;margin-top:5px;width:100%;">';
					
					$html .= '<input style="width:80%;float:left;" id="' . esc_attr( $field['id'] ) . '_title" type="text" name="' . esc_attr( $option_name ) . '_title" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
					
					$html .= ' <a style="width: 22px;padding: 4px 7px;font-size: 12px;float: left;margin: 1px 0 0 5px;" href="#" class="tcf-add-input-group" data-target="'.$field['id'].'_add_task" style="line-height:28px;">Add</a>';

					$html .= '<ul style="display:block;width: 100%;margin:0;" class="input-group ui-sortable">';
						
						foreach( $data['tasks'] as $e => $task ) {

							if( $e > 0 ){
								
								$class='input-group-row ui-state-default ui-sortable-handle';
							}
							else{
								
								$class='input-group-row ui-state-default ui-state-disabled';
							}
							
							$task = str_replace('\\\'','\'',$task);
							
							if( $e == 0 || !empty($task) ){
								
								$checked 	= ( isset($data['checked'][$e])  ? $data['checked'][$e] : 'off' );
								$id 		= ( isset($data['ids'][$e])  ? intval($data['ids'][$e]) : 0 );

								if( $e > 0 ){
									
									$post = get_post($id);
								}
								
								if( $e == 0 || ( isset($post->post_status) && $post->post_status == 'publish' ) ){
									
									$html .= '<li class="'.$class.' '.$field['id'].'_add_task" style="border:none;margin: 10px 0 0 0;display:'.( $e == 0 ? 'none' : 'inline-block' ).';width:97%;background: rgb(255, 255, 255);">';
								
										$html .= '<div style="width:100%;display:inline-block;' . ( 1==2 ? 'background-image: url(' . $this->parent->assets_url . 'images/dnd-icon.png?3);background-position-y:5px;background-position-x:right;background-repeat: no-repeat;background-color: transparent;' : '' ) . '">';
											
											$html .= '<input id="' . esc_attr( $field['id'] ) . '_task" type="hidden" name="' . esc_attr( $option_name ) . '[tasks][]" value="'.$task.'" />' . "\n";
											
											/*
											$html .= '<div class="tcf-status button-group" style="float: left;padding: 12px 0px;">';
												
												$html .= '<a style="padding: 5px;margin: 0;height: 5px;" class="button button-danger '.( $checked == 'off' ? 'active' : 'notActive' ).'" data-toggle="' . esc_attr( $field['id'] ) . '_checked_'.$e.'" data-title="off"></a>';
												$html .= '<a style="padding: 5px;margin: 0;height: 5px;" class="button button-warning '.( $checked == 'in' ? 'active' : 'notActive' ).'" data-toggle="' . esc_attr( $field['id'] ) . '_checked_'.$e.'" data-title="in"></a>';
												$html .= '<a style="padding: 5px;margin: 0;height: 5px;" class="button button-success '.( $checked == 'on' ? 'active' : 'notActive' ).'" data-toggle="' . esc_attr( $field['id'] ) . '_checked_'.$e.'" data-title="on"></a>';
											
											$html .= '</div>';
											
											$html .= '<input type="hidden" name="' . esc_attr( $field['id'] ) . '[checked][]" id="' . esc_attr( $field['id'] ) . '_checked_'.$e.'" value="'.$checked.'">';										
											*/
											
											$html .= '<input style="float:left;margin:11px 5px;" id="' . esc_attr( $field['id'] ) . '_checked" type="checkbox" name="' . esc_attr( $field['id'] ) . '_checked"' . ( $checked == 'on' ? ' checked="checked"' : '' ) . '/>' . "\n";

											$html .= '<div class="input-value" style="width:75%;float:left;margin: 10px 5px;' . ( $checked === 'on' ? 'text-decoration:line-through;' : '' ) . '">' . $task . '</div>';
					
											$html .= '<input id="' . esc_attr( $field['id'] ) . '_checked" type="hidden" name="' . esc_attr( $option_name ) . '[checked][]" value="'.$checked.'" />' . "\n";

											$html .= '<input id="' . esc_attr( $field['id'] ) . '_id" type="hidden" name="' . esc_attr( $option_name ) . '[ids][]" value="'.$id.'" />' . "\n";

											if( $e > 0 ){
											
												$html .= '<a style="padding: 0px 7px;margin: 10px 0 0 0;border-radius: 20px;" class="remove-input-group" href="#">x</a> ';
											}

										$html .= '</div>';

									$html .= '</li>';
								}
							}
						}
					
					$html .= '</ul>';
					
				$html .= '</div>';
				
			break;

		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<span class="description">' . $field['description'] . '</span>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
			break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field ( $data = '', $type = 'text' ) {

		switch( $type ) {
			case 'text': $data = esc_attr( $data ); break;
			case 'url': $data = esc_url( $data ); break;
			case 'email': $data = is_email( $data ); break;
		}

		return $data;
	}

	/**
	 * Add meta box to the dashboard
	 * @param string $id            Unique ID for metabox
	 * @param string $title         Display title of metabox
	 * @param array  $post_types    Post types to which this metabox applies
	 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
	 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
	 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
	 * @return void
	 */
	public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

		// Get post type(s)
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		// Generate each metabox
		foreach ( $post_types as $post_type ) {
			add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
		}
	}

	/**
	 * Display metabox content
	 * @param  object $post Post object
	 * @param  array  $args Arguments unique to this metabox
	 * @return void
	 */
	public function meta_box_content ( $post, $args ) {

		$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		echo '<div class="custom-field-panel">' . "\n";

		foreach ( $fields as $field ) {

			if ( ! isset( $field['metabox'] ) ) continue;

			if ( ! is_array( $field['metabox'] ) ) {
				$field['metabox'] = array( $field['metabox'] );
			}

			if ( in_array( $args['id'], $field['metabox'] ) ) {
				$this->display_meta_box_field( $field, $post );
			}

		}

		echo '</div>' . "\n";

	}

	/**
	 * Dispay field in metabox
	 * @param  array  $field Field data
	 * @param  object $post  Post object
	 * @return void
	 */
	public function display_meta_box_field ( $field = array(), $post ) {

		if ( ! is_array( $field ) || 0 == count( $field ) ) return;

		$field = '<div class="form-field">' . ( !empty($field['label']) ? '<label for="' . $field['id'] . '">' . $field['label'] . '</label>' : '' ) . $this->display_field( $field, $post, false ) . '</div>' . "\n";

		echo $field;
	}

	/**
	 * Save metabox fields
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	public function save_meta_boxes ( $post_id = 0 ) {

		if ( ! $post_id ) return;

		$post_type = get_post_type( $post_id );

		$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );
		
		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		foreach ( $fields as $field ) {
			
			if ( isset( $_REQUEST[ $field['id'] ] ) ) {
				
				update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
			} 
			else {
				
				update_post_meta( $post_id, $field['id'], '' );
			}
		}
	}

}
