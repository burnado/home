<?php
/* Exit if accessed directly */
defined( 'ABSPATH' ) or die( '-1' );

/**
 * Module Name: Typewriter
 * Description: Display Typewriter content
 */
class TB_Typewriter extends Themify_Builder_Component_Module
{

	public function __construct()
	{
		parent::__construct(
			array(
				'name' => __( 'Typewriter', 'builder-typewriter' ),
				'slug' => 'typewriter',
				'category' => array('addon')
			)
		);
	}

	public function get_assets()
	{
		$instance = Builder_Typewriter::get_instance();
		return array(
			'selector' => '[data-typer-targets]',
			'css' => themify_enque( $instance->url . 'assets/style.css' ),
			'js' => themify_enque( $instance->url . 'assets/frontend-scripts.js' ),
			'ver' => $instance->version,
			'external' => Themify_Builder_Model::localize_js( 'tb_typewriter_vars', array(
					'url' => $instance->url
				)
			)
		);
	}

	public function get_title( $module )
	{
		return isset( $module['mod_settings']['mod_title_typewriter'] ) ? wp_trim_words( $module['mod_settings']['mod_title_typewriter'], 100 ) : '';
	}

	public function get_options()
	{
		return array(
			array(
				'id' => 'mod_title_typewriter',
				'type' => 'title'
			),
			// Typewriter
			array(
				'type' => 'separator',
				'label' => __( 'Typewriter', 'builder-typewriter' ),
			),
			array(
				'id' => 'builder_typewriter_tag',
				'type' => 'select',
				'label' => __( 'Text Tag', 'builder-typewriter' ),
				'options' => array(
					'p' => 'p',
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6',
				)
			),
			array(
				'id' => 'builder_typewriter_text_before',
				'type' => 'text',
				'label' => __( 'Before Text', 'builder-typewriter' ),
				'class' => 'fullwidth',
				'control' => array(
					'selector' => '.typewritter-text-before'
				)
			),
			array(
				'id' => 'builder_typewriter_text_after',
				'type' => 'text',
				'label' => __( 'After Text', 'builder-typewriter' ),
				'class' => 'fullwidth',
				'control' => array(
					'selector' => '.typewritter-text-after'
				)
			),
			array(
				'id' => 'builder_typewriter_terms',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'builder_typewriter_term',
						'type' => 'text',
						'label' => __( 'Text', 'builder-typewriter' ),
						'class' => 'large'
					)
				),
				'new_row' => __( 'Add New Text', 'builder-typewriter' )
			),
			// Effects
			array(
				'type' => 'separator',
				'label' => __( 'Effects', 'builder-typewriter' ),
			),
			array(
				'id' => 'builder_typewriter_highlight_speed',
				'type' => 'select',
				'label' => __( 'Highlight Speed', 'builder-typewriter' ),
				'help' => __( 'Select "None" to disable highlight', 'builder-typewriter' ),
				'options' => array(
					'50' => __( 'Normal', 'builder-typewriter' ),
					'100' => __( 'Slow', 'builder-typewriter' ),
					'25' => __( 'Fast', 'builder-typewriter' ),
					'0' => __( 'None', 'builder-typewriter' ),
				)
			),
			array(
				'id' => 'builder_typewriter_type_speed',
				'type' => 'select',
				'label' => __( 'Type Speed', 'builder-typewriter' ),
				'options' => array(
					'150' => __( 'Normal', 'builder-typewriter' ),
					'220' => __( 'Slow', 'builder-typewriter' ),
					'90' => __( 'Fast', 'builder-typewriter' ),
				),
			),
			array(
				'id' => 'builder_typewriter_clear_delay',
				'type' => 'number',
				'label' => __( 'Clear Delay', 'builder-typewriter' ),
				'step'=>0.1,
				'after' => __( 'second(s)', 'builder-typewriter' )
			),
			array(
				'id' => 'builder_typewriter_type_delay',
				'type' => 'number',
				'label' => __( 'Type Delay', 'builder-typewriter' ),
				'step'=>0.1,
				'after' => __( 'second(s)', 'builder-typewriter' )
			),
			array(
				'id' => 'builder_typewriter_typer_interval',
				'type' => 'number',
				'label' => __( 'Highlight Delay', 'builder-typewriter' ),
				'step'=>0.1,
				'after' => __( 'second(s)', 'builder-typewriter' )
			),
			array(
				'id' => 'builder_typewriter_typer_direction',
				'type' => 'select',
				'label' => __( 'Highlight Direction', 'builder-typewriter' ),
				'options' => array(
					'rtl' => __( 'Right-to-left', 'builder-typewriter' ),
					'ltr' => __( 'Left-to-right', 'builder-typewriter' )
				)
			),
			array(
				'id' => 'add_css_text',
				'type' => 'custom_css'
			),
			array( 'type' => 'custom_css_id' )
		);
	}

	public function get_default_settings()
	{
		return array(
			'builder_typewriter_tag' => 'h3',
			'builder_typewriter_text_before' => __( 'This is', 'builder-typewriter' ),
			'builder_typewriter_text_after' => __( 'addon', 'builder-typewriter' ),
			'builder_typewriter_type_speed' => '150',
			'builder_typewriter_highlight_speed' => '50',
			'builder_typewriter_terms' => array(
				array(
					'builder_typewriter_term' => __( 'Typewriter', 'builder-typewriter' )
				)
			),
			'span_background_color' => '#ffff00'
		);
	}

	public function get_styling()
	{
		/*START temp solution when the addon is new,the FW is old 09.03.19*/
		if ( version_compare( THEMIFY_VERSION, '4.5', '<' ) ) {
			return array();
		}
		$general = array(
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image()
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( '', 'b_i', 'bg_c', 'b_r', 'b_p', 'h' )
						)
					)
				) )
			) ),// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( array( '', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ) ),
							self::get_color( array( '', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ), 'f_c' ),
							self::get_font_size( array( ' ', ' p', '  h1', '  h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ) ),
							self::get_line_height( array( ' ', ' p', '  h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ) ),
							self::get_text_align(),
							self::get_text_shadow( array( '', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ) ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( array( ':hover', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6' ), 'f_f_h' ),
							self::get_color( array( ':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6' ), 'f_c_h' ),
							self::get_font_size( '', 'f_s', '', 'h' ),
							self::get_line_height( '', 'l_h', 'h' ),
							self::get_text_align( '', 't_a', 'h' ),
							self::get_text_shadow( array( '', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6' ), 't_sh', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( '', 'p', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin()
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( '', 'm', 'h' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '', 'b', 'h' )
						)
					)
				) )
			) ),
			// Height & Min Height
			self::get_expand('ht', array(
					self::get_height(),
					self::get_min_height(),
					self::get_max_height()
				)
			),
			// Rounded Corners
			self::get_expand( 'r_c', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius( '', 'r_c', 'h' )
							)
						)
					) )
				)
			),
			// Shadow
			self::get_expand( 'sh', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow( '', 'sh', 'h' )
							)
						)
					) )
				)
			),
		);

		$typewriter = array(
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .typewriter-main-tag .typewriter-span *', 'span_background_color', __( 'Highlight Background', 'themify' ), 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' .typewriter-main-tag .typewriter-span span', 's_b_c', __( 'Highlight Background', 'themify' ), 'background-color', 'h' )
						)
					)
				) )
			) ),
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .typewriter-main-tag .typewriter-span', 'span_font_color', __( 'Highlight Text Color', 'themify' ) )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' .typewriter-main-tag .typewriter-span', 's_f_c', __( 'Highlight Text Color', 'themify' ), null, 'h' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .typewriter-main-tag .typewriter-span span', 'span_padding' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .typewriter-main-tag .typewriter-span span', 's_p', 'h' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .typewriter-main-tag .typewriter-span span', 'span_border' ) )
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .typewriter-main-tag .typewriter-span span', 's_b', 'h' )
						)
					)
				) )
			) )
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'm_t' => array(
					'options' => $this->module_title_custom_style()
				),
				't' => array(
					'label' => __( 'Typewriter', 'builder-typewriter' ),
					'options' => $typewriter
				)
			)
		);
	}

	protected function _visual_template()
	{
		$module_args = self::get_module_args();
		?>
        <#
        var typewriterTerms = { targets: [] };
        if( data.builder_typewriter_terms ) {
	    for(var i in data.builder_typewriter_terms){
		if(data.builder_typewriter_terms[i]){
		    typewriterTerms.targets.push( data.builder_typewriter_terms[i].builder_typewriter_term );
		}
	    }
        }
        typewriterTerms = JSON.stringify( typewriterTerms );
        #>

        <div class="module module-<?php echo $this->slug; ?> {{ data.add_css_text }}">
            <# if( data.mod_title_typewriter ) { #>
			<?php echo $module_args['before_title']; ?>
            {{{ data.mod_title_typewriter }}}
			<?php echo $module_args['after_title']; ?>
            <# } #>

			<?php do_action( 'themify_builder_before_template_content_render' ) ?>

            <{{{ data.builder_typewriter_tag }}} class="typewriter-main-tag">
            <span class="typewritter-text-before">{{{ data.builder_typewriter_text_before }}}</span>
            <span class="typewriter-span"
                  data-typer-targets='{{ typewriterTerms }}'
                  data-typer-highlight-speed="{{ data.builder_typewriter_highlight_speed }}"
                  data-typer-type-speed="{{ data.builder_typewriter_type_speed }}"
                  data-typer-clear-delay="{{ data.builder_typewriter_clear_delay }}"
                  data-typer-type-delay="{{ data.builder_typewriter_type_delay }}"
                  data-typer-interval="{{ data.builder_typewriter_typer_interval }}"
                  data-typer-direction="{{ data.builder_typewriter_typer_direction }}">
	    </span>
            <span class="typewritter-text-after">{{{ data.builder_typewriter_text_after }}}</span>
        </{{{ data.builder_typewriter_tag }}}>

		<?php do_action( 'themify_builder_after_template_content_render' ) ?>
        </div>
		<?php
	}

}

Themify_Builder_Model::register_module( 'TB_Typewriter' );
