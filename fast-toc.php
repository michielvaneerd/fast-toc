<?php
/*
Plugin Name: Fast TOC
Description: Display a table of content
Version: 20200105
Author: Michiel van Eerd
Author URI: https://www.michielvaneerd.nl/
Requires at least: 5
Requires PHP: 5.4.0
License: GPL2
*/

define('FAST_TOC_PLUGIN_VERSION', '20200105');

add_action('wp_enqueue_scripts', function() {
    
    if (is_singular()) {

        if (in_array(get_post_type(), get_option('fast_toc_post_types', []))) {
            wp_enqueue_script('mve_toc', plugin_dir_url(__FILE__) . 'toc.js', [], FAST_TOC_PLUGIN_VERSION);
            $showToc = get_post_meta(get_the_ID(), 'mve_show_toc', true);
            $jsVar = [
                'show_toc' => $showToc !== '' ? ($showToc === 'true' ? true : false) : (get_option('fast_toc_enabled') == 1 ? true : false),
                'root_selector' => get_option('fast_toc_root_selector'),
                'title' => get_option('fast_toc_title'),
                'selector_ignore' => get_option('fast_toc_selector_ignore'),
                'minimal_header_count' => get_option('fast_toc_minimal_header_count', 1)
            ];
            wp_add_inline_script('mve_toc', 'window.MVE_FAST_TOC=' . json_encode($jsVar) . ';', 'before');

            wp_enqueue_style('mve_toc', plugin_dir_url(__FILE__) . 'toc.css', [], FAST_TOC_PLUGIN_VERSION);
        }
    }
});

add_action('init', function() {

    $postTypes = get_option('fast_toc_post_types', []);
    foreach ($postTypes as $postType) {
        register_post_meta($postType, 'mve_show_toc', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string', // 'true' or 'false' as strings, empty string means not set yet
        ));
    }

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
    if (in_array(get_post_type(), get_option('fast_toc_post_types', []))) {
        wp_enqueue_script('plugin-sidebar-js');
    }
});

add_action('admin_init', function() {

    global $pagenow;

    // Dit wordt op elke admin pagina uitgevoerd, lijkt me niet nodig toch?

    register_setting('reading', 'fast_toc_root_selector');
    register_setting('reading', 'fast_toc_selector_ignore');
    register_setting('reading', 'fast_toc_enabled');
    register_setting('reading', 'fast_toc_title');
    register_setting('reading', 'fast_toc_post_types');
    register_setting('reading', 'fast_toc_minimal_header_count');

    if ($pagenow === "options-reading.php") {

        add_settings_section(
            'fast_toc_settings_section',
            'Fast TOC settings',
            function() {},
            'reading'
        );

        add_settings_field(
            'fast_toc_post_types',
            'Post types',
            function() {
                $postTypes = get_post_types(['public'   => true], 'objects');
                $setting = get_option('fast_toc_post_types');
                echo '<fieldset><p>';
                foreach ($postTypes as $postType) {
                    ?>
                    <label>
                    <input type="checkbox" name="fast_toc_post_types[]" value="<?php echo $postType->name; ?>" <?php checked(true, in_array($postType->name, $setting)); ?>>
                    <?php echo $postType->label; ?>
                    </label><br>
                    <?php
                }
                ?><p class="description">Enable for selected post types.</p></p></fieldset><?php
            },
            'reading',
            'fast_toc_settings_section'
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
                Enabled by default for all selected post types
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

        add_settings_field(
            'fast_toc_minimal_header_count',
            '<label for="fast_toc_minimal_header_count">Minimum number of headers</label>',
            function() {
                $setting = get_option('fast_toc_minimal_header_count');
                ?>
                <input min="1" id="fast_toc_minimal_header_count" type="number" name="fast_toc_minimal_header_count" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
                <p class="description">Show the TOC only when there are at least this number of headers.</p>
                <?php
            },
            'reading',
            'fast_toc_settings_section'
        );

    }
    
});
