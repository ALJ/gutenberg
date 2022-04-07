<?php
/**
 * Typography block support flag.
 *
 * @package gutenberg
 */

/**
 * Registers the style and typography block attributes for block types that support it.
 *
 * @param WP_Block_Type $block_type Block Type.
 */
function gutenberg_register_typography_support( $block_type ) {
	if ( ! property_exists( $block_type, 'supports' ) ) {
		return;
	}

	$typography_supports = _wp_array_get( $block_type->supports, array( 'typography' ), false );
	if ( ! $typography_supports ) {
		return;
	}

	$has_font_family_support     = _wp_array_get( $typography_supports, array( '__experimentalFontFamily' ), false );
	$has_font_size_support       = _wp_array_get( $typography_supports, array( 'fontSize' ), false );
	$has_font_style_support      = _wp_array_get( $typography_supports, array( '__experimentalFontStyle' ), false );
	$has_font_weight_support     = _wp_array_get( $typography_supports, array( '__experimentalFontWeight' ), false );
	$has_letter_spacing_support  = _wp_array_get( $typography_supports, array( '__experimentalLetterSpacing' ), false );
	$has_line_height_support     = _wp_array_get( $typography_supports, array( 'lineHeight' ), false );
	$has_text_decoration_support = _wp_array_get( $typography_supports, array( '__experimentalTextDecoration' ), false );
	$has_text_transform_support  = _wp_array_get( $typography_supports, array( '__experimentalTextTransform' ), false );

	$has_typography_support = $has_font_family_support
		|| $has_font_size_support
		|| $has_font_style_support
		|| $has_font_weight_support
		|| $has_letter_spacing_support
		|| $has_line_height_support
		|| $has_text_decoration_support
		|| $has_text_transform_support;

	if ( ! $block_type->attributes ) {
		$block_type->attributes = array();
	}

	if ( $has_typography_support && ! array_key_exists( 'style', $block_type->attributes ) ) {
		$block_type->attributes['style'] = array(
			'type' => 'object',
		);
	}

	if ( $has_font_size_support && ! array_key_exists( 'fontSize', $block_type->attributes ) ) {
		$block_type->attributes['fontSize'] = array(
			'type' => 'string',
		);
	}
}

/**
 * Add CSS classes and inline styles for typography features such as font sizes
 * to the incoming attributes array. This will be applied to the block markup in
 * the front-end.
 *
 * @param  WP_Block_Type $block_type       Block type.
 * @param  array         $block_attributes Block attributes.
 *
 * @return array Typography CSS classes and inline styles.
 */
function gutenberg_apply_typography_support( $block_type, $block_attributes ) {
	if ( ! property_exists( $block_type, 'supports' ) ) {
		return array();
	}

	$typography_supports = _wp_array_get( $block_type->supports, array( 'typography' ), false );
	if ( ! $typography_supports ) {
		return array();
	}

	if ( gutenberg_should_skip_block_supports_serialization( $block_type, 'typography' ) ) {
		return array();
	}

	$attributes = array();
	$classes    = array();
	$styles     = array();

	$has_font_family_support     = _wp_array_get( $typography_supports, array( '__experimentalFontFamily' ), false );
	$has_font_size_support       = _wp_array_get( $typography_supports, array( 'fontSize' ), false );
	$has_font_style_support      = _wp_array_get( $typography_supports, array( '__experimentalFontStyle' ), false );
	$has_font_weight_support     = _wp_array_get( $typography_supports, array( '__experimentalFontWeight' ), false );
	$has_letter_spacing_support  = _wp_array_get( $typography_supports, array( '__experimentalLetterSpacing' ), false );
	$has_line_height_support     = _wp_array_get( $typography_supports, array( 'lineHeight' ), false );
	$has_text_decoration_support = _wp_array_get( $typography_supports, array( '__experimentalTextDecoration' ), false );
	$has_text_transform_support  = _wp_array_get( $typography_supports, array( '__experimentalTextTransform' ), false );

	// Whether to skip individual block support features.
	$should_skip_font_size       = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'fontSize' );
	$should_skip_font_family     = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'fontFamily' );
	$should_skip_font_style      = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'fontStyle' );
	$should_skip_font_weight     = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'fontWeight' );
	$should_skip_line_height     = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'lineHeight' );
	$should_skip_text_decoration = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'textDecoration' );
	$should_skip_text_transform  = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'textTransform' );
	$should_skip_letter_spacing  = gutenberg_should_skip_block_supports_serialization( $block_type, 'typography', 'letterSpacing' );

	if ( $has_font_size_support && ! $should_skip_font_size ) {
		$has_named_font_size  = array_key_exists( 'fontSize', $block_attributes );
		$has_custom_font_size = isset( $block_attributes['style']['typography']['fontSize'] );

		if ( $has_named_font_size ) {
			$classes['fontSize'] = _wp_to_kebab_case( $block_attributes['fontSize'] );
		} elseif ( $has_custom_font_size ) {
			$styles['fontSize'] = $block_attributes['style']['typography']['fontSize'];
		}
	}

	if ( $has_font_family_support && ! $should_skip_font_family ) {
		$has_named_font_family  = array_key_exists( 'fontFamily', $block_attributes );
		$has_custom_font_family = isset( $block_attributes['style']['typography']['fontFamily'] );

		if ( $has_named_font_family ) {
			$classes['fontFamily'] = _wp_to_kebab_case( $block_attributes['fontFamily'] );
		} elseif ( $has_custom_font_family ) {
			$font_family_custom = $block_attributes['style']['typography']['fontFamily'];
			// Before using classes, the value was serialized as a CSS Custom Property.
			// We don't need to check for a preset when it lands in core.
			$font_family_value    = gutenberg_typography_get_preset_inline_style_value( $font_family_custom, 'font-family' );
			$styles['fontFamily'] = $font_family_value;
		}
	}

	if ( $has_font_style_support && ! $should_skip_font_style ) {
		$font_style       = _wp_array_get( $block_attributes, array( 'style', 'typography', 'fontStyle' ), null );
		$font_style_value = gutenberg_typography_get_preset_inline_style_value( $font_style, 'font-style' );
		if ( $font_style_value ) {
			$styles['fontStyle'] = $font_style_value;
		}
	}

	if ( $has_font_weight_support && ! $should_skip_font_weight ) {
		$font_weight       = _wp_array_get( $block_attributes, array( 'style', 'typography', 'fontWeight' ), null );
		$font_weight_value = gutenberg_typography_get_preset_inline_style_value( $font_weight, 'font-weight' );
		if ( $font_weight_value ) {
			$styles['fontWeight'] = $font_weight_value;
		}
	}

	if ( $has_line_height_support && ! $should_skip_line_height ) {
		$has_line_height = isset( $block_attributes['style']['typography']['lineHeight'] );
		if ( $has_line_height ) {
			$styles['lineHeight'] = $block_attributes['style']['typography']['lineHeight'];
		}
	}

	if ( $has_text_decoration_support && ! $should_skip_text_decoration ) {
		$text_decoration       = _wp_array_get( $block_attributes, array( 'style', 'typography', 'textDecoration' ), null );
		$text_decoration_value = gutenberg_typography_get_preset_inline_style_value( $text_decoration, 'text-decoration' );
		if ( $text_decoration_value ) {
			$styles['textDecoration'] = $text_decoration_value;
		}
	}

	if ( $has_text_transform_support && ! $should_skip_text_transform ) {
		$text_transform       = _wp_array_get( $block_attributes, array( 'style', 'typography', 'textTransform' ), null );
		$text_transform_value = gutenberg_typography_get_preset_inline_style_value( $text_transform, 'text-transform' );
		if ( $text_transform_value ) {
			$styles['textTransform'] = $text_transform_value;
		}
	}

	if ( $has_letter_spacing_support && ! $should_skip_letter_spacing ) {
		$letter_spacing       = _wp_array_get( $block_attributes, array( 'style', 'typography', 'letterSpacing' ), null );
		$letter_spacing_value = gutenberg_typography_get_preset_inline_style_value( $letter_spacing, 'letter-spacing' );
		if ( $letter_spacing_value ) {
			$styles['letterSpacing'] = $letter_spacing_value;
		}
	}

	$style_engine  = gutenberg_get_style_engine();
	$inline_styles = $style_engine->generate(
		array( 'typography' => $styles ),
		array(
			'inline' => true,
		)
	);

	$classnames = $style_engine->get_classnames(
		array( 'typography' => $classes ),
		array(
			'use_schema' => true,
		)
	);

	if ( ! empty( $classnames ) ) {
		$attributes['class'] = $classnames;
	}

	if ( ! empty( $inline_styles ) ) {
		$attributes['style'] = $inline_styles;
	}

	return $attributes;
}

/**
 * Generates an inline style value for a typography feature e.g. text decoration,
 * text transform, and font style.
 *
 * @param string $style_value    A raw style value for a single typography feature from a block's style attribute.
 * @param string $css_property   Slug for the CSS property the inline style sets.
 *
 * @return string?             A CSS inline style value.
 */
function gutenberg_typography_get_preset_inline_style_value( $style_value, $css_property ) {
	// If the style value is not a preset CSS variable go no further.
	if ( empty( $style_value ) || strpos( $style_value, "var:preset|{$css_property}|" ) === false ) {
		return $style_value;
	}

	// For backwards compatibility.
	// Presets were removed in https://github.com/WordPress/gutenberg/pull/27555.
	// We have a preset CSS variable as the style.
	// Get the style value from the string and return CSS style.
	$index_to_splice = strrpos( $style_value, '|' ) + 1;
	// @TODO
	// Font family requires the slugs to be converted to kebab case. Should this be optional in this method?
	// Let's test with some older blocks.
	$slug = _wp_to_kebab_case( substr( $style_value, $index_to_splice ) );

	// Return the actual CSS inline style value e.g. `var(--wp--preset--text-decoration--underline);`.
	return sprintf( 'var(--wp--preset--%s--%s);', $css_property, $slug );
}

// Register the block support.
WP_Block_Supports::get_instance()->register(
	'typography',
	array(
		'register_attribute' => 'gutenberg_register_typography_support',
		'apply'              => 'gutenberg_apply_typography_support',
	)
);
