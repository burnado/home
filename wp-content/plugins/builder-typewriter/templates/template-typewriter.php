<?php
/* Exit if accessed directly */
defined('ABSPATH') or die('-1');

/**
 * Template Typewriter
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):
    $fields_default = array(
        'mod_title_typewriter' => '',
        'builder_typewriter_tag' => 'p',
        'builder_typewriter_text_before' => '',
        'builder_typewriter_text_after' => '',
        'builder_typewriter_terms' => array(),
        'builder_typewriter_highlight_speed' => 50,
        'builder_typewriter_type_speed' => 150,
        'builder_typewriter_clear_delay' => 1.5,
        'builder_typewriter_type_delay' => 0.2,
        'builder_typewriter_typer_interval' => 1.5,
        'builder_typewriter_typer_direction' => 'rtl',
        'add_css_text' => '',
        'animation_effect' => '',
        'span_padding'=>0,
        'span_padding_unit' => 'px',
        'span_padding_top' => 0,
        'span_padding_top_unit' => 'px',
        'span_padding_right' => 0,
        'span_padding_right_unit' => 'px',
        'span_padding_bottom' => 0,
        'span_padding_bottom_unit' => 'px',
        'span_padding_left' => 0,
        'span_padding_left_unit' => 'px',
        'span_border_width'=>0,
        'span_border_style'=>'style',
        'span_border_top_width' => 0,
        'span_border_top_style'=>'style',
        'span_border_right_width' => 0,
        'span_border_right_style'=>'style',
        'span_border_bottom_width' => 0,
        'span_border_bottom_style'=>'style',
        'span_border_left_width' => 0,
        'span_border_left_style'=>'style'
    );

    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $container_class = apply_filters('themify_builder_module_classes', array('module','module-' . $args['mod_name'],$args['module_ID'],$fields_args['add_css_text'],self::parse_animation_effect($fields_args['animation_effect'], $fields_args)), $args['mod_name'], $args['module_ID'], $fields_args);
    if(!empty($args['element_id'])){
	$container_class[] = 'tb_'.$args['element_id'];
    }
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $is_not_empty = !empty($fields_args['builder_typewriter_terms']);
    $typewriter_terms = array();
    if($is_not_empty===true){
		$typewriter_first_term = esc_html($fields_args['builder_typewriter_terms'][0]['builder_typewriter_term']);
        foreach ($fields_args['builder_typewriter_terms'] as $term) {
            $typewriter_terms[] = esc_attr($term['builder_typewriter_term']);
        }
        $typewriter_terms = json_encode(array( 'targets' => $typewriter_terms ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $args['module_ID'],
        'class' => implode( ' ', $container_class),
            ), $fields_args, $args['mod_name'], $args['module_ID']);
    ?>
    <!-- module typewriter -->
    <div <?php echo self::get_element_attributes($container_props); ?>>
    <?php
    // DYNAMIC STYLE
    $container_props=$container_class=null;
    if($is_not_empty===true){
        $margin_top = $fields_args['span_border_top_style']!=='none'?(int) $fields_args['span_border_top_width']:0;
        if ($fields_args['span_padding_top_unit'] === 'px') {
            $margin_top += (int) $fields_args['span_padding_top'];
        }

        $margin_right = $fields_args['span_border_right_style']!=='none'?(int) $fields_args['span_border_right_width']:0;
        if ($fields_args['span_padding_right_unit'] === 'px') {
            $margin_right += (int) $fields_args['span_padding_right'];
        }

        $margin_bottom = $fields_args['span_border_bottom_style']!=='none'?(int) $fields_args['span_border_bottom_width']:0;
        if ($fields_args['span_padding_bottom_unit'] === 'px') {
            $margin_bottom += (int) $fields_args['span_padding_bottom'];
        }

        $margin_left = $fields_args['span_border_left_style']!=='none'?(int) $fields_args['span_border_left_width']:0;
        if ($fields_args['span_padding_left_unit'] === 'px') {
            $margin_left += (int)$fields_args['span_padding_left'] ;
        }
        $style = '';


        if ($margin_top > 0 || $margin_right > 0 || $margin_bottom > 0 || $margin_left > 0) {

            if ($margin_top > 0) {
                $style= "margin-top: -{$margin_top}px;\n";
            }
            if ($margin_right > 0) {
                $style .= "margin-right: -{$margin_right}px;\n";
            }
            if ($margin_bottom > 0) {
                $style .= "margin-bottom: -{$margin_bottom}px;\n";
            }
            if ($margin_left > 0) {
                $style .= "margin-left: -{$margin_left}px;\n";
            }
        }

        if ($style!=='') {
            $output = "<style type='text/css'>\n";
            $output .= ".{$args['module_ID']} .typewriter-main-tag .typewriter-span span{";
            $output .=$style;
            $output .= '}</style>';
            echo $output;
        }
    }
    ?>

        <?php if ($fields_args['mod_title_typewriter'] !== '') : ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_typewriter'], $fields_args). $fields_args['after_title'] ?>
        <?php endif ?>

        <?php do_action('themify_builder_before_template_content_render') ?>

        <?php if($is_not_empty===true):?>
            <?php $builder_typewriter_tag = esc_attr($fields_args['builder_typewriter_tag']);?>
            <<?php echo $builder_typewriter_tag?> class="typewriter-main-tag">
                <span class="typewritter-text-before"><?php echo trim(wp_kses_post($fields_args['builder_typewriter_text_before'])) . ' ';?></span>
                <span class="typewriter-span" 
                    data-typer-targets='<?php echo $typewriter_terms?>'
                    data-typer-highlight-speed="<?php echo $fields_args['builder_typewriter_highlight_speed']?>"
                    data-typer-type-speed="<?php echo $fields_args['builder_typewriter_type_speed']?>"
                    data-typer-clear-delay="<?php echo (float)($fields_args['builder_typewriter_clear_delay'])?>"
                    data-typer-type-delay="<?php echo (float)($fields_args['builder_typewriter_type_delay'])?>"
                    data-typer-interval="<?php echo (float)($fields_args['builder_typewriter_typer_interval'])?>"
		    data-typer-direction="<?php echo $fields_args['builder_typewriter_typer_direction']; ?>"
                    >
				<?php echo $typewriter_first_term; ?>
                </span>
                <span class="typewritter-text-after"><?php echo ' ' .trim(wp_kses_post($fields_args['builder_typewriter_text_after']));?></span>
            </<?php echo $builder_typewriter_tag;?>>
        <?php endif;?>
        <?php do_action('themify_builder_after_template_content_render') ?>
    </div>
    <!-- /module typewriter -->
    <?php endif; ?>
    <?php TFCache::end_cache(); ?>
