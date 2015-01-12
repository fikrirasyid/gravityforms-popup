<?php
/*
 Plugin Name: Gravity Forms Popup
 Plugin URI: http://fikrirasy.id/portfolio/gravity-forms-popup/
 Description: Display Gravity Forms' form as a popup when user visits your site to increase your subscription
 Author: Fikri Rasyid
 Version: 0.1
 Author URI: http://fikrirasy.id
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( class_exists( 'GFForms' ) ){
	GFForms::include_addon_framework();

	/**
	 * Extending GFAddon to create Gravity Forms Addon
	 */
	class GFPopup extends GFAddon{
        protected $_version 					= "0.1";
        protected $_min_gravityforms_version 	= "1.8.20";
        protected $_slug 						= "gravityforms-popup";
        protected $_path 						= __FILE__;
        protected $_full_path 					= __FILE__;
        protected $_url 						= "http://fikrirasy.id/portfolio/gravityforms-popup";
        protected $_title 						= "Gravity Forms Popup";
        protected $_short_title 				= "Popup";

        /**
         * Displaying result on the front end
         * 
         * @access protected
         */
        protected function init_frontend(){
        	parent::init_frontend();

        	add_action( 'wp_enqueue_scripts', 							array( $this, 'frontend_scripts' ) );
        	add_action( 'wp_footer', 									array( $this, 'display_popup_form' ) );
        }

        /**
         * Adding custom action on admin dashboard
         * 
         * @access protected
         */
        protected function init_admin(){
            parent::init_admin();

            // Only do this on Gravityforms Popup page
            if( isset( $_GET['page'] ) && isset( $_GET['subview'] ) && 'gf_settings' == $_GET['page'] && 'gravityforms-popup' == $_GET['subview'] ){

                wp_enqueue_media();
                wp_enqueue_script( 'gravityforms-popup-dashboard', $this->get_base_url() . "/js/gravityforms-popup-dashboard.js", array( 'jquery' ), $this->_version, true );

            }
        }

        /**
         * Adding AJAX process
         * 
         * @access protected
         */
        protected function init_ajax(){
        	parent::init_ajax();

        	add_action( 'wp_ajax_gravityforms_popup_update_cookie',  	array( $this, 'set_cookie_value' ) );
        }

        /**
         * Providing forms data for input choices
         * 
         * @access private
         * @return array 	forms data
         */
        private function choices_gforms( $name = false, $append_default = true ){

        	$gforms = GFFormsModel::get_forms( true, 'ID' );

        	// Prepare choice
        	$forms = array();

        	// Default choice
        	if( $append_default ){
	        	$forms[0] = array(
	        		'label'		=> __( 'Select Form', 'gravityforms-popup' ),
	        		'value'		=> 0
	    		);

	    		if( $name ){
	    			$forms[0]['name'] = $name;
	    		}
        	}

        	// Prepare data
        	if( ! empty( $gforms ) ){

        		foreach ( $gforms as $gform ) {

        			$forms[$gform->id]['label'] 	= $gform->title;
        			$forms[$gform->id]['value'] 	= $gform->id;

        			// Append name data
        			if( $name ){

		    			$forms[$gform->id]['name'] 	= $name;

        			}

        		}

        	}

        	return $forms;
        }

        /**
         * Plugin settings
         * 
         * @access public
         */
        public function plugin_settings_fields(){
        	$setting_fields = array(
			    array(
			        "title" => __( '', 'gravityforms-popup' ),
			        "fields" => array(
			        	array(
			        		'label' 		 => __( 'Select Form:', 'gravityforms-popup'),
			        		'type'			 => 'select',
			        		'name'			 => 'gform',
			        		'default_values' => $this->get_setting( 'gform', 0 ),
			        		'choices'		 => $this->choices_gforms()
		        		),
                        array(
                            'label'          => __( 'Seconds To Appearance:', 'gravityforms-popup'),
                            'type'           => 'number',
                            'name'           => 'seconds_to_appearance',
                            'default_value'  => 10
                        ),
			        	array(
			        		'label' 		 => __( 'Show Popup on:', 'gravityforms-popup'),
			        		'type'			 => 'checkbox',
			        		'name'			 => 'popup_appearance',
			        		'default_value'	 => true,
			        		'choices'		 => array(
			        			array(
			        				'label'	 => __( 'Front Page', 'gravityforms-popup' ),
			        				'name'	 => 'appearance_is_front_page'
		        				),
			        			array(
			        				'label'	 => __( 'Single Posts, Pages, and other Post Types', 'gravityforms-popup' ),
			        				'name'	 => 'appearance_is_singular'
		        				),
                                array(
                                    'label'  => __( 'Category, Tags, and Custom Taxonomy Page', 'gravityforms-popup' ),
                                    'name'   => 'appearance_is_archive'
                                ),
			        			array(
			        				'label'	 => __( 'Search Results', 'gravityforms-popup' ),
			        				'name'	 => 'appearance_is_search'
		        				),
			        			array(
			        				'label'	 => __( '404 Not Found Page', 'gravityforms-popup' ),
			        				'name'	 => 'appearance_is_404'
		        				),
                                array(
                                    'label'  => __( 'When user reaches the bottom of the page', 'gravityforms-popup' ),
                                    'name'   => 'display_on_bottom_page'
                                ),
		        			)
		        		),
                        array(
                            'label'         => __( 'Initial Image:', 'gravityforms-popup' ),
                            'type'          => 'select_image',
                            'name'          => 'initial_image'
                        ),
		        		array(
		        			'label'			=> __( 'Update Cookie:', 'gravityforms-popup' ),
		        			'type'			=> 'update_gravityforms_popup_cookie_type',
                            'name'          => 'update_cookie'
	        			)
		        	)
			    ),
			);

			return apply_filters( 'gravityforms_popup_plugin_settings_fields', $setting_fields );
        }

        /**
         * Better way to get plugin setting: return default value if intended value doesn't exist
         * 
         * @param string $setting_name - Plugin setting to be returned
         * @param mixed - specified default value
         * @return mixed  - Returns the specified plugin setting or null if the setting doesn't exist
         */
        protected function get_plugin_setting( $setting_name, $default_value = false ){
            $settings = $this->get_plugin_settings();

            if( isset($settings[$setting_name]) ){
                return $settings[$setting_name];
            } else {
                return $default_value;
            }
        }        

        /**
         * Input numbers
         */
        protected function settings_number($field, $echo = true) {

            $field["type"] = "number"; //making sure type is set to text
            $attributes = $this->get_field_attributes($field);
            $default_value = rgar( $field, 'value' ) ? rgar( $field, 'value' ) : rgar( $field, 'default_value' );
            $value = $this->get_setting($field["name"], $default_value);


            $name = esc_attr($field["name"]);
            $tooltip =  isset( $choice['tooltip'] ) ? gform_tooltip( $choice['tooltip'], rgar( $choice, 'tooltip_class'), true ) : "";
            $html = "";

            $html .= '<input
                        type="number"
                        name="_gaddon_setting_' . esc_attr($field["name"]) . '"
                        value="' . esc_attr($value) . '"' .
                        implode( ' ', $attributes ) .
                    ' />';

            $feedback_callback = rgar($field, 'feedback_callback');
            if(is_callable($feedback_callback)){
                $is_valid = call_user_func_array( $feedback_callback, array( $value, $field ) );
                $icon = "";
                if($is_valid === true)
                    $icon = "icon-check fa-check gf_valid"; // check icon
                else if($is_valid === false)
                    $icon = "icon-remove fa-times gf_invalid"; // x icon

                if(!empty($icon))
                    $html .= "&nbsp;&nbsp;<i class=\"fa {$icon}\"></i>";
            }

            if( $this->field_failed_validation( $field ) )
                $html .= $this->get_error_icon( $field );

            if ($echo)
                echo $html;

            return $html;
        }

        /**
         * Custom field for selecting image
         * 
         * @access public
         * @return void
         */
        public function settings_select_image( $field ){

            $image_id_name  = 'image_id_' . $field['name'];
            $image_src_name = 'image_src_' . $field['name'];

            // Field select
            $this->settings_hidden( array(
                'name' => $image_id_name
            ) );

            $this->settings_hidden( array(
                'name' => $image_src_name
            ) );

            // Default value
            $image_id   = $this->get_setting( $image_id_name, false );
            $image_src  = $this->get_setting( $image_src_name, false );

            // Default style
            if( $image_src ){
                $style_button_select = 'style="display: none;"';
                $style_button_rest = '';                
            } else {
                $style_button_select = '';
                $style_button_rest = 'style="display: none;"';
            }

            ?>
            <div class="span preview-image" id="preview-image-<?php echo $field['name']; ?>" data-name="<?php echo $field['name']; ?>" style="display: block; margin-bottom: 5px;">
                <?php 
                    if( $image_src ){
                        echo "<img src='{$image_src}' style='width: 100%;'>";
                    }
                ?>
            </div>
            <button <?php echo $style_button_select; ?> class="button button-select-image" data-name="<?php echo $field['name']; ?>"><?php _e( 'Select Image', 'gravityforms-popup' ); ?></button>
            <button <?php echo $style_button_rest; ?> class="button button-change-image" data-name="<?php echo $field['name']; ?>"><?php _e( 'Change Image', 'gravityforms-popup' ); ?></button>
            <button <?php echo $style_button_rest; ?> class="button button-primary button-delete-image" data-name="<?php echo $field['name']; ?>"><?php _e( 'Delete Image', 'gravityforms-popup' ); ?></button>

            <?php
        }

        /**
         * Custom field for updating Gravity Forms Popup cookie
         * 
         * @access public
         * @return void
         */
        public function settings_update_gravityforms_popup_cookie_type(){
        	$link 			= "admin-ajax.php?action=gravityforms_popup_update_cookie";
        	$label 			= __( 'Update Cookie', 'gravityforms-popup' );
        	$desc 			= __( 'Clicking this will force all visitor to view the popup (again)', 'gravityforms-popup' );

        	// Display notification
        	if( isset( $_GET['update_cookie'] ) ){

        		if( 'success' == $_GET['update_cookie'] ){

        			echo '<span style="font-weight: bold; display: block; padding-bottom: 10px; color: green; font-size: .8em; ">';
        			_e( 'Cookie has been updated', 'gravityforms-popup' );
        			echo '</span>';

        		} else {

        			echo '<span style="font-weight: bold; display: block; padding-bottom: 10px; color: red; font-size: .8em;">';
        			_e( 'Cookie cannot be updated', 'gravityforms-popup' );
        			echo '</span>';

        		}
        	}

        	echo "<a href='{$link}' class='button'>{$label}</a>";
        	echo "<span style='display: block; padding-top: 10px; font-size: .8em;'>{$desc}</span>";
        }

        /**
         * Generating conditional based on saved settings
         * 
         * @access private
         * @return bool
         */
        private function is_visible(){

            // Check for long term cookie
            $long_term_cookie_name = 'gravityforms_popup_cookie_submitted_' . $this->get_plugin_setting( 'gform', 0 );
            if( isset( $_COOKIE[$long_term_cookie_name] ) && $_COOKIE[$long_term_cookie_name] == $this->get_plugin_setting( 'gform', 0 ) ){
                return false;
            }

        	// Cookie based checking first
        	if( isset( $_COOKIE['gravityforms_popup_cookie']) && $_COOKIE['gravityforms_popup_cookie'] == $this->get_cookie_value() ){
        		// User has viewed this popup before
        		return false;
        	}


        	$return = false;

        	// Get settings
	        $settings = $this->get_plugin_settings();

	        // Conditional Lists
	        $conditionals = array( 'is_front_page', 'is_singular', 'is_archive', 'is_search', 'is_404' );

	        // Display content based on conditionals
	        if( ! empty( $settings ) ){

	        	foreach ( $settings as $setting_key => $setting ) {

	        		if( 'appearance_' == substr( $setting_key, 0, 11 ) ){

	        			$conditional = str_replace('appearance_', '', $setting_key);

	        			$appearance = intval( $setting );

	        			if( $appearance > 0 && in_array( $conditional, $conditionals ) && function_exists( $conditional ) && $conditional() ){

	        				// Modify return value
	        				$return = true;

	        			}

	        		}
	        	}
	        }

	        return $return;
        }

        /**
         * Set cookie value. Generate new value to make existing cookie obsolete
         * 
         * @access private
         * @return void
         */
        public function set_cookie_value(){
        	$settings_link 	= admin_url( "admin.php?page=gf_settings&subview=gravityforms-popup" );

        	$timestamp = current_time( 'timestamp' );

        	$update = update_option( 'gravityforms_popup_cookie', 'gravityforms_popup_cookie-' . $timestamp );

        	if( $update ){
        		wp_redirect( $settings_link . "&update_cookie=success" );
        	} else {        		
        		wp_redirect( $settings_link . "&update_cookie=fail" );
        	}

        	die();
        }

        /**
         * Get cookie value
         * 
         * @access private
         * @return string
         */
        private function get_cookie_value(){
        	return get_option( 'gravityforms_popup_cookie', 'gravityforms_popup_cookie' );
        }

        /**
         * Front end scripts for display popup
         * 
         * @return void
         */
        public function frontend_scripts(){

        	// Only add scripts on visible page
        	if( $this->is_visible() ){
        		wp_enqueue_script( 'gravityforms-popup-frontend', $this->get_base_url() . "/js/gravityforms-popup-frontend.js", array( 'jquery' ), $this->_version, true );
        		wp_enqueue_style( 'gravityforms-popup-frontend', $this->get_base_url() . "/css/gravityforms-popup-frontend.css", array(), $this->_version );

        		// Adding parameters
        		$params = array(
        			'seconds_to_appearance' 	=> $this->get_plugin_setting( 'seconds_to_appearance', 10 ),
                    'appearance_point_id'       => apply_filters( 'gravityforms_popup_appearance_point_id', '#footer' ), // Display popup when the bottom of the window reaches this ID
        			'endpoint'					=> admin_url( '/admin-ajax.php?action=gravityforms_popup_endpoint' ),
        			'displayed_nonce'			=> wp_create_nonce( 'gravityforms_popup_displayed' ),
        			'cookie_name'				=> 'gravityforms_popup_cookie',
        			'cookie_value'				=> $this->get_cookie_value(),
        			'cookie_days'				=> 7,
                    'display_on_bottom_page'    => $this->get_plugin_setting( 'display_on_bottom_page', 0 ),
                    'form_id'                   => $this->get_plugin_setting( 'gform', 0 )
    			);

    			wp_localize_script( 'gravityforms-popup-frontend', 'gravityforms_popup_params', $params );
        	}
        }

        /**
         * Display popup form
         * 
         * @access public
         * @return void
         */
        public function display_popup_form(){

        	// Render content if it should be visible on current page
        	if( $this->is_visible() ){

        		// Get form ID
        		$form_id = $this->get_plugin_setting( 'gform' );

				// Get form
				if( isset( $form_id ) && $form_id && $form_id != '0' ){

                    $initial_image = $this->get_plugin_setting( 'image_src_initial_image', false );

                    if( $initial_image ){

                        echo '<div id="gravityforms-popup" class="gravityforms-popup-block undisplayed has-initial-image">';

                        echo '<img src="'. $initial_image .'" class="initial-image" />';

                    } else {

                        echo '<div id="gravityforms-popup" class="gravityforms-popup-block undisplayed">';

                    }

					echo do_shortcode( "[gravityform id='{$form_id}' title='true' description='true' ajax='true']" );

					echo '<button id="gravityforms-popup-close">'.__( "Close", "gravityforms-popup" ).'</button></div><div id="gravityforms-popup-modal" class="gravityforms-popup-block undisplayed"></div>';

				}

        	}

        }

	}

    new GFPopup();
}