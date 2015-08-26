<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'TC_Form_Fields_API' ) ) {

	class TC_Form_Fields_API {

		var $form_fields	 = array();
		var $setting_name = false;
		var $setting_key	 = false;
		var $sub_key		 = false;

		function __construct( $fields = array(), $setting_name = 'tc', $setting_key = 'gateways', $sub_key = false ) {
			$this->form_fields	 = $fields;
			$this->setting_name	 = $setting_name;
			$this->setting_key	 = $setting_key;
			$this->sub_key		 = $sub_key;
		}

		public function admin_options() {
			$this->generate_settings_html();
		}

		public function get_option( $key, $data ) {
			$setting = get_option( 'tc_settings' );
			if ( isset( $data[ 'default' ] ) ) {
				$default_value = $data[ 'default' ];
			} else {
				$default_value = '';
			}
			return isset( $setting[ $this->setting_key ][ $this->sub_key ][ $key ] ) ? $setting[ $this->setting_key ][ $this->sub_key ][ $key ] : $default_value;
		}

		public function sanitize_field_name( $field_name ) {
			return esc_attr( sanitize_key( $field_name ) );
		}

		public function generate_settings_html( $form_fields = false ) {

			if ( $this->setting_name === false ) {
				return;
			}

			if ( !$form_fields ) {
				$form_fields = $this->form_fields;
			}

			$html = '';

			foreach ( $form_fields as $k => $v ) {

				if ( !isset( $v[ 'type' ] ) || ( $v[ 'type' ] == '' ) ) {
					$v[ 'type' ] = 'text'; // Default to "text" field type.
				}

				if ( method_exists( $this, 'generate_' . $v[ 'type' ] . '_field' ) ) {
					$html .= $this->{'generate_' . $v[ 'type' ] . '_field'}( $k, $v, $this->setting_name, $this->setting_key );
				} else {
					$html .= $this->{'generate_text_field'}( $k, $v );
				}
			}

			echo $html;
		}

		public function generate_text_field( $key, $data ) {

			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'placeholder'		 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<input class="input-text regular-input <?php echo esc_attr( $data[ 'class' ] ); ?>" type="<?php echo esc_attr( $data[ 'type' ] ); ?>" name="<?php echo $field; ?>" id="<?php echo $field; ?>" style="<?php echo esc_attr( $data[ 'css' ] ); ?>" value="<?php echo esc_attr( $this->get_option( $key, $data ) ); ?>" placeholder="<?php echo esc_attr( $data[ 'placeholder' ] ); ?>" <?php disabled( $data[ 'disabled' ], true ); ?> <?php echo $this->get_custom_attribute_field( $data ); ?> />
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function generate_password_field( $key, $data ) {
			$data[ 'type' ] = 'password';
			return $this->generate_text_field( $key, $data );
		}

		public function generate_textarea_field( $key, $data ) {

			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'placeholder'		 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<textarea rows="3" cols="20" class="input-text wide-input <?php echo esc_attr( $data[ 'class' ] ); ?>" type="<?php echo esc_attr( $data[ 'type' ] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data[ 'css' ] ); ?>" placeholder="<?php echo esc_attr( $data[ 'placeholder' ] ); ?>" <?php disabled( $data[ 'disabled' ], true ); ?> <?php echo $this->get_custom_attribute_field( $data ); ?>><?php echo esc_textarea( $this->get_option( $key, $data ) ); ?></textarea>
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function generate_wp_editor_field( $key, $data ) {

			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'placeholder'		 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<?php wp_editor( html_entity_decode( stripcslashes( esc_textarea( $this->get_option( $key, $data ) ) ) ), $this->sanitize_field_name( $field ), array( 'textarea_name' => esc_attr( $field ), 'textarea_rows' => 2 ) ); ?>
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function generate_checkbox_field( $key, $data ) {

			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'label'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			if ( !$data[ 'label' ] ) {
				$data[ 'label' ] = $data[ 'title' ];
			}

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<label for="<?php echo esc_attr( $field ); ?>">
							<input <?php disabled( $data[ 'disabled' ], true ); ?> class="<?php echo esc_attr( $data[ 'class' ] ); ?>" type="checkbox" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data[ 'css' ] ); ?>" value="1" <?php checked( $this->get_option( $key, $data ), 'yes' ); ?> <?php echo $this->get_custom_attribute_field( $data ); ?> /> <?php echo wp_kses_post( $data[ 'label' ] ); ?></label><br/>
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function generate_checkboxes_field( $key, $data ) {//multiple check box fields
			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'label'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			if ( !$data[ 'label' ] ) {
				$data[ 'label' ] = $data[ 'title' ];
			}

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<?php foreach ( $data[ 'options' ] as $option => $value ) { ?>
							<label class="tc_checkboxes_label">
								<input type="checkbox" name="<?php echo $field; ?>[]" value="<?php echo esc_attr( $option ); ?>" <?php
								if ( in_array( $option, (array)$this->get_option( $key, $data ) ) ) {
									echo 'checked';
								}
								?> /> <?php echo $value; ?>
							</label>
						<?php } ?>
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function generate_select_field( $key, $data ) {

			$field		 = $this->field_name( $key );
			$defaults	 = array(
				'title'				 => '',
				'disabled'			 => false,
				'class'				 => '',
				'css'				 => '',
				'placeholder'		 => '',
				'type'				 => 'text',
				'desc_tip'			 => false,
				'description'		 => '',
				'custom_attributes'	 => array(),
				'options'			 => array()
			);

			$data = wp_parse_args( $data, $defaults );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data[ 'title' ] ); ?></span></legend>
						<select class="select <?php echo esc_attr( $data[ 'class' ] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data[ 'css' ] ); ?>" <?php disabled( $data[ 'disabled' ], true ); ?> <?php echo $this->get_custom_attribute_field( $data ); ?>>
							<?php foreach ( (array) $data[ 'options' ] as $option_key => $option_value ) : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $this->get_option( $key, $data ) ); ?>><?php echo esc_attr( $option_value ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php echo $this->get_description_field( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		public function get_custom_attribute_field( $data ) {

			$custom_attributes = array();

			if ( !empty( $data[ 'custom_attributes' ] ) && is_array( $data[ 'custom_attributes' ] ) ) {

				foreach ( $data[ 'custom_attributes' ] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			return implode( ' ', $custom_attributes );
		}

		public function field_name( $key ) {
			return esc_attr( $this->setting_name . '[' . $this->setting_key . '][' . $this->sub_key . '][' . $key . ']' );
		}

		public function get_description_field( $data ) {

			if ( !empty( $data[ 'desc_tip' ] ) ) {
				$description = $data[ 'description' ];
			} elseif ( !empty( $data[ 'description' ] ) ) {
				$description = $data[ 'description' ];
			} else {
				$description = '';
			}

			return $description ? '<p class="description">' . wp_kses_post( $description ) . '</p>' . "\n" : '';
		}

	}

}
?>