<?php 
/*
Plugin Name: CITS Support SVG, WEBP, ICO Media and TTF,OTF File Upload
Plugin URI: https://coderitsolution.com
Author: Ashikur
Author URI: https://ashik.me
Description:  Enable Securely upload SVG, WEBP, ICO, TTF, OTF files and automatically sanitize media files to protect your website from XML/SVG vulnerabilities.
Tags: svg support, safe svg, webp support, ico support, favicon support, support webp, cits support svg, cits support webp, CITS Support svg, webp media upload, font file upload support, ttf upload, otf upload, eot upload, woff upload.
Version: 4.1
Requires at least: 5.0
Tested up to: 6.5.3
Requires PHP version: 7.0
License: GPL2
*/
define('CITS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CITS_PLUGIN_PATH', plugin_dir_path(__FILE__));
require_once __DIR__ . '/vendor/autoload.php';

class CITS_SUPPORT_SVG_WEBP_MEDIA {
    public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'cits_enqueue_admin_styles'));
		 
        add_filter('upload_mimes', array($this, 'cits_upload_media_mimes'));
        add_filter('file_is_displayable_image', array($this, 'cits_is_displayable_webp'), 10, 2);
        add_filter('wp_prepare_attachment_for_js', array($this, 'cits_response_for_svg'), 10, 3);
        add_filter('wp_check_filetype_and_ext', array($this, 'cits_check_types'), 10, 4);
        add_filter('wp_handle_upload_prefilter', array($this, 'cits_sanitize_svg'));  
		
		// Include the admin file
        include CITS_PLUGIN_PATH . 'admin/cits-admin.php';
    }
    
    function cits_upload_media_mimes($cits_mimes) {
        $cits_mimes['webp'] = 'image/webp'; 
        $cits_mimes['svg'] = 'image/svg+xml';
        $cits_mimes['svgz'] = 'image/svg+xml';
        $cits_mimes['ico'] = 'image/x-icon';
        $cits_mimes['ttf'] = 'application/x-font-ttf';
        $cits_mimes['otf'] = 'application/x-font-otf';
        $cits_mimes['eot'] = 'application/x-font-eot';
        $cits_mimes['woff'] = 'application/x-font-woff';
        $cits_mimes['woff2'] = 'application/x-font-woff2';
        return $cits_mimes;
    }

    function cits_is_displayable_webp($result, $path) {
        if ($result === false) {
            $image_types = array(IMAGETYPE_WEBP);
            $info = @getimagesize($path); 
            if (empty($info)) {
                $result = false;
            } elseif (!in_array($info[2], $image_types)) {
                $result = false;
            } else {
                $result = true;
            }
        }
        return $result;
    }

    function cits_check_types($checked, $file, $filename, $mimes) {
        if (!$checked['type']) { 
            $check_filetype = wp_check_filetype($filename, $mimes);
            $ext = $check_filetype['ext'];
            $type = $check_filetype['type'];
            $proper_filename = $filename; 
            if ($type && (0 === strpos($type, 'image/')) && ($ext !== 'svg' && $ext !== 'webp' && $ext !== 'ico')) {
                $ext = $type = false;
            }
            $checked = compact('ext', 'type', 'proper_filename');
        } 
        return $checked; 
    }

    function cits_response_for_svg($response, $attachment, $meta) { 
        if ($response['mime'] == 'image/svg+xml' && empty($response['sizes'])) { 
            $svg_path = get_attached_file($attachment->ID); 
            if (!file_exists($svg_path)) { 
                $svg_path = $response['url'];
            } 
            $dimensions = $this->cits_get_dimensions($svg_path); 
            $response['sizes'] = array(
                'full' => array(
                    'url' => $response['url'],
                    'width' => $dimensions->width,
                    'height' => $dimensions->height,
                    'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait'
                )
            ); 
        } 
        return $response; 
    } 

    function cits_get_dimensions($svg) {
        $svg = simplexml_load_file($svg);
        if ($svg === FALSE) { 
            $width = '0';
            $height = '0'; 
        } else { 
            $attributes = $svg->attributes();
            $width = (string) $attributes->width;
            $height = (string) $attributes->height;
        } 
        return (object) array('width' => $width, 'height' => $height);
    }

    function cits_sanitize_svg($file) {
        if ($file['type'] === 'image/svg+xml') {
            $dirty = file_get_contents($file['tmp_name']);

            // Create a new sanitizer instance
            $sanitizer = new \enshrined\svgSanitize\Sanitizer();

            // Configure the sanitizer as needed
            $sanitizer->minify(true);

            // Sanitize the SVG
            $clean = $sanitizer->sanitize($dirty);

            if (false === $clean) {
                // Handle the error appropriately
                $file['error'] = 'Error sanitizing SVG file.';
                return $file;
            }

            // Save the cleaned-up SVG content back to the temporary file
            file_put_contents($file['tmp_name'], $clean);
        }
        return $file;
    }
	
	function cits_enqueue_admin_styles() { 
        wp_enqueue_style('cits_admin_css', CITS_PLUGIN_URL . 'admin/cits-admin.css');
    }
}
new CITS_SUPPORT_SVG_WEBP_MEDIA();