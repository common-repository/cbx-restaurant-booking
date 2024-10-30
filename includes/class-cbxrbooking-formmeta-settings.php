<?php
	/**
	 * Class CBXRbookingFormmetasettings
	 */
	if ( ! class_exists( 'CBXRbookingFormmetasettings' ) ):

		class CBXRbookingFormmetasettings {

			/**
			 * meta settings sections array
			 *
			 * @var array
			 */
			private $meta_settings_sections = array();

			/**
			 * meta settings fields array
			 *
			 * @var array
			 */
			private $meta_settings_fields = array();

			/**
			 * Singleton instance
			 *
			 * @var object
			 */
			private static $_instance;
			public $metakey;

			//public function __construct($metakey = 'cbxrbooking') {
			public function __construct( $metakey = 'cbxrbookingmetabox' ) {
				$this->metakey = $metakey;
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}

			/**
			 * Enqueue scripts and styles
			 */
			public function admin_enqueue_scripts() {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_media();
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'jquery' );
			}

			/**
			 * Set settings sections
			 *
			 * @param array $sections setting sections array
			 */
			public function set_meta_settings_sections( $sections ) {
				$this->meta_settings_sections = $sections;

				return $this;
			}

			/**
			 * Add a single section
			 *
			 * @param array $section
			 */
			public function add_meta_settings_section( $section ) {
				$this->meta_settings_sections = $section;

				return $this;
			}

			/**
			 * Set settings fields
			 *
			 * @param array $fields settings fields array
			 */
			public function set_meta_settings_fields( $fields ) {
				$this->meta_settings_fields = $fields;

				return $this;
			}

			/**
			 * Add settings fields
			 *
			 * @param type $section
			 * @param type $field
			 *
			 * @return \cbxrbookingmetasettings
			 */
			public function add_meta_settings_field( $section, $field ) {
				$defaults = array(
					'name'  => '',
					'label' => '',
					'desc'  => '',
					'type'  => 'text'
				);

				$arg                                    = wp_parse_args( $field, $defaults );
				$this->meta_settings_fields[ $section ] = $arg;

				return $this;
			}

			/**
			 * Call from admin page to render settings meta box
			 */
			public function cbxrbookingform_show_metabox( $form_sections, $form_fields, $post ) {

				$this->cbxrbookingform_show_nav( $form_sections, $post );
				$this->cbxrbookingform_show_items( $form_sections, $form_fields, $post );

				$this->script();
			}

			/**
			 * Show nav links for meta settings box
			 *
			 * @param type $form_sections
			 */
			public function cbxrbookingform_show_nav( $form_sections, $post ) {
				$html = '<h2 class="cbxrbooking-meta-settings nav-tab-wrapper" data-post_id="' . get_the_ID() . '">';
				foreach ( $form_sections as $tab ) {
					$html .= sprintf( '<a href="#%1$s" class="nav-tab cbxrbooking-meta-nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
				}
				$html .= '</h2>';

				echo $html;
			}

			/**
			 * Show nav items for meta settings box
			 *
			 * @param type $form_sections
			 * @param type $form_fields
			 */
			public function cbxrbookingform_show_items( $form_sections, $form_fields, $post ) {

				wp_nonce_field( 'cbxrbookingmetabox', 'cbxrbookingmetabox[nonce]' );

				echo '<div class="metabox-holder">
                <div class="cbxrbooking-group">';
				foreach ( $form_sections as $key => $form_defination ) {
					?>
					<div id="<?php echo $form_defination['id']; ?>" style="padding: 20px; display: none;"
						 class="cbxrbooking-setting-meta group">
						<?php if ( array_key_exists( 'desc', $form_defination ) ): ?>
							<p><?php echo $form_defination['desc']; ?></p>
						<?php endif; ?>
						<table class="table form-table">

							<?php
								foreach ( $form_fields[ $form_defination['id'] ] as $form_fields_key => $form_field_defination ) {

									$callback = 'cbxrbooking_callback_' . $form_field_defination['type'];

									$form_field['section'] = $form_defination['id'];
									$form_field['id']      = $form_field_defination['name'];
									$form_field['label']   = $form_field_defination['label'];
									$form_field['desc']    = $form_field_defination['desc'];
									$form_field['default'] = $form_field_defination['default'];
									if ( isset( $form_field_defination['label_selector'] ) ) {
										$form_field['label_selector'] = $form_field_defination['label_selector'];
									}
									if ( isset( $form_field_defination['value_selector'] ) ) {
										$form_field['value_selector'] = $form_field_defination['value_selector'];
									}
									if ( isset( $form_field_defination['show_type'] ) ) {
										$form_field['show_type'] = $form_field_defination['show_type'];
									}

									if ( isset( $form_field_defination['options'] ) ) {
										$form_field['options'] = $form_field_defination['options'];
									}

									if ( isset( $form_field_defination['weekdays'] ) ) {
										$form_field['weekdays'] = $form_field_defination['weekdays'];
									}
									if ( isset( $form_field_defination['placeholder'] ) ) {
										$form_field['placeholder'] = $form_field_defination['placeholder'];
									}
									echo '<tr>';

									if ( is_callable( array( $this, $callback ), true ) ) {
										call_user_func( array( $this, $callback ), $form_field );
									}

									echo '</tr>';
								}
							?>
						</table>
					</div>

					<?php
				}
				echo '</div>
            </div>';
			}

			/**
			 * Call back for text field
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_text( $args ) {

				global $post;

				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$dropdown_html     = '';
				//$CBXRBooking_Admin = new CBXRBooking_Admin( CBXRBOOKING_PLUGIN_NAME, CBXRBOOKING_PLUGIN_VERSION );

				if ( isset( $args['label_selector'] ) || isset( $args['value_selector'] ) ) {

					if ( isset( $args['show_type'] ) && sizeof( $args['show_type'] ) > 0 ) {
						$show_type = $args['show_type'];
					} else {
						$show_type = array();
					}
					//$dropdown_html = $CBXRBooking_Admin->cbxrbooking_render_text_textarea_dropdown($args['label_selector'], $args['value_selector'],false,'',$show_type);
				}

				$value = esc_attr( $args['default'] );

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}


				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$html = sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';
				//$html .= '<p style="padding-bottom: 10px;">'.$dropdown_html.'</p>';
				$html .= sprintf( '<input type="text" style="height: 30px;" class="%3$s %1$s-text cbxrbookingfield_select_target" id="%5$s[%2$s][%3$s]" name="%5$s[settings][%2$s][%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value, $this->metakey );
				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Call back for text field repeat
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_text_repeat( $args ) {

				global $post;

				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$dropdown_html     = '';
				//$CBXRBooking_Admin = new CBXRBooking_Admin( CBXRBOOKING_PLUGIN_NAME, CBXRBOOKING_PLUGIN_VERSION );

				if ( isset( $args['label_selector'] ) || isset( $args['value_selector'] ) ) {

					if ( isset( $args['show_type'] ) && sizeof( $args['show_type'] ) > 0 ) {
						$show_type = $args['show_type'];
					} else {
						$show_type = array();
					}

				}

				$value = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				if ( ! is_array( $value ) ) {
					$value = array();
				}

				$value = array_filter($value);

				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				echo '<!-- mustache template -->
                <!--script id="textbox_repeat_template" type="x-tmpl-mustache">
                    <div class="cbxrbooking_repeat_field cbxrbooking_repeat_field_textbox">
                        <input type="text" style="height: 30px;" 
                        class="' . $args['id'] . ' ' . $size . '-text cbxrbookingfield_select_target"  
                        id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                        name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][]" 
                        value="" 
                        placeholder="' . $args['placeholder'] . '-{{incrementplus}}' . '"/>
                        <a href="#" title="' . esc_html__( 'Delete Textbox', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-repeat"></a>
                    </div>
                </--script-->';

				$html = sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';

				$html .= '<div class="cbxrbooking_repeat_fields_wrap cbxrbooking_repeat_fields_wrap_textbox">';
				$html .= '<div class="cbxrbooking_repeat_fields cbxrbooking_repeat_fields_textbox">';

				foreach ( $value as $index => $val ) {
					$html .= '<div class="cbxrbooking_repeat_field cbxrbooking_repeat_field_textbox">';
					$html .= sprintf( '<div class="cbxrbooking_textbox_wrap"><input type="text" style="height: 30px;" class="%3$s %1$s-text cbxrbookingfield_select_target" id="%5$s[%2$s][%3$s]-%6$d" name="%5$s[settings][%2$s][%3$s][]" value="%4$s"  placeholder="%8$s - %7$d" /></div>', $size, $args['section'], $args['id'], $val, $this->metakey, $index, ($index+1), $args['placeholder'] );
					$html .= '<a href="#" title="' . esc_html__( 'Move Textbox', 'cbxrbooking' ) . '" class="dashicons dashicons-menu move-textbox"></a>';
					$html .= '<a href="#" title="' . esc_html__( 'Delete Textbox', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-repeat"></a>';
					$html .= '</div>';
				}

				$html .= '</div>';
				$html .= '<a data-args_id="' . $args['id'] . '" data-size="' . $size . '" data-meta_key="' . $this->metakey . '" data-section="' . $args['section'] . '"
                    data-count="' . sizeof( $value ) . '" data-placeholder="' . $args['placeholder'] . '" class="button-secondary  cbxrbooking_textbox_repeat_trigger" href="#">' . esc_html__( 'Add New', 'cbxrbooking' ) . '</a>';

				$html .= '</div>';
				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Call back for scheduler field type
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_scheduler( $args ) {

				global $post;

				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$dropdown_html     = '';
				//$CBXRBooking_Admin = new CBXRBooking_Admin( CBXRBOOKING_PLUGIN_NAME, CBXRBOOKING_PLUGIN_VERSION );

				if ( isset( $args['label_selector'] ) || isset( $args['value_selector'] ) ) {

					if ( isset( $args['show_type'] ) && sizeof( $args['show_type'] ) > 0 ) {
						$show_type = $args['show_type'];
					} else {
						$show_type = array();
					}
					//$dropdown_html = $CBXRBooking_Admin->cbxrbooking_render_text_textarea_dropdown($args['label_selector'], $args['value_selector'],false,'',$show_type);
				}

				$value = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				if ( ! is_array( $value ) ) {
					$value = array();
				}

				$value = array_filter($value);

				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

				$checkboxes = '';
				foreach ( $args['weekdays'] as $daykey => $dayval ) {
					$checkboxes .= '<li class="">
                            <input type="checkbox" 
                            class="' . $args['id'] . ' ' . $size . '-text cbxrbookingfield_select_target"  
                            id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                            name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][weekdays][]" 
                            value="' . $daykey . '" >' . $dayval . '</input></li>';
				}

				echo '<!-- mustache template regular schedule -->
                <script id="scheduler_repeat_template" type="x-tmpl-mustache">
                        <div class="cbxrbooking_scheduler_repeat_field cbxrbooking_scheduler_repeat_field_scheduler">
                            <div class="cbxrbooking_scheduler_schedules_wrap">
                                <div class="cbxrbooking_scheduler_schedule cbxrbooking_scheduler_schedule_weekdays">
                                    <ul class="cbxrbooking_scheduler_week_nav">
                                        <li><span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Weekly', 'cbxrbooking' ) . '</li>
                                    </ul>
                                    <p>' . esc_html__( 'Days of the week', 'cbxrbooking' ) . '</p>
                                    <ul class="cbxrbooking_scheduler_checkboxes">
                                        ' . $checkboxes . '
                                    </ul>
                                </div>
                                <div class="cbxrbooking_scheduler_schedule cbxrbooking_scheduler_schedule_dayslots">
                                    <ul class="cbxrbooking_scheduler_time_nav">
                                        <li class="active"><a data-slotsel="allday" class="cbxrbooking_scheduler_time_nav_item" href="#"><span class="dashicons dashicons-clock"></span>' . esc_html__( 'All Day Opened', 'cbxrbooking' ) . '</a></li>
                                        <li class=""><a data-slotsel="slots" class="cbxrbooking_scheduler_time_nav_item" href="#"><span class="dashicons dashicons-backup"></span>' . esc_html__( 'Opening Time Slots', 'cbxrbooking' ) . '</a></li>
                                    </ul>
                                    <div class="cbxrbooking_scheduler_time_selection_wrap">
                                        <div class="time-selection-toggle time-selection-toggle-allday time-selection-toggle-active">' . __( 'All Day Long.  To select specific time slot click <strong>Time Slots</strong> from above.', 'cbxrbooking' ) . '</div>
                                        <div class="time-selection-toggle time-selection-toggle-slots">
                                            <div class="alignleft">
                                                <span>' . esc_html__( 'Start', 'cbxrbooking' ) . '</span>
                                                <input type="text" 
                                                    class="' . $args['id'] . ' ' . $size . ' small-text cbxrbookingfield_select_target cbxrb_scheduled_time"  
                                                    id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                                                    name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][times][start]" 
                                                    value="" />
                                                    <a class="input-button" title="clear" data-clear><i class="icon-close"></i></a>
                                            </div>
                                            <div class="alignleft">
                                                <span>' . esc_html__( 'End', 'cbxrbooking' ) . '</span>
                                                <input type="text" 
                                                    class="' . $args['id'] . ' ' . $size . ' small-text cbxrbookingfield_select_target cbxrb_scheduled_time"  
                                                    id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                                                    name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][times][end]" 
                                                    value="" />
                                                    <a class="input-button" title="clear" data-clear><i class="icon-close"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <a href="#" title="' . esc_html__( 'Move Schedule', 'cbxrbooking' ) . '" class="dashicons dashicons-menu move-schedule"></a>
                            <a href="#" title="' . esc_html__( 'Delete Schedule', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-schedule"></a>
                        </div>
                </script>';

				$html = sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';


				$html .= '<div class="cbxrbooking_repeat_fields_wrap cbxrbooking_repeat_fields_wrap_scheduler" id="cbxrbooking_repeat_fields_wrap_scheduler">'; //main parent wrapper

				$html .= '<div class="cbxrbooking_scheduler_repeat_fields cbxrbooking_scheduler_repeat_fields_scheduler" id="cbxrbooking_scheduler_repeat_fields_scheduler">'; //all fields wrapper,  we will use sorting based on this parent


				$value = array_splice( $value, 0, 7 );


				foreach ( $value as $index => $arr_values ) {
					if ( isset( $arr_values['weekdays'] ) ) {
						$html .= '<div class="cbxrbooking_scheduler_repeat_field cbxrbooking_scheduler_repeat_field_scheduler">'; //start of single field(actually multiple fields counted as one)

						$html .= '<div class="cbxrbooking_scheduler_schedules_wrap">';
						$html .= '<div class="cbxrbooking_scheduler_schedule cbxrbooking_scheduler_schedule_weekdays">';
						$html .= '<ul class="cbxrbooking_scheduler_week_nav">';
						$html .= '<li><span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Weekly', 'cbxrbooking' ) . '</li>';
						$html .= '</ul>';

						$html .= '<p>' . esc_html__( 'Days of the week', 'cbxrbooking' ) . '</p>';
						$html .= '<ul class="cbxrbooking_scheduler_checkboxes">';

						foreach ( $args['weekdays'] as $daykey => $dayval ) {
							$html .= sprintf( '<li class="">
                                                        <input type="checkbox"
                                                        class="%3$s %1$s-text cbxrbookingfield_select_target"
                                                        id="%5$s[%2$s][%3$s]-%6$d" name="%5$s[settings][%2$s][%3$s][%6$d][weekdays][]"
                                                        value="%7$s" %8$s>' . $dayval . '</input>
                                                    </li>', $size, $args['section'], $args['id'], $dayval, $this->metakey, $index, $daykey, checked( true, is_array( $arr_values ) ? in_array( $daykey, $arr_values['weekdays'] ) : false, false ) );
						}

						$html .= '</ul>';
						$html .= '<div class="clear"></div>';
						$html .= '</div>'; // .cbxrbooking_scheduler_schedule_weekdays

						$html           .= '<div class="cbxrbooking_scheduler_schedule cbxrbooking_scheduler_schedule_dayslots">';
						$time_selection = 0;
						if ( $arr_values['times']['start'] != '' || $arr_values['times']['end'] != '' ) {
							$time_selection = 1;
						}

						$html .= '';

						$html .= '<ul class="cbxrbooking_scheduler_time_nav">';
						$html .= '<li class="' . ( ( ! $time_selection ) ? 'active' : '' ) . '"><a data-slotsel="allday" class="cbxrbooking_scheduler_time_nav_item" href="#"><span class="dashicons dashicons-clock"></span>' . esc_html__( 'All Day Opened', 'cbxrbooking' ) . '</a></li>';
						$html .= '<li class="' . ( ( $time_selection ) ? 'active' : '' ) . '"><a data-slotsel="slots" class="cbxrbooking_scheduler_time_nav_item" href="#"><span class="dashicons dashicons-backup"></span>' . esc_html__( 'Opening Time Slots', 'cbxrbooking' ) . '</a></li>';
						$html .= '</ul>';

						$html .= '<div class="cbxrbooking_scheduler_time_selection_wrap">';

						$html .= '<div class="time-selection-toggle time-selection-toggle-allday ' . ( ( ! $time_selection ) ? 'time-selection-toggle-active' : '' ) . '" >' . __( 'All Day Long.  To select specific time slot click <strong>Time Slots</strong> from above.', 'cbxrbooking' ) . '</div>'; //.time-selection-toggle (1st tab)

						$html .= '<div class="time-selection-toggle time-selection-toggle-slots ' . ( ( $time_selection ) ? 'time-selection-toggle-active' : '' ) . '" >';
						$html .= sprintf( '<div class="alignleft">
                                                                    <span>' . esc_html__( 'Start', 'cbxrbooking' ) . '</span>
                                                                    <input type="text"
                                                                    class="%3$s %1$s small-text cbxrbookingfield_select_target cbxrb_scheduled_time"
                                                                    id="%4$s[%2$s][%3$s]-%5$d"
                                                                    name="%4$s[settings][%2$s][%3$s][%5$d][times][start]"
                                                                    value="%6$s" />
                                                                    <a class="input-button" title="clear" data-clear><i class="icon-close"></i></a>
                                                                 </div>', $size, $args['section'], $args['id'], $this->metakey, $index, isset( $arr_values['times']['start'] ) ? $arr_values['times']['start'] : '' );

						$html .= sprintf( '<div class="alignleft">
                                                                    <span>' . esc_html__( 'End', 'cbxrbooking' ) . '</span>
                                                                    <input type="text"
                                                                    class="%3$s %1$s small-text cbxrbookingfield_select_target cbxrb_scheduled_time"
                                                                    id="%4$s[%2$s][%3$s]-%5$d"
                                                                    name="%4$s[settings][%2$s][%3$s][%5$d][times][end]"
                                                                    value="%6$s" />
                                                                    <a class="input-button" title="clear" data-clear><i class="icon-close"></i></a>
                                                                 </div>', $size, $args['section'], $args['id'], $this->metakey, $index, isset( $arr_values['times']['end'] ) ? $arr_values['times']['end'] : '' );

						$html .= '</div>'; //.time-selection-toggle (2nd tab)


						$html .= '</div>';//.cbxrbooking_scheduler_time_selection_wrap
						$html .= '<div class="clear"></div>';

						$html .= '</div>'; //.cbxrbooking_scheduler_schedule_dayslots
						$html .= '<div class="clear"></div>';
						$html .= '</div>'; // .cbxrbooking_scheduler_schedules_wrap
						$html .= '<a href="#" title="' . esc_html__( 'Move Schedule', 'cbxrbooking' ) . '" class="dashicons dashicons-menu move-schedule"></a>';
						$html .= '<a href="#" title="' . esc_html__( 'Delete Schedule', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-schedule"></a>';
						$html .= '</div>'; //.cbxrbooking_scheduler_repeat_field_scheduler end start of single field(actually multiple fields counted as one)
					}
				}

				//$html .= '</div>';

				$html .= '</div>'; //.cbxrbooking_scheduler_repeat_fields_scheduler end all fields wrapper, we will use sorting based on this parent

				$add_display = 'display: inline';
				if ( sizeof( $value ) >= 7 ) {
					$add_display = 'display: none';
				}
				$html .= '<a data-count="' . sizeof( $value ) . '" style="' . $add_display . '; padding: 6px 12px;" class="button-secondary cbxrbooking_scheduler_repeat_trigger" href="#">' . esc_html__( 'Add New Schedule', 'cbxrbooking' ) . '</a>';

				$html .= '</div>'; //.cbxrbooking_repeat_fields_wrap_scheduler end main parent wrapper

				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Call back for scheduler exceptions field type
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_scheduler_exceptions( $args ) {

				global $post;

				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$dropdown_html     = '';
				$CBXRBooking_Admin = new CBXRBooking_Admin( CBXRBOOKING_PLUGIN_NAME, CBXRBOOKING_PLUGIN_VERSION );

				if ( isset( $args['label_selector'] ) || isset( $args['value_selector'] ) ) {

					if ( isset( $args['show_type'] ) && sizeof( $args['show_type'] ) > 0 ) {
						$show_type = $args['show_type'];
					} else {
						$show_type = array();
					}
					//$dropdown_html = $CBXRBooking_Admin->cbxrbooking_render_text_textarea_dropdown($args['label_selector'], $args['value_selector'],false,'',$show_type);
				}

				$value = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				if ( ! is_array( $value ) ) {
					$value = array();
				}

				$value = array_filter($value);



				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

				echo '<!-- mustache template for exception -->
                <script id="scheduler_exceptions_repeat_template" type="x-tmpl-mustache">
                        <div class="cbxrbooking_scheduler_exceptions_repeat_field cbxrbooking_scheduler_exceptions_repeat_field_scheduler">
                            <div class="cbxrbooking_scheduler_schedules_exceptions_wrap">
                                <div class="cbxrbooking_scheduler_exceptions_schedule cbxrbooking_scheduler_exceptions_schedule_date">
                                    <ul class="cbxrbooking_scheduler_exceptions_date_nav">
                                        <li><span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Date', 'cbxrbooking' ) . '</li>
                                    </ul>
                                    <div class="alignleft">
                                        <span>' . esc_html__( 'Date', 'cbxrbooking' ) . '</span>
                                        <input type="text"
                                            class="' . $args['id'] . ' ' . $size . ' -text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_date"  
                                            id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                                            name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][date]" 
                                            value="" 
                                            placeholder="' . esc_html__( 'date', 'cbxrbooking' ) . '"/>   
                                    </div>
                                </div>
                                <div class="cbxrbooking_scheduler_exceptions_schedule cbxrbooking_scheduler_exceptions_schedule_dayslots">
                                    <ul class="cbxrbooking_scheduler_exceptions_time_nav">
                                        <li class="active"><a data-slotsel="allday" class="cbxrbooking_scheduler_exceptions_time_nav_item" href="#"><span class="dashicons dashicons-clock"></span>' . esc_html__( 'All Day Closed', 'cbxrbooking' ) . '</a></li>
                                        <li class=""><a data-slotsel="slots" class="cbxrbooking_scheduler_exceptions_time_nav_item" href="#"><span class="dashicons dashicons-backup"></span>' . esc_html__( 'Openning Time Slots', 'cbxrbooking' ) . '</a></li>
                                    </ul>
                                    <div class="cbxrbooking_scheduler_exceptions_time_selection_wrap">
                                        <div class="time-selection-toggle time-selection-toggle-allday time-selection-toggle-active">' . __( 'All Day Long closed.  To select specific open time slot click <strong>Time Slots</strong> from above.', 'cbxrbooking' ) . '</div>
                                        <div class="time-selection-toggle time-selection-toggle-slots">
                                            <div class="alignleft">
                                                <span>' . esc_html__( 'Start', 'cbxrbooking' ) . '</span>
                                                <input type="text" 
                                                    class="' . $args['id'] . ' ' . $size . ' small-text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_time"  
                                                    id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                                                    name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][times][start]" 
                                                    value="" />
                                            </div>
                                            <div class="alignleft">
                                                <span>' . esc_html__( 'End', 'cbxrbooking' ) . '</span>
                                                <input type="text" 
                                                    class="' . $args['id'] . ' ' . $size . ' small-text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_time"  
                                                    id="' . $this->metakey . '[' . $args['section'] . '][' . $args['id'] . ']-{{increment}}"
                                                    name="' . $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . '][{{increment}}][times][end]" 
                                                    value="" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <a href="#" title="' . esc_html__( 'Move Schedule Exception', 'cbxrbooking' ) . '" class="dashicons dashicons-menu move-schedule-exceptions"></a>
                            <a href="#" title="' . esc_html__( 'Delete Schedule Exception', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-schedule-exceptions"></a>
                        </div>
                </script>';

				$html = sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';


				$html .= '<div class="cbxrbooking_repeat_fields_wrap cbxrbooking_repeat_fields_wrap_scheduler_exceptions" id="cbxrbooking_repeat_fields_wrap_scheduler_exceptions">'; //main parent wrapper

				$html .= '<div class="cbxrbooking_scheduler_exceptions_repeat_fields cbxrbooking_scheduler_repeat_fields_scheduler_exceptions" id="cbxrbooking_scheduler_repeat_fields_scheduler_exceptions">'; //all fields wrapper,  we will use sorting based on this parent



				foreach ( $value as $index => $arr_values ) {
					//var_dump($index);
					if ( isset( $arr_values['date'] ) && $arr_values['date'] != '' ) {
						$html .= '<div class="cbxrbooking_scheduler_exceptions_repeat_field cbxrbooking_scheduler_exceptions_repeat_field_scheduler">'; //start of single field(actually multiple fields counted as one)

						$html .= '<div class="cbxrbooking_scheduler_exceptions_schedules_wrap">';
						$html .= '<div class="cbxrbooking_scheduler_exceptions_schedule cbxrbooking_scheduler_exceptions_schedule_date">';
						$html .= '<ul class="cbxrbooking_scheduler_exceptions_date_nav">';
						$html .= '<li><span class="dashicons dashicons-calendar"></span> ' . esc_html__( 'Date', 'cbxrbooking' ) . '</li>';
						$html .= '</ul>';
						//var_dump($arr_values['date']);

						$html .= sprintf( '<div class="alignleft">
                                                                    <span>' . esc_html__( 'Date', 'cbxrbooking' ) . '</span>
                                                                    <input type="text"
                                                                    class="%3$s %1$s -text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_date"
                                                                    id="%4$s[%2$s][%3$s]-%5$d"
                                                                    name="%4$s[settings][%2$s][%3$s][%5$d][date]"
                                                                    value="%6$s" />
                                                                 </div>', $size, $args['section'], $args['id'], $this->metakey, $index, isset( $arr_values['date'] ) ? $arr_values['date'] : '' );

						$html .= '<div class="clear"></div>';
						$html .= '</div>'; // .cbxrbooking_scheduler_exceptions_schedule_date

						$html           .= '<div class="cbxrbooking_scheduler_exceptions_schedule cbxrbooking_scheduler_exceptions_schedule_dayslots">';
						$time_selection = 0;
						if ( $arr_values['times']['start'] != '' || $arr_values['times']['end'] != '' ) {
							$time_selection = 1;
						}

						$html .= '';

						$html .= '<ul class="cbxrbooking_scheduler_exceptions_time_nav">';
						$html .= '<li class="' . ( ( ! $time_selection ) ? 'active' : '' ) . '"><a data-slotsel="allday" class="cbxrbooking_scheduler_exceptions_time_nav_item" href="#"><span class="dashicons dashicons-clock"></span>' . esc_html__( 'All Day Closed', 'cbxrbooking' ) . '</a></li>';
						$html .= '<li class="' . ( ( $time_selection ) ? 'active' : '' ) . '"><a data-slotsel="slots" class="cbxrbooking_scheduler_exceptions_time_nav_item" href="#"><span class="dashicons dashicons-backup"></span>' . esc_html__( 'Openning Time Slots', 'cbxrbooking' ) . '</a></li>';
						$html .= '</ul>';

						$html .= '<div class="cbxrbooking_scheduler_exceptions_time_selection_wrap">';

						$html .= '<div class="time-selection-toggle time-selection-toggle-allday ' . ( ( ! $time_selection ) ? 'time-selection-toggle-active' : '' ) . '" >' . __( 'All Day Long <strong>closed</strong>.  To select specific open time slot click <strong>Time Slots</strong> from above.', 'cbxrbooking' ) . '</div>'; //.time-selection-toggle (1st tab)

						$html .= '<div class="time-selection-toggle time-selection-toggle-slots ' . ( ( $time_selection ) ? 'time-selection-toggle-active' : '' ) . '" >';
						$html .= sprintf( '<div class="alignleft">
                                                                    <span>' . esc_html__( 'Start', 'cbxrbooking' ) . '</span>
                                                                    <input type="text" class="%3$s %1$s small-text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_time"
                                                                    id="%4$s[%2$s][%3$s]-%5$d"  name="%4$s[settings][%2$s][%3$s][%5$d][times][start]" value="%6$s" />
                                                                 </div>', $size, $args['section'], $args['id'], $this->metakey, $index, isset( $arr_values['times']['start'] ) ? $arr_values['times']['start'] : '' );

						$html .= sprintf( '<div class="alignleft">
                                                                    <span>' . esc_html__( 'End', 'cbxrbooking' ) . '</span>
                                                                    <input type="text" class="%3$s %1$s small-text cbxrbookingfield_select_target cbxrb_scheduled_exceptions_time"
                                                                    id="%4$s[%2$s][%3$s]-%5$d" name="%4$s[settings][%2$s][%3$s][%5$d][times][end]" value="%6$s" />
                                                                 </div>', $size, $args['section'], $args['id'], $this->metakey, $index, isset( $arr_values['times']['end'] ) ? $arr_values['times']['end'] : '' );

						$html .= '</div>'; //.time-selection-toggle (2nd tab)


						$html .= '</div>';//.cbxrbooking_scheduler_exceptions_time_selection_wrap
						$html .= '<div class="clear"></div>';

						$html .= '</div>'; //.cbxrbooking_scheduler_exceptions_schedule_dayslots
						$html .= '<div class="clear"></div>';
						$html .= '</div>'; // .cbxrbooking_scheduler_exceptions_schedules_wrap
						$html .= '<a href="#" title="' . esc_html__( 'Move Exception', 'cbxrbooking' ) . '" class="dashicons dashicons-menu move-schedule-exceptions"></a>';
						$html .= '<a href="#" title="' . esc_html__( 'Delete Exception', 'cbxrbooking' ) . '" class="dashicons dashicons-post-trash trash-schedule-exceptions"></a>';
						$html .= '</div>'; //.cbxrbooking_scheduler_exceptions_repeat_field_scheduler end start of single field(actually multiple fields counted as one)
					}
				}



				$html .= '</div>'; //.cbxrbooking_scheduler_exceptions_repeat_fields_scheduler end all fields wrapper, we will use sorting based on this parent

				$html .= '<a data-count="' . sizeof( $value ) . '" class="button-secondary cbxrbooking_scheduler_exceptions_repeat_trigger" href="#">' . esc_html__( 'Add New Exception', 'cbxrbooking' ) . '</a>';

				$html .= '</div>'; //.cbxrbooking_repeat_fields_wrap_scheduler_exceptions end main parent wrapper

				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Displays a rich text wysiwyg for a settings field
			 *
			 * @param array $args settings field args
			 */
			function cbxrbooking_callback_wysiwyg( $args ) {
				global $post;
				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$value = $args['default'];
				$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				$editor_settings = array(
					'teeny'         => true,
					'textarea_name' => $this->metakey . '[settings][' . $args['section'] . '][' . $args['id'] . ']',
					'textarea_rows' => 10,
					'editor_class'  => 'cbxrbookingfield_select_target_wysiwyg'
				);

				echo sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				echo '</td>';
				echo '<td style="max-width: ' . $size . ';">';
				wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
				echo sprintf( '<br/><span style="" class="description"> %s</span></td>', $args['desc'] );

			}

			/**
			 * Callback for number
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_number( $args ) {


				global $post;
				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );
				$value            = '';


				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}


				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$html = sprintf( '<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td>';

				$html .= sprintf( '<td><input type="number" style="height: 30px;" class="%1$s-text" id="%2$s-%3$s" name="%5$s[settings][%2$s][%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value, $this->metakey );
				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Displays a info field
			 *
			 * @param array $args settings field args
			 */
			function cbxrbooking_callback_title( $args ) {
				$html = sprintf( '<td colspan="2"><h3 class="setting_heading_title"><span>%s</span></h3></td>', $args['label'] );
				echo $html;
			}

			/**
			 * Displays a info field
			 *
			 * @param array $args settings field args
			 */
			function cbxrbooking_callback_subtitle( $args ) {
				$html = sprintf( '<td colspan="2"><h4 class="setting_heading_subtitle"><span>%s</span></h4></td>', $args['label'] );
				echo $html;
			}

			/**
			 * Call back for text field
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_color( $args ) {

				global $post;


				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', false );
				$value            = '';

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}


				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$html = sprintf( '<td><span style="" class="cbxrbooking_meta_settings_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td>';
				$html .= '<td>';
				$html .= sprintf( '<input type="text" style="height: 30px;" class="%3$s %1$s-text wp-color-picker-field" id="%5$s[%2$s][%3$s]" name="%5$s[settings][%2$s][%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value, $this->metakey );
				$html .= sprintf( '<br><span style="" class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Call back for text area
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_textarea( $args ) {

				global $post;
				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );
				$value            = '';

				//$CBXRBooking_Admin = new CBXRBooking_Admin(CBXRBOOKING_BASE_NAME,CBXRBOOKING_PLUGIN_VERSION);

				if ( isset( $args['label_selector'] ) || isset( $args['value_selector'] ) ) {

					if ( isset( $args['show_type'] ) && sizeof( $args['show_type'] ) > 0 ) {
						$show_type = $args['show_type'];
					} else {
						$show_type = array();
					}

				}

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$html = sprintf( '<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';

				$html .= sprintf( '<textarea style="width: 350px;" rows="5" cols="140" class="%3$s %1$s-text cbxrbookingfield_select_target" id="%2$s[%3$s]" name="%5$s[settings][%2$s][%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value, $this->metakey );

				$html .= sprintf( '<br><span class="description"> %s</span></td>', $args['desc'] );
				echo $html;
			}

			/**
			 * Call back for check box
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_checkbox( $args ) {


				global $post;

				$post_id          = $post->ID;
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );
				$value            = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}


				$html = sprintf( '<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td>';
				$html .= sprintf( '<input type="hidden" name="%3$s[settings][%1$s][%2$s]" value="off" />', $args['section'], $args['id'], $this->metakey );
				$html .= sprintf( '<td><input type="checkbox" class="checkbox js-switch cbxrbookingeditjs-switch" id="%1$s[%2$s]" name="%5$s[settings][%1$s][%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ), $this->metakey );
				$html .= sprintf( '<span for="%1$s[%2$s]"> %3$s</span></td>', $args['section'], $args['id'], $args['desc'] );

				echo $html;
			}

			/**
			 * Call back for check box
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_radio( $args ) {

				global $post;
				$post_id = $post->ID;

				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$value = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				$html = sprintf( '<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td><td>';


				foreach ( $args['options'] as $key => $label ) {
					$html .= sprintf( '<input type="radio" class="%1$s"  name="%5$s[settings][%1$s][%2$s]" id="%1$s[%2$s]"" value="%7$s" %4$s /> %6$s ', $args['section'], $args['id'], $value, checked( $value, $key, false ), $this->metakey, $label, $key );
				}

				$html .= sprintf( '<br /><br /><span for="%1$s[%2$s]"> %3$s</span></td>', $args['section'], $args['id'], $args['desc'] );

				echo $html;
			}

			/**
			 * Callback for select field
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			public function cbxrbooking_callback_select( $args ) {

				global $post;
				$post_id = $post->ID;

				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );

				$value = $args['default'];

				if ( isset( $cbxrbooking_meta['settings'] ) && isset( $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
					if ( is_array( $cbxrbooking_meta['settings'] ) && ! empty( $cbxrbooking_meta['settings'] ) && array_key_exists( $args['id'], $cbxrbooking_meta['settings'][ $args['section'] ] ) ) {
						$value = $cbxrbooking_meta['settings'][ $args['section'] ][ $args['id'] ];
					}
				}

				$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$html = sprintf( '<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label'] );
				$html .= '</td>';
				$html .= sprintf( '<td><select class="%1$s"  name="%4$s[settings][%2$s][%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'], $this->metakey );

				foreach ( $args['options'] as $key => $label ) {

					$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
				}

				$html .= sprintf( '</select>' );


				$html .= sprintf( '<br><span class="description"> %s</span></td>', $args['desc'] );

				echo $html;
			}

			/**
			 * Callback for select field
			 *
			 * @global type $post
			 *
			 * @param type  $args
			 */
			/*public function cbxrbooking_callback_schedulewrappper($args) {
	
				global $post;
				$post_id = $post->ID;
	
				$cbxrbooking_meta = get_post_meta($post_id, '_cbxrbookingmeta', TRUE);
	
				$value = $args['default'];
	
				if (isset($cbxrbooking_meta['settings']) && isset($cbxrbooking_meta['settings'][$args['section']])) {
					if (is_array($cbxrbooking_meta['settings']) && !empty($cbxrbooking_meta['settings']) && array_key_exists($args['id'], $cbxrbooking_meta['settings'][$args['section']])) {
						$value = $cbxrbooking_meta['settings'][$args['section']][$args['id']];
					}
				}
	
				$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
				$html = sprintf('<td><span style="" class="cbxrbooking_label"><strong> %s</strong></span>', $args['label']);
				$html .= '</td>';
				$html .= sprintf('<td><select class="%1$s"  name="%4$s[settings][%2$s][%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'], $this->metakey);
	
				foreach ($args['options'] as $key => $label) {
	
					$html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
				}
	
				$html .= sprintf('</select>');
	
	
				$html .= sprintf('<br><span class="description"> %s</span></td>', $args['desc']);
	
			}*/


			/**
			 * Tabbable JavaScript codes & Initiate Color Picker
			 *
			 * This code uses localstorage for displaying active tabs
			 */
			function script() {
				?>
				<script type="text/javascript">

					jQuery(document).ready(function ($) {

						//Initiate Color Picker
						$('.wp-color-picker-field').wpColorPicker();

						//text_repeat field
						var $textbox_repeat_template = '<div class="cbxrbooking_repeat_field cbxrbooking_repeat_field_textbox"> ' +
							'<div class="cbxrbooking_textbox_wrap">' +
							'<input type="text" style="height: 30px;" ' +
							'class="{{args_id}} {{size}}-text cbxrbookingfield_select_target"' +
							' id="{{metakey}}[{{section}}][{{args_id}}]-{{increment}}"' +
							' name="{{metakey}}[settings][{{section}}][{{args_id}}][]"' +
							' value=""' +
							' placeholder="{{placeholder}} - {{incrementplus}}"/> ' +
							'</div>' +
							'<a href="#" title="<?php echo esc_html__( 'Move Textbox', 'cbxrbooking' ); ?>" class="dashicons dashicons-menu move-textbox"></a>' +
							'<a href="#" title="<?php echo esc_html__( 'Delete Textbox', 'cbxrbooking' ); ?>" class="dashicons dashicons-post-trash trash-repeat"></a>' +
							'</div>';


						$('.cbxrbooking_repeat_fields_wrap_textbox').on('click', 'a.cbxrbooking_textbox_repeat_trigger', function (e) {
							e.preventDefault();

							var $this        = $(this);
							var $field_panel = $this.prev('.cbxrbooking_repeat_fields_textbox');
							var $count       = $this.data('count');


							var $size        = $this.data('size');
							var $metakey     = $this.data('meta_key');
							var $section     = $this.data('section');
							var $args_id     = $this.data('args_id');
							var $placeholder = $this.data('placeholder');

							var $textbox_dynamic_datas = {
								size         : $size,
								metakey      : $metakey,
								section      : $section,
								args_id      : $args_id,
								placeholder  : $placeholder,
								increment    : $count,
								incrementplus: ($count + 1)
							};

							var rendered = Mustache.to_html($textbox_repeat_template, $textbox_dynamic_datas);
							$field_panel.append(rendered);

							$this.data('count', $count + 1);
						});

						//text_repeat field remove
						$('.cbxrbooking_repeat_fields_textbox').on('click', '.trash-repeat', function (e) {
							e.preventDefault();

							var $this = $(this);
							$this.parent('.cbxrbooking_repeat_field_textbox').remove();
						});
						//end text field repeat

						//scheduler field repeat

						// in setting scheduler event js flatpickr trigger
						$('.cbxrbooking_scheduler_repeat_fields_scheduler').on('focus', ".cbxrb_scheduled_time", function () {

							$(this).flatpickr({
								disableMobile: true,
								enableTime   : true,
								noCalendar   : true,

								enableSeconds: false, // disabled by default

								time_24hr: true,

								// default format
								dateFormat: "H:i",

								// initial values for time. don't use these to preload a date
								defaultHour  : 12,
								defaultMinute: 0,
								//wrap: true

								// Preload time with defaultDate instead:
								// defaultDate: "3:30"
							});
						});

						var $scheduler_repeat_template = $('#scheduler_repeat_template').html();
						Mustache.parse($scheduler_repeat_template);   // optional, speeds up future uses

						$('.cbxrbooking_repeat_fields_wrap_scheduler').on('click', 'a.cbxrbooking_scheduler_repeat_trigger', function (e) {

							e.preventDefault();

							var $this        = $(this);
							var $field_panel = $this.prev('.cbxrbooking_scheduler_repeat_fields');

							var $count = $this.data('count');
							if ($count >= 7) {
								alert('Sorry! Maximum 7 booking schedule is possible in a week.'); //todo:translation
								return false;
							}

							var rendered = Mustache.render($scheduler_repeat_template, {
								increment    : $count,
								//incrementplus: ($count + 1)
							});
							$field_panel.append(rendered);
							$this.data('count', $count + 1);
						});

						//scheduler field repeat remove
						$('.cbxrbooking_scheduler_repeat_fields_scheduler').on('click', '.trash-schedule', function (e) {

							e.preventDefault();

							var $this = $(this);

							$this.parent('.cbxrbooking_scheduler_repeat_field_scheduler').remove();

							var schedule_repeat_btn   = $('.cbxrbooking_repeat_fields_wrap_scheduler').find('.cbxrbooking_scheduler_repeat_trigger');
							var schedule_repeat_count = parseInt(schedule_repeat_btn.data('count'));
							schedule_repeat_btn.data('count', schedule_repeat_count - 1);

							if (schedule_repeat_count - 1 >= 7) {
								schedule_repeat_btn.css('display', 'none');
							} else {
								schedule_repeat_btn.css('display', 'inline');
							}
						});

						$('.cbxrbooking_scheduler_repeat_fields_scheduler').on('click', '.cbxrbooking_scheduler_time_nav_item', function (e) {

							e.preventDefault();

							var $this     = $(this);
							var $this_nav = $this.parents('.cbxrbooking_scheduler_time_nav');
							$this_nav.find('li').removeClass('active');
							$this.parent('li').addClass('active');
							$this_nav.next('.cbxrbooking_scheduler_time_selection_wrap').find('.time-selection-toggle').removeClass('time-selection-toggle-active');

							var $slotsel = $this.data('slotsel');
							if ($slotsel == 'allday') {
								$this_nav.next('.cbxrbooking_scheduler_time_selection_wrap').find('.time-selection-toggle-allday').addClass('time-selection-toggle-active');

								//clear the time selection
								$this_nav.next('.cbxrbooking_scheduler_time_selection_wrap').find('.cbxrb_scheduled_time').each(function (index, element) {
									if (typeof  element._flatpickr !== "undefined") {
										element._flatpickr.clear();
									}
									else {
										$(element).val('');
									}
								});

							}
							else {
								$this_nav.next('.cbxrbooking_scheduler_time_selection_wrap').find('.time-selection-toggle-slots').addClass('time-selection-toggle-active');
							}

						});

						//end scheduler field repeat


						//scheduler exceptions field repeat

						$('.cbxrbooking_scheduler_repeat_fields_scheduler_exceptions').on('focus', ".cbxrb_scheduled_exceptions_date", function (event) {
							$(this).flatpickr({
								disableMobile: true,
								minDate      : "today"
							});
						});


						$('.cbxrbooking_scheduler_repeat_fields_scheduler_exceptions').on('focus', ".cbxrb_scheduled_exceptions_time", function (event) {

							$(this).flatpickr({
								disableMobile: true,
								enableTime   : true,
								noCalendar   : true,

								enableSeconds: false, // disabled by default

								time_24hr: true,

								// default format
								dateFormat: "H:i",

								// initial values for time. don't use these to preload a date
								defaultHour  : 12,
								defaultMinute: 0

								// Preload time with defaultDate instead:
								// defaultDate: "3:30"
							});
						});

						var $scheduler_exceptions_repeat_template = $('#scheduler_exceptions_repeat_template').html();
						Mustache.parse($scheduler_exceptions_repeat_template);   // optional, speeds up future uses

						$('.cbxrbooking_repeat_fields_wrap_scheduler_exceptions').on('click', 'a.cbxrbooking_scheduler_exceptions_repeat_trigger', function (e) {

							e.preventDefault();

							var $this        = $(this);
							var $field_panel = $this.prev('.cbxrbooking_scheduler_exceptions_repeat_fields');

							var $count = $this.data('count');

							var rendered = Mustache.render($scheduler_exceptions_repeat_template, {
								increment    : $count,
								//incrementplus: ($count + 1)
							});
							$field_panel.append(rendered);
							$this.data('count', $count + 1);
						});

						//scheduler exceptions field repeat remove
						$('.cbxrbooking_scheduler_repeat_fields_scheduler_exceptions').on('click', '.trash-schedule-exceptions', function (e) {

							e.preventDefault();

							var $this = $(this);
							$this.parent('.cbxrbooking_scheduler_exceptions_repeat_field_scheduler').remove();
						});

						$('.cbxrbooking_scheduler_repeat_fields_scheduler_exceptions').on('click', '.cbxrbooking_scheduler_exceptions_time_nav_item', function (e) {

							e.preventDefault();

							var $this     = $(this);
							var $this_nav = $this.parents('.cbxrbooking_scheduler_exceptions_time_nav');
							$this_nav.find('li').removeClass('active');
							$this.parent('li').addClass('active');
							$this_nav.next('.cbxrbooking_scheduler_exceptions_time_selection_wrap').find('.time-selection-toggle').removeClass('time-selection-toggle-active');

							var $slotsel = $this.data('slotsel');
							if ($slotsel == 'allday') {
								$this_nav.next('.cbxrbooking_scheduler_exceptions_time_selection_wrap').find('.time-selection-toggle-allday').addClass('time-selection-toggle-active');

								//clear the time selection
								$this_nav.next('.cbxrbooking_scheduler_exceptions_time_selection_wrap').find('.cbxrb_scheduled_exceptions_time').each(function (index, element) {
									if (typeof  element._flatpickr !== "undefined") {
										element._flatpickr.clear();
									}
									else {
										$(element).val('');
									}
								});

							}
							else {
								$this_nav.next('.cbxrbooking_scheduler_exceptions_time_selection_wrap').find('.time-selection-toggle-slots').addClass('time-selection-toggle-active');

							}

						});

						//end scheduler exceptions field repeat

						// Switches option sections
						$('.group').hide();
						var activetab = '';
						if (typeof (localStorage) != 'undefined') {
							//get
							activetab = localStorage.getItem("cbxrbookingmetaactivetab_" + $('.nav-tab-wrapper').data('post_id'));

						}
						if (activetab != '' && $(activetab).length) {
							$(activetab).fadeIn();
						} else {
							$('.group:first').fadeIn();
						}

						$('.group .collapsed').each(function (index, element) {
							$(this).find('input:checked').parent().parent().parent().nextAll().each(
								function () {
									if ($(this).hasClass('last')) {
										$(this).removeClass('hidden');
										return false;
									}
									$(this).filter('.hidden').removeClass('hidden');
								});
						});

						if (activetab != '' && $(activetab + '-tab').length) {
							$(activetab + '-tab').addClass('nav-tab-active');
						}
						else {
							$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
						}

						$('.nav-tab-wrapper a').on('click', function (evt) {
							evt.preventDefault();
							$('.nav-tab-wrapper a').removeClass('nav-tab-active');
							$(this).addClass('nav-tab-active').blur();
							var clicked_group = $(this).attr('href');
							if (typeof (localStorage) != 'undefined') {
								//set
								localStorage.setItem("cbxrbookingmetaactivetab_" + $('.nav-tab-wrapper').data('post_id'), $(this).attr('href'));
							}
							$('.group').hide();
							$(clicked_group).fadeIn();

						});

						//file browser field js
						$('.wpsa-browse').on('click', function (event) {
							event.preventDefault();

							var self = $(this);

							// Create the media frame.
							var file_frame = wp.media.frames.file_frame = wp.media({
								title   : self.data('uploader_title'),
								button  : {
									text: self.data('uploader_button_text')
								},
								multiple: false
							});

							file_frame.on('select', function () {
								attachment = file_frame.state().get('selection').first().toJSON();

								self.prev('.wpsa-url').val(attachment.url);
							});

							// Finally, open the modal
							file_frame.open();
						});

						//add chooser

						// jquery sortable js
						//sorting textbox repeat fields
						var adjustment_textbox;
						$(".cbxrbooking_repeat_fields_textbox").sortable({
							vertical         : true,
							handle           : '.move-textbox',
							containerSelector: '.cbxrbooking_repeat_fields_textbox',
							itemSelector     : '.cbxrbooking_repeat_field_textbox',
							placeholder      : '<div class="cbxrbooking_repeat_field_textbox_placeholder"/>',
							// animation on drop
							onDrop           : function ($item, container, _super) {
								var $clonedItem = $('<div />').css({height: 0});
								$item.before($clonedItem);
								$clonedItem.animate({'height': $item.height()});

								$item.animate($clonedItem.position(), function () {
									$clonedItem.detach();
									_super($item, container);
								});
							},
							// set $item relative to cursor position
							onDragStart      : function ($item, container, _super) {
								var offset  = $item.offset(),
									pointer = container.rootGroup.pointer;

								adjustment_textbox = {
									left: pointer.left - offset.left,
									top : pointer.top - offset.top
								};

								_super($item, container);
							},
							onDrag           : function ($item, position) {
								$item.css({
									left: position.left - adjustment_textbox.left,
									top : position.top - adjustment_textbox.top
								});
							}

						});

						// jquery sortable js
						//sorting booking scheduler updates
						var adjustment_scheduler;
						$("#cbxrbooking_scheduler_repeat_fields_scheduler").sortable({
							vertical         : true,
							handle           : '.move-schedule',
							containerSelector: '#cbxrbooking_scheduler_repeat_fields_scheduler',
							itemSelector     : '.cbxrbooking_scheduler_repeat_field_scheduler',
							placeholder      : '<div class="cbxrbooking_scheduler_repeat_field_scheduler_placeholder"/>',
							// animation on drop
							onDrop           : function ($item, container, _super) {
								var $clonedItem = $('<div />').css({height: 0});
								$item.before($clonedItem);
								$clonedItem.animate({'height': $item.height()});

								$item.animate($clonedItem.position(), function () {
									$clonedItem.detach();
									_super($item, container);
								});
							},

							// set $item relative to cursor position
							onDragStart: function ($item, container, _super) {
								var offset  = $item.offset(),
									pointer = container.rootGroup.pointer;

								adjustment_scheduler = {
									left: pointer.left - offset.left,
									top : pointer.top - offset.top
								};

								_super($item, container);
							},
							onDrag     : function ($item, position) {
								$item.css({
									left: position.left - adjustment_scheduler.left,
									top : position.top - adjustment_scheduler.top
								});
							}

						});

						// jquery sortable js
						//sorting booking scheduler exceptionsupdates
						var adjustment_scheduler_exceptions;
						$("#cbxrbooking_scheduler_repeat_fields_scheduler_exceptions").sortable({
							vertical         : true,
							handle           : '.move-schedule-exceptions',
							containerSelector: '#cbxrbooking_scheduler_repeat_fields_scheduler_exceptions',
							itemSelector     : '.cbxrbooking_scheduler_exceptions_repeat_field_scheduler',
							placeholder      : '<div class="cbxrbooking_scheduler_repeat_field_scheduler_exceptions_placeholder"/>',
							// animation on drop
							onDrop           : function ($item, container, _super) {
								var $clonedItem = $('<div />').css({height: 0});
								$item.before($clonedItem);
								$clonedItem.animate({'height': $item.height()});

								$item.animate($clonedItem.position(), function () {
									$clonedItem.detach();
									_super($item, container);
								});
							},

							// set $item relative to cursor position
							onDragStart: function ($item, container, _super) {
								var offset  = $item.offset(),
									pointer = container.rootGroup.pointer;

								adjustment_scheduler_exceptions = {
									left: pointer.left - offset.left,
									top : pointer.top - offset.top
								};

								_super($item, container);
							},
							onDrag     : function ($item, position) {
								$item.css({
									left: position.left - adjustment_scheduler_exceptions.left,
									top : position.top - adjustment_scheduler_exceptions.top
								});
							}

						});

					});
				</script>

				<style type="text/css">
					body.dragging, body.dragging * {
						cursor: move !important;
					}

					.dragged {
						position: absolute;
						opacity: 0.5;
						z-index: 2000;
					}

					/** WordPress 3.8 Fix **/
					.form-table th {
						padding: 20px 10px;
					}

					#wpbody-content .metabox-holder {
						padding-top: 5px;
					}

					.chosen-container-single, .chosen-container-multi {
						min-width: 244px !important;
					}

					#poststuff h2.nav-tab-wrapper {
						margin-bottom: 0px !important;
						padding-bottom: 0px;;
					}

					.nav-tab-active, .nav-tab-active:hover {
						background: #fff !important;
					}

					.nav-tab-active, .nav-tab-active:focus, .nav-tab-active:focus:active, .nav-tab-active:hover, .postbox {
						border-top: 2px solid #0085ba;
					}

					/* Field: Textbox */
					.cbxrbooking_repeat_fields_textbox {
						position: relative;
					}

					.cbxrbooking_repeat_field_textbox {
						border: 1px solid #ccc;
						margin-bottom: 5px;
						padding: 5px;
						position: relative;
					}

					.cbxrbooking_repeat_field_textbox_placeholder {
						border: 1px dashed #000;
						min-height: 50px;
						position: relative;
						margin-bottom: 5px;
						background-color: #fff;
					}

					.cbxrbooking_repeat_field_textbox_placeholder:before {
						position: absolute;
					}

					.cbxrbooking_repeat_field_textbox .trash-repeat {
						position: absolute;
						right: 2px;
						top: 2px;
					}

					.cbxrbooking_repeat_field_textbox .move-textbox {
						position: absolute;
						right: 30px;
						top: 2px;
						cursor: move;
					}

					/* End Field: Textbox*/

					/* Field: scheduler */
					#cbxrbooking_scheduler_repeat_fields_scheduler {
						position: relative;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler {
						border: 1px solid #ccc;
						margin-bottom: 5px;
						padding: 5px;
						position: relative;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler_placeholder {
						border: 1px dashed #ccc;
						min-height: 100px;
						position: relative;
						margin-bottom: 5px;
						background-color: #fff;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler_placeholder:before {
						position: absolute;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler .trash-schedule {
						position: absolute;
						right: 2px;
						top: 2px;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler .move-schedule {
						position: absolute;
						right: 30px;
						top: 2px;
						cursor: move;
					}

					ul.cbxrbooking_scheduler_checkboxes li {
						display: inline;
					}

					.cbxrbooking_scheduler_schedule {
						float: left;
						display: inline-block;
						width: 48%;
						margin-right: 1%;
					}

					.cbxrbooking_scheduler_schedule_dayslots {
						float: right;
						width: 48%;
					}

					.cbxrbooking_scheduler_time_nav {
						list-style: none;
					}

					.cbxrbooking_scheduler_time_nav li {
						display: inline-block;
						padding: 5px;
					}

					.cbxrbooking_scheduler_time_nav li.active {
						font-weight: bold;
					}

					.cbxrbooking_scheduler_time_nav li a {
						color: #000000;
						text-decoration: none;
					}

					.time-selection-toggle {
						display: none;
					}

					.time-selection-toggle-active {
						display: block;
					}

					/* End Field: scheduler*/

					/* Field: scheduler Exceptions */
					#cbxrbooking_scheduler_repeat_fields_scheduler_exceptions {
						position: relative;
					}

					.cbxrbooking_scheduler_exceptions_repeat_field_scheduler {
						border: 1px solid #ccc;
						margin-bottom: 5px;
						padding: 5px;
						position: relative;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler_exceptions_placeholder {
						border: 1px dashed #ccc;
						min-height: 100px;
						position: relative;
						margin-bottom: 5px;
						background-color: #fff;
					}

					.cbxrbooking_scheduler_repeat_field_scheduler_exceptions_placeholder:before {
						position: absolute;
					}

					.cbxrbooking_scheduler_exceptions_repeat_field_scheduler .trash-schedule-exceptions {
						position: absolute;
						right: 2px;
						top: 2px;
					}

					.cbxrbooking_scheduler_exceptions_repeat_field_scheduler .move-schedule-exceptions {
						position: absolute;
						right: 30px;
						top: 2px;
						cursor: move;
					}

					.cbxrbooking_scheduler_exceptions_schedule {
						float: left;
						display: inline-block;
						width: 48%;
						margin-right: 1%;
					}

					.cbxrbooking_scheduler_exceptions_schedule_dayslots {
						float: right;
						width: 48%;
					}

					.cbxrbooking_scheduler_exceptions_time_nav {
						list-style: none;
					}

					.cbxrbooking_scheduler_exceptions_time_nav li {
						display: inline-block;
						padding: 5px;
					}

					.cbxrbooking_scheduler_exceptions_time_nav li.active {
						font-weight: bold;
					}

					.cbxrbooking_scheduler_exceptions_time_nav li a {
						color: #000000;
						text-decoration: none;
					}

					.time-selection-toggle {
						display: none;
					}

					.time-selection-toggle-active {
						display: block;
					}

					/* End Field: scheduler*/

				</style>
				<?php
			}
		}
	endif;