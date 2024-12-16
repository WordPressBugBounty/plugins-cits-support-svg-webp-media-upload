<?php
if (!defined('ABSPATH')) exit;

define('CITS_CUSTOM_FONTS_PATH', plugin_dir_path(__FILE__));
define('CITS_CUSTOM_FONTS_URL', plugin_dir_url(__FILE__));

// Require the centralized support code file
require_once CITS_CUSTOM_FONTS_PATH . '/support-all.php';

// Enqueue Admin Scripts and Styles
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_cits-custom-fonts') {
        wp_enqueue_media();
        wp_enqueue_script(
            'cits-custom-fonts',
            CITS_CUSTOM_FONTS_URL . 'js/cits-admin.js',
            ['jquery'],
            filemtime(CITS_CUSTOM_FONTS_PATH . 'js/cits-admin.js'),
            true
        );
        wp_enqueue_style(
            'cits-custom-fonts',
            CITS_CUSTOM_FONTS_URL . 'css/cits-admin.css',
            [],
            filemtime(CITS_CUSTOM_FONTS_PATH . 'css/cits-admin.css')
        );
    }
});

// Admin Menu
add_action('admin_menu', function () {
    add_menu_page('CITS Custom Fonts', 'CITS Custom Fonts', 'manage_options', 'cits-custom-fonts', 'cits_custom_fonts_page', CITS_CUSTOM_FONTS_URL . 'images/logo.svg', 25);
});

// Main Admin Page with Tabs
function cits_custom_fonts_page() {
    echo '<div class="wrap">';
    echo '<h1>CITS Custom Fonts</h1>';
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=cits-custom-fonts&tab=upload" class="nav-tab ' . esc_attr(cits_active_tab('upload')) . '">Upload Font</a>';
    echo '<a href="?page=cits-custom-fonts&tab=assign" class="nav-tab ' . esc_attr(cits_active_tab('assign')) . '">Assign Font</a>';
    echo '<a href="?page=cits-custom-fonts&tab=settings" class="nav-tab ' . esc_attr(cits_active_tab('settings')) . '">Settings</a>';
    echo '<a href="?page=cits-custom-fonts&tab=support" class="nav-tab ' . esc_attr(cits_active_tab('support')) . '">Support & Instructions</a>';
    echo '</h2>';

    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'upload';

    if ($tab === 'assign') {
        cits_assign_fonts_tab();
    } elseif ($tab === 'settings') {
        cits_settings_tab();
    } elseif ($tab === 'support') {
        cits_support_tab();
    } else {
        cits_upload_fonts_tab();
    }

    echo '</div>';
}



// Helper to Highlight Active Tab
function cits_active_tab($current) {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'upload';
    return $tab === $current ? 'nav-tab-active' : '';
}

/* ================================
   Tab 1: Upload Fonts
================================ */
function cits_upload_fonts_tab() {
    // Handle Form Submission for Font Upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cits_fonts_nonce']) && wp_verify_nonce($_POST['cits_fonts_nonce'], 'cits_save_fonts_action')) {
        if (isset($_POST['font_name']) && !empty($_POST['font_name'])) {
            cits_handle_font_upload();
        }
    }

    // Handle Deletion of Font
    if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'cits_delete_font_action')) {
        $fonts = get_option('cits_custom_fonts', []);
        $key = sanitize_text_field(wp_unslash($_GET['delete']));

        if (isset($fonts[$key])) {
            unset($fonts[$key]);
            update_option('cits_custom_fonts', $fonts);
            cits_generate_fonts_css();
            echo '<div class="notice notice-success"><p>Font deleted successfully.</p></div>';
        }
    }

    $fonts = get_option('cits_custom_fonts', []);

    // Add Custom Font Button
    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;margin-top: 15px;">
            <h2 style="margin: 0;">Uploaded Fonts</h2>
            <button id="add-custom-font-btn" class="button button-primary">Add Custom Font</button>
          </div>';

    // Hidden Upload Font Form
    echo '<div id="custom-font-form" style="display: none; margin-top: 20px;margin-bottom: 20px; border: 1px solid #ccc; padding: 20px; background: #f9f9f9; border-radius: 5px;">
    <h2 style="margin: 0;">Add New Custom Font</h2>
            <form method="post">
                <table class="form-table">
                    <tr><th>Font File</th><td>
                        <input type="text" name="font_file_url" id="font_file_url" class="regular-text" required>
                        <button type="button" id="select_font_button" class="button">Select Font</button>
                    </td></tr>
                    <tr><th>Font Family Name</th><td>
                        <input type="text" name="font_name" id="font_name" class="regular-text" required>
                    </td></tr>
                    <tr><th>Font Weight</th><td>
                        <select name="font_weight">
                            <option value="100">100 - Thin</option>
                            <option value="200">200 - Extra Light</option>
                            <option value="300">300 - Light</option>
                            <option value="400" selected>400 - Normal</option>
                            <option value="500">500 - Medium</option>
                            <option value="600">600 - Semi-Bold</option>
                            <option value="700">700 - Bold</option>
                            <option value="800">800 - Extra Bold</option>
                            <option value="900">900 - Black</option>
                        </select>
                    </td></tr>
                    <tr><th>Font Style</th><td>
                        <select name="font_style">
                            <option value="normal" selected>Normal</option>
                            <option value="italic">Italic</option>
                            <option value="oblique">Oblique</option>
                        </select>
                    </td></tr>
                </table>';

    // Add Nonce Field
    wp_nonce_field('cits_save_fonts_action', 'cits_fonts_nonce');

    echo '<p><input type="submit" class="button-primary" value="Upload Font"></p>';
    echo '</form></div>';

    // Font List with Preview
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Font Family</th><th>Weight</th><th>Style</th><th>Preview</th><th>Actions</th></tr></thead><tbody>';
    foreach ($fonts as $key => $font) {
        echo '<tr>';
        echo '<td>' . esc_html($font['name']) . '</td>';
        echo '<td>' . esc_html($font['weight']) . '</td>';
        echo '<td>' . esc_html($font['style']) . '</td>';
        echo '<td style="font-family:' . esc_attr($font['name']) . '; font-weight:' . esc_attr($font['weight']) . '; font-style:' . esc_attr($font['style']) . '; font-size:18px;">
                The quick brown fox jumps over the lazy dog
              </td>';
        echo '<td>
                <a href="' . esc_url(add_query_arg(['page' => 'cits-custom-fonts', 'delete' => $key, '_wpnonce' => wp_create_nonce('cits_delete_font_action')], admin_url('admin.php'))) . '" class="button button-danger">Delete</a>
              </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function cits_handle_font_upload() {
    if (isset($_POST['cits_fonts_nonce']) && wp_verify_nonce($_POST['cits_fonts_nonce'], 'cits_save_fonts_action')) {
        // Validate and sanitize input
        $font_name = isset($_POST['font_name']) ? sanitize_text_field(wp_unslash($_POST['font_name'])) : '';
        $font_file_url = isset($_POST['font_file_url']) ? esc_url_raw(wp_unslash($_POST['font_file_url'])) : '';
        $font_weight = isset($_POST['font_weight']) ? sanitize_text_field(wp_unslash($_POST['font_weight'])) : '';
        $font_style = isset($_POST['font_style']) ? sanitize_text_field(wp_unslash($_POST['font_style'])) : '';

        if (!empty($font_name) && !empty($font_file_url)) {
            $fonts = get_option('cits_custom_fonts', []);
            $fonts[] = [
                'name' => $font_name,
                'file' => $font_file_url,
                'weight' => $font_weight,
                'style' => $font_style,
            ];
            update_option('cits_custom_fonts', $fonts);
            cits_generate_fonts_css();
            echo '<div class="notice notice-success"><p>Font uploaded successfully!</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Invalid request. Please try again.</p></div>';
    }
}

/* ================================
   Tab 2: Assign Fonts
================================ */
function cits_assign_fonts_tab() {
    
    // Handle Assign Font Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cits_assign_fonts_nonce'])) {
        if (wp_verify_nonce(wp_unslash($_POST['cits_assign_fonts_nonce']), 'cits_assign_fonts_action')) {
            // Sanitize and validate form inputs
            $selected_font = isset($_POST['selected_font']) ? sanitize_text_field(wp_unslash($_POST['selected_font'])) : '';
            $elements = isset($_POST['elements']) ? array_map('sanitize_text_field', wp_unslash($_POST['elements'])) : [];
            $custom_selectors = isset($_POST['custom_selectors']) ? sanitize_textarea_field(wp_unslash($_POST['custom_selectors'])) : '';

            if (!empty($selected_font)) {
                // Retrieve current assignments
                $assignments = get_option('cits_font_assignments', []);

                // Add new assignment
                $assignments[] = [
                    'font' => $selected_font,
                    'elements' => $elements,
                    'custom_selectors' => $custom_selectors,
                ];

                // Save back to options
                update_option('cits_font_assignments', $assignments);

                // Regenerate CSS
                cits_generate_fonts_css();

                // Success message
                echo '<div class="notice notice-success"><p>Font successfully assigned!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Please select a font to assign.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Invalid request. Please try again.</p></div>';
        }
    }

    
    
    $fonts = get_option('cits_custom_fonts', []);
    $assignments = get_option('cits_font_assignments', []);

    // Add Assign Font Button
    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;margin-top: 15px;">
            <h2 style="margin: 0;">Assigned Fonts</h2>
            <button id="show-assign-font-form" class="button button-primary">Assign Font</button>
          </div>';

    // Hidden Assign Font Form
    echo '<div id="assign-font-form" style="display: none; margin-top: 20px;margin-bottom: 20px; border: 1px solid #ccc; padding: 20px; background: #f9f9f9; border-radius: 5px;">
            <form method="post">';
    wp_nonce_field('cits_assign_fonts_action', 'cits_assign_fonts_nonce');

    echo '<table class="form-table">';
    echo '<tr><th>Select Font Family</th><td><select name="selected_font" required>';
    echo '<option value="">- Select -</option>';
    foreach ($fonts as $font) {
        echo '<option value="' . esc_attr($font['name']) . '">' . esc_html($font['name']) . '</option>';
    }
    echo '</select></td></tr>';

    // Predefined Checkboxes for HTML Elements
    echo '<tr><th>Select Elements to Assign</th><td>';
    echo '<div style="display: flex; gap: 30px;">';

    // Headings and Titles
    echo '<div><strong>Headings And Titles</strong><br>';
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $heading) {
        echo '<label><input type="checkbox" name="elements[]" value="' . esc_attr($heading) . '"> ' . esc_html('Headline ' . strtoupper($heading)) . '</label><br>';
    }
    echo '<label><input type="checkbox" name="elements[]" value="body.page .entry-title"> ' . esc_html('Page Title') . '</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="body.category .entry-title"> ' . esc_html('Category Title') . '</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="body.single-post .entry-title"> ' . esc_html('Post Title') . '</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value=".widget-title"> ' . esc_html('Widget Title') . '</label>';
    echo '</div>';

    // Body Content
    echo '<div><strong>Body</strong><br>';
    echo '<label><input type="checkbox" name="elements[]" value="body"> Body (body tag)</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="p"> Paragraphs (p tags)</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="a"> Hyperlinks (a tag)</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="strong"> Bold (strong tag)</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value="em"> Italic (em tag)</label>';
    echo '</div>';

    // Site Identity
    echo '<div><strong>Site Identity</strong><br>';
    echo '<label><input type="checkbox" name="elements[]" value=".site-title"> Site Title</label><br>';
    echo '<label><input type="checkbox" name="elements[]" value=".site-description"> Site Description</label>';
    echo '</div>';

    echo '</div></td></tr>';

    // Custom Selectors
    echo '<tr><th>Custom CSS Selectors</th><td>';
    echo '<textarea name="custom_selectors" rows="4" cols="50" placeholder="#example, .my-class"></textarea>';
    echo '</td></tr>';
    echo '</table>';
    echo '<p><input type="submit" class="button-primary" value="Assign Font"></p>';
    echo '</form>';
    echo '</div>';

    // Display Assigned Fonts Table 
    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>Font Family</th><th>Elements</th><th>Custom Selectors</th><th>Actions</th></tr></thead><tbody>';
    foreach ($assignments as $key => $assignment) {
        echo '<tr>';
        echo '<td>' . esc_html($assignment['font']) . '</td>';
        echo '<td>';
        if (isset($assignment['elements']) && is_array($assignment['elements'])) {
            echo implode(', ', array_map('esc_html', $assignment['elements']));
        } else {
            echo 'No elements assigned';
        }
        echo '</td>';
        echo '<td>' . nl2br(esc_html($assignment['custom_selectors'])) . '</td>';
        echo '<td><a href="?page=cits-custom-fonts&tab=assign&delete_assignment=' . esc_attr($key) . '" class="button button-danger">Delete</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
 
}

if (isset($_GET['delete_assignment']) && !headers_sent()) {
    $assignments = get_option('cits_font_assignments', []);
    $key = sanitize_text_field($_GET['delete_assignment']);
    
    if (isset($assignments[$key])) {
        unset($assignments[$key]);
        update_option('cits_font_assignments', $assignments);
        cits_generate_fonts_css(); // Regenerate CSS

        // Redirect after successful deletion
        echo '<script>window.location = "' . esc_url(admin_url('admin.php?page=cits-custom-fonts&tab=assign')) . '";</script>';
        exit;
    }
} elseif (isset($_GET['delete_assignment'])) {
    echo '<div class="notice notice-error"><p>Headers already sent. Please refresh the page to see changes.</p></div>';
}
 
function cits_generate_fonts_css() {
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    // Retrieve fonts and assignments
    $fonts = get_option('cits_custom_fonts', []);
    $assignments = get_option('cits_font_assignments', []);

    $css = '';

    // Generate @font-face rules
    foreach ($fonts as $font) {
        $css .= "@font-face {
            font-family: '" . esc_attr($font['name']) . "';
            src: url('" . esc_url($font['file']) . "') format('woff2');
            font-weight: " . esc_attr($font['weight']) . ";
            font-style: " . esc_attr($font['style']) . ";
        }\n";
    }

    // Generate CSS rules for assigned fonts
    foreach ($assignments as $assignment) {
        $font_name = esc_attr($assignment['font']);
        $elements = isset($assignment['elements']) ? $assignment['elements'] : [];
        $custom_selectors = isset($assignment['custom_selectors']) ? esc_textarea($assignment['custom_selectors']) : '';

        // Predefined elements
        foreach ($elements as $element) {
            $css .= "$element {
                font-family: '$font_name', sans-serif;
            }\n";
        }

        // Custom CSS selectors
        if (!empty($custom_selectors)) {
            $selectors = explode("\n", $custom_selectors);
            foreach ($selectors as $selector) {
                $selector = trim($selector);
                if (!empty($selector)) {
                    $css .= "$selector {
                        font-family: '$font_name', sans-serif;
                    }\n";
                }
            }
        }
    }

    // Save CSS to file
    $css_file = CITS_CUSTOM_FONTS_PATH . 'cits-custom-fonts.css';
    $wp_filesystem->put_contents($css_file, $css, FS_CHMOD_FILE);
}

function cits_enqueue_custom_fonts_css() {
    $css_file_url = CITS_CUSTOM_FONTS_URL . 'cits-custom-fonts.css';

    // Regenerate the CSS to ensure updates
    cits_generate_fonts_css();

    // Enqueue the CSS file
    wp_enqueue_style('cits-custom-fonts', $css_file_url, [], filemtime(CITS_CUSTOM_FONTS_PATH . 'cits-custom-fonts.css'));
}
add_action('wp_enqueue_scripts', 'cits_enqueue_custom_fonts_css');

/* ================================
   Tab 3: Settings
================================ */
function cits_settings_tab() {
    // Load the existing options or set default values
    $builder_options = get_option('cits_builder_support_options', [
        'elementor' => 1, // Default: Elementor enabled
    ]);

    $builders = [
        'wp_editor'   => 'WordPress Editor',
        'elementor'   => 'Elementor',
        'wpbakery'    => 'WPBakery Page Builder',
        'divi'        => 'Divi Builder',
        'redux'       => 'Redux Framework',
        'siteorigin'  => 'SiteOrigin Builder',
        'beaver'      => 'Beaver Builder',
    ];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_options = [];
        foreach ($builders as $key => $label) {
            $new_options[$key] = isset($_POST[$key]) ? 1 : 0;
        }
        update_option('cits_builder_support_options', $new_options);
        echo '<div class="notice notice-success"><p>Settings updated successfully!</p></div>';
    }

    // Display the settings form
    echo '<form method="post">';
    echo '<h2>Supported Theme/Page Builders</h2>';
    echo '<table class="form-table">';
    foreach ($builders as $key => $label) {
        echo '<tr>';
        echo '<th scope="row">' . esc_html($label) . '</th>';
        echo '<td><input type="checkbox" name="' . esc_attr($key) . '" ' . checked(1, $builder_options[$key] ?? 0, false) . '></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<p><input type="submit" class="button-primary" value="Save Settings"></p>';
    echo '</form>';
}
/* ================================
   Tab 4: Instructions - Support 
================================ */
function cits_support_tab() {
    echo '<h2>Instructions</h2>';
    echo '<p>' . esc_html__('Follow these steps to use the CITS Custom Fonts plugin:', 'cits-support-svg-webp-media-upload') . '</p>';
    echo '<ol>';
    echo '<li>' . esc_html__('Upload Fonts: Go to the "Upload Font" tab and upload your custom fonts.', 'cits-support-svg-webp-media-upload') . '</li>';
    echo '<li>' . esc_html__('Assign Fonts: Use the "Assign Font" tab to assign uploaded fonts to specific elements.', 'cits-support-svg-webp-media-upload') . '</li>';
    echo '<li>' . esc_html__('Settings: Enable or disable font support for different page builders in the "Settings" tab.', 'cits-support-svg-webp-media-upload') . '</li>';
    echo '</ol>';

    echo '<h2>Support</h2>';
    echo '<ul>';
    echo '<li>Email: <a href="mailto:hello@coderitsolution.com">' . esc_html__('hello@coderitsolution.com', 'cits-support-svg-webp-media-upload') . '</a></li>';
    echo '<li>WhatsApp: <a href="' . esc_url('https://wa.me/8801751331330') . '" target="_blank">' . esc_html__('Chat on WhatsApp', 'cits-support-svg-webp-media-upload') . '</a></li>';
    echo '</ul>';

    echo '<h2>' . esc_html__('Support the Plugin', 'cits-support-svg-webp-media-upload') . '</h2>';
    echo '<p>' . esc_html__('If you find this plugin useful, please consider buying me a coffee to support further development.', 'cits-support-svg-webp-media-upload') . '</p>';
    echo '<a href="' . esc_url('https://ko-fi.com/coderitsolution') . '" class="button button-primary" target="_blank">' . esc_html__('Buy Me a Coffee', 'cits-support-svg-webp-media-upload') . '</a>';
}

function cits_load_builder_support() {
    $builder_options = get_option('cits_builder_support_options', []);

    if (!empty($builder_options['wp_editor'])) {
        cits_wp_editor_support();
    }
    if (!empty($builder_options['elementor'])) {
        add_action('elementor/controls/controls_registered', 'cits_elementor_support', 10, 1);
    }
    if (!empty($builder_options['divi'])) {
        add_filter('et_websafe_fonts', 'cits_divi_support', 10, 2);
    }
    if (!empty($builder_options['siteorigin'])) {
        add_filter('siteorigin_widgets_font_families', 'cits_siteorigin_support');
    }
    if (!empty($builder_options['redux'])) {
        add_filter('redux/option_name/field/typography/custom_fonts', 'cits_redux_support');
    }
    if (!empty($builder_options['beaver'])) {
        add_filter('fl_theme_system_fonts', 'cits_beaver_builder_support');
    }
    if (!empty($builder_options['wpbakery'])) {
        add_filter('vc_google_fonts_get_fonts_filter', 'cits_wpbakery_support');
    }
}
add_action('plugins_loaded', 'cits_load_builder_support');
