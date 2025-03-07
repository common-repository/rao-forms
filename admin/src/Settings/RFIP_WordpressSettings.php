<?php
namespace RFIP\Admin\Settings;
abstract class RFIP_WordpressSettings {

    /**
     * ID of the Settings
     * @var string
     */
    public $settings_id = "";

    /**
	 * Tabs for the settings page
	 * @var array
	 */
	public $tabs = array( 
		'general' => 'General' );

	/**
	 * Settings from database
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Array of fields for the general tab
	 * array(
	 * 	'tab_slug' => array(
	 * 		'field_name' => array(),
	 * 		),
	 * 	)
	 * @var array
	 */
	protected $fields = array();

	/** 
	 * Data gotten from POST
	 * @var array
	 */
	protected $posted_data = array();

     /**
	 * Get the settings from the database
	 * @return void 
	 */
	public function init_settings() {

		$this->settings = (array) get_option( $this->settings_id );

		foreach ( $this->fields as $tab_key => $tab ) {
			
			foreach ( $tab as $name => $field ) {
				
				if( isset( $this->settings[ $name ] ) ) {
					$this->fields[ $tab_key ][ $name ]['default'] = $this->settings[ $name ];
				}	
			
			}

		}

	}

	/**
	 * Save settings from POST
	 * @return [type] [description]
	 */
	public function save_settings(){
		
	 	$this->posted_data = $_POST;

	 	if( empty( $this->settings ) ) {

	 		$this->init_settings();

	 	}

	 	foreach ($this->fields as $tab => $tab_data ) {

	 		foreach ($tab_data as $name => $field) {
	 			if($field['type'] === "heading")
                continue;

	 			$this->settings[ $name ] = $this->{ 'validate_' . $field['type'] }( $name );
	 	
	 		}

	 	}
	 	update_option( $this->settings_id, $this->settings );	

	}

	/**
	 * Gets and option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param  string $key
	 * @param  mixed  $empty_value
	 * @return mixed  The value specified for the option or a default value for the option.
	 */
	public function get_option( $key, $empty_value = null ) {

		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		// Get option default if unset.
		if ( ! isset( $this->settings[ $key ] ) ) {

			$form_fields = $this->fields;

			foreach ( $this->tabs as $tab_key => $tab_title ) {

				if( isset( $form_fields[ $tab_key ][ $key ] ) ) {

					$this->settings[ $key ] = isset( $form_fields[ $tab_key ][ $key ]['default'] ) ? $form_fields[ $tab_key ][ $key ]['default'] : '';
				
				}

			}
			
		}

		if ( ! is_null( $empty_value ) && empty( $this->settings[ $key ] ) && '' === $this->settings[ $key ] ) {
			$this->settings[ $key ] = $empty_value;
		}

		return $this->settings[ $key ];
	}

    /**
	 * Validate text field
	 * @param  string $key name of the field
	 * @return string     
	 */
	public function validate_text( $key ){

		$text  = $this->get_option( $key );

		if ( isset( $this->posted_data[ $key ] ) ) {
			$text = wp_kses_post( trim( stripslashes( $this->posted_data[ $key ] ) ) );
		}

		return $text;
	}

	/**
	 * Validate textarea field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_textarea( $key ){

		$text  = $this->get_option( $key );
		 
		if ( isset( $this->posted_data[ $key ] ) ) {

			$text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
		}

		return $text;
	}

	/**
	 * Validate WPEditor field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_wpeditor( $key ){
		$text  = $this->get_option( $key );
		 
		if ( isset( $this->posted_data[ $key ] ) ) {

			$text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
		}

		return $text;
	}

	/**
	 * Validate select field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_select( $key ) {

		$value = $this->get_option( $key );
		if ( isset( $this->posted_data[ $key ] ) ) {
			if(is_array($this->posted_data[ $key ]))
			$value = $this->posted_data[ $key ];
			else
			$value = stripslashes( $this->posted_data[ $key ] );
		}

		return $value;
	}

	/**
	 * Validate radio
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_radio( $key ) {

		$value = $this->get_option( $key );

		if ( isset( $this->posted_data[ $key ] ) ) {
			$value = stripslashes( $this->posted_data[ $key ] );
		}

		return $value;
	}

	/**
	 * Validate checkbox field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_checkbox( $key ) {

		$status = '';

		if ( isset( $this->posted_data[ $key ] ) && ( 1 == $this->posted_data[ $key ] ) ) {
			$status = '1';
		}

		return $status;
	}

    /**
	 * Adding fields 
	 * @param array $array options for the field to add
	 * @param string $tab tab for which the field is
	 */
	public function add_field( $array, $tab = 'general' ) {
		$allowed_field_types = array(
            'heading',
			'text',
			'textarea',
			'wpeditor',
			'select',
			'radio',
			'checkbox' );

		// If a type is set that is now allowed, don't add the field
		if( isset( $array['type'] ) &&$array['type'] != '' && ! in_array( $array['type'], $allowed_field_types ) ){
			return;
		}

		$defaults = array(
			'name' => '',
			'title' => '',
			'default' => '',
			'placeholder' => '',
			'type' => 'text',
			'options' => array(),
			'desc' => '',
			);

		$array = array_merge( $defaults, $array );

		if( $array['name'] == '' ) {
			return;
		}

		foreach ( $this->fields as $tabs ) {
			if( isset( $tabs[ $array['name'] ] ) ) {
				trigger_error( 'There is alreay a field with name ' . esc_attr($array['name']) );
				return;
			}
		}

		// If there are options set, then use the first option as a default value
		if( ! empty( $array['options'] ) && $array['default'] == '' ) {
			$array_keys = array_keys( $array['options'] );
			$array['default'] = $array_keys[0];
		}

		if( ! isset( $this->fields[ $tab ] ) ) {
			$this->fields[ $tab ] = array();
		}

		$this->fields[ $tab ][ $array['name'] ] = $array;

	}
	
	/**
	 * Adding tab
	 * @param array $array options
	 */
	public function add_tab( $array ) {

		$defaults = array(
			'slug' => '',
			'title' => '' );

		$array = array_merge( $defaults, $array );

		if( $array['slug'] == '' || $array['title'] == '' ){
			return;
		}

		$this->tabs[ $array['slug'] ] = $array['title'];
	}

     /**
	 * Rendering fields 
	 * @param  string $tab slug of tab
	 * @return void  
	 */
	public function render_fields( $tab ) {

		if( ! isset( $this->fields[ $tab ] ) ) {

			echo wp_kses_post('<p>' . __( 'There are no settings on these page.', 'textdomain' ) . '</p>');
			return;
		}

		foreach ( $this->fields[ $tab ] as $name => $field ) {
			
			$this->{ 'render_' . $field['type'] }( $field );

		}
	}

    /**
	 * Render heading field
	 * @param  string $field options
	 * @return void     
	 */
	public function render_heading( $field ){

		extract( $field );
		?>

		<tr>
			<th colspan="2">
				<?php echo esc_attr($title); ?>
                <?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr($desc) . '</p>');
				}?>
			</th>
		</tr>

		<?php
	}

    /**
	 * Render text field
	 * @param  string $field options
	 * @return void     
	 */
	public function render_text( $field ){

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr($default); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" />	
				<?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr($desc) . '</p>');
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render textarea field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_textarea( $field ){

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" ><?php echo esc_attr($default); ?></textarea>	
				<?php if( $desc != '' ) {
					echo '<p class="description">' . esc_attr($desc) . '</p>';
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render WPEditor field
	 * @param  string $field  options
	 * @return void      
	 */
	public function render_wpeditor( $field ){
		
		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<?php wp_editor( $default, $name, array('wpautop' => false) ); ?>
				<?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr($desc) . '</p>');
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render select field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_select( $field ) {

		extract( $field );
		$attr_text = "";
		if($attr === "multiple") {
			$attr_text = 'multiple="multiple"';
		} 
		
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<select name="<?php echo esc_attr($name); ?>[]" <?php echo esc_attr($attr_text);?> id="<?php echo esc_attr($name); ?>" >
					<?php 
						foreach ($options as $value => $text) {
							if(in_array($value, $default))
							$selected = 'selected="selected"';
							else
							$selected = "";
							echo '<option ' . esc_attr($selected) . ' value="' . esc_attr($value) . '">' . esc_attr($text) . '</option>';
						}
					?>
				</select>
				<?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr($desc) . '</p>');
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render radio
	 * @param  string $field options
	 * @return void      
	 */
	public function render_radio( $field ) {

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<?php 
					foreach ($options as $value => $text) {
						echo wp_kses_post('<input name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" type="'.  esc_attr($type) . '" ' . checked( $default, $value, false ) . ' value="' . esc_attr($value) . '">' . esc_attr($text) . '</option><br/>');
					}
				?>
				<?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr($desc) . '</p>');
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render checkbox field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_checkbox( $field ) {

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<input <?php checked( $default, '1', true ); ?> type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="1" placeholder="<?php echo $placeholder; ?>" />
				<?php echo esc_attr($desc); ?>
			</td>
		</tr>

		<?php
	}
}