<?php
/*
Plugin Name: Fast TOC
Description: Display a table of content
Version: 20200105
Author: Michiel van Eerd
Author URI: https://www.michielvaneerd.nl/
License: GPL2
*/

define('FAST_TOC_PLUGIN_VERSION', '20200105');

add_action('wp_enqueue_scripts', function() {
    if (is_singular()) {

        wp_enqueue_script('mve_toc', plugin_dir_url(__FILE__) . 'toc.js', [], FAST_TOC_PLUGIN_VERSION);
        $showToc = get_post_meta(get_the_ID(), 'mve_show_toc', true);
        $jsVar = [
            'show_toc' => $showToc !== '' ? ($showToc === 'true' ? true : false) : (get_option('fast_toc_enabled') == 1 ? true : false),
            'root_selector' => get_option('fast_toc_root_selector'),
            'title' => get_option('fast_toc_title'),
            'fast_toc_selector_ignore' => get_option('fast_toc_selector_ignore')
        ];
        wp_add_inline_script('mve_toc', 'window.MVE_FAST_TOC=' . json_encode($jsVar) . ';', 'before');

        wp_enqueue_style('mve_toc', plugin_dir_url(__FILE__) . 'toc.css', [], FAST_TOC_PLUGIN_VERSION);
    }
});

add_action('init', function() {

    register_post_meta('', 'mve_show_toc', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string', // 'true' or 'false' as strings, empty string means not set yet
    ));

    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');
    wp_register_script(
        'plugin-sidebar-js',
        plugins_url('/build/index.js', __FILE__),
        $asset_file['dependencies'],
        $asset_file['version']
    );
    $enabledByDefault = get_option('fast_toc_enabled') == 1;
    wp_add_inline_script('plugin-sidebar-js', 'window.MVE_FAST_TOC_ENABLED=' . json_encode($enabledByDefault) . ';', 'before');

});

add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script('plugin-sidebar-js');
});

add_action('admin_init', function() {

    register_setting('reading', 'fast_toc_root_selector');
    register_setting('reading', 'fast_toc_selector_ignore');
    register_setting('reading', 'fast_toc_enabled');
    register_setting('reading', 'fast_toc_title');

    add_settings_section(
        'fast_toc_settings_section',
        'Fast TOC settings',
        function() {},
        'reading'
    );

    add_settings_field(
        'fast_toc_enabled',
        'Enable',
        function() {
            $setting = get_option('fast_toc_enabled');
            ?>
            <fieldset>
            <label>
            <input id="fast_toc_enabled" type="checkbox" name="fast_toc_enabled" <?php checked($setting, '1'); ?> value="1">
            Enable by default
            </label>
            <p class="description">You can overrule this per post.</p>
            </fieldset>
            <?php
        },
        'reading',
        'fast_toc_settings_section'
    );
 
    add_settings_field(
        'fast_toc_root_selector',
        '<label for="fast_toc_root_selector">Root selector</label>',
        function() {
            $setting = get_option('fast_toc_root_selector');
            ?>
            <input id="fast_toc_root_selector" type="text" name="fast_toc_root_selector" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
            <p class="description">The CSS selector of the root element that wraps all the headers. If this is empty, the root element will be the BODY.</p>
            <?php
        },
        'reading',
        'fast_toc_settings_section'
    );

    add_settings_field(
        'fast_toc_selector_ignore',
        '<label for="fast_toc_selector_ignore">Ignore selector</label>',
        function() {
            $setting = get_option('fast_toc_selector_ignore');
            ?>
            <input id="fast_toc_selector_ignore" type="text" name="fast_toc_selector_ignore" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
            <p class="description">The CSS selector of headers that should NOT be added to the TOC. If this is empty, no header inside the root element is ignored.</p>
            <?php
        },
        'reading',
        'fast_toc_settings_section'
    );

    add_settings_field(
        'fast_toc_title',
        '<label for="fast_toc_title">Title</label>',
        function() {
            $setting = get_option('fast_toc_title');
            ?>
            <input id="fast_toc_title" type="text" name="fast_toc_title" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
            <p class="description">Leave empty to hide the title.</p>
            <?php
        },
        'reading',
        'fast_toc_settings_section'
    );

    
});
