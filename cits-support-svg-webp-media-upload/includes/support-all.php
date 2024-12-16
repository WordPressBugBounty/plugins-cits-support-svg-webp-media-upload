<?php
if (!defined('ABSPATH')) exit;

// Retrieve Fonts Helper
function cits_get_font_families() {
    $fonts = get_option('cits_custom_fonts', []);
    $font_list = [];
    foreach ($fonts as $font) {
        $font_list[$font['name']] = $font['name'];
    }
    return $font_list;
}

// WordPress Editor Support
function cits_wp_editor_support() {
    add_filter('mce_buttons_2', function ($options) {
        array_unshift($options, 'fontsizeselect', 'fontselect');
        return $options;
    });

    add_filter('tiny_mce_before_init', function ($init_array) {
        $fonts = cits_get_font_families();
        $font_formats = '';
        foreach ($fonts as $font_name) {
            $font_formats .= $font_name . '=' . $font_name . ';';
        }
        $init_array['font_formats'] = $font_formats . 'Andale Mono=Andale Mono, Times;Arial=Arial, Helvetica, sans-serif;Arial Black=Arial Black, Avant Garde;Book Antiqua=Book Antiqua, Palatino;Comic Sans MS=Comic Sans MS, sans-serif;Courier New=Courier New, Courier;Georgia=Georgia, Palatino;Helvetica=Helvetica;Impact=Impact, Chicago;Symbol=Symbol;Tahoma=Tahoma, Arial, Helvetica, sans-serif;Terminal=Terminal, Monaco;Times New Roman=Times New Roman, Times;Trebuchet MS=Trebuchet MS, Geneva;Verdana=Verdana, Geneva;Webdings=Webdings;Wingdings=Wingdings';
        return $init_array;
    });
}

// Elementor Support
function cits_elementor_support($controls_registry) {
    $fonts = cits_get_font_families();
    $elementor_fonts = ['CITS Custom Fonts' => []];
    foreach ($fonts as $font_name) {
        $elementor_fonts[$font_name] = 'system';
    }
    $existing_fonts = $controls_registry->get_control('font')->get_settings('options');
    $new_fonts = array_merge($elementor_fonts, $existing_fonts);
    $controls_registry->get_control('font')->set_settings('options', $new_fonts);
}
add_action('elementor/controls/controls_registered', 'cits_elementor_support', 10, 1);

// WPBakery Support
function cits_wpbakery_support($fonts) {
    $custom_fonts = cits_get_font_families();
    $fonts_uaf = [];
    foreach ($custom_fonts as $font_name) {
        $fonts_uaf[] = (object)[
            'font_family' => $font_name,
            'font_types'  => '400 regular:400:normal',
            'font_styles' => 'regular'
        ];
    }
    return array_merge($fonts_uaf, $fonts);
}
add_filter('vc_google_fonts_get_fonts_filter', 'cits_wpbakery_support');

// Divi Support
function cits_divi_support($fonts) {
    $custom_fonts = cits_get_font_families();
    foreach ($custom_fonts as $font_name) {
        $fonts[$font_name] = ['styles' => '400', 'type' => 'serif'];
    }
    return $fonts;
}
add_filter('et_websafe_fonts', 'cits_divi_support', 10, 2);

// Redux Framework Support
add_filter('redux/option_name/field/typography/custom_fonts', function ($fonts) {
    return array_merge(['CITS Custom Fonts' => cits_get_font_families()], $fonts);
});

// Other Theme/Page Builder Support
add_filter('siteorigin_widgets_font_families', function ($fonts) {
    return array_merge(cits_get_font_families(), $fonts);
});

add_filter('fl_theme_system_fonts', function ($fonts) {
    return array_merge(cits_get_font_families(), $fonts);
});