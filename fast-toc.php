<?php
/*
Plugin Name: Fast TOC
Description: Display a table of contents
Version: 20200111
Author: Michiel van Eerd
Author URI: https://www.michielvaneerd.nl/
Requires at least: 5
Requires PHP: 5.4.0
License: GPL2
*/

define('FAST_TOC_PLUGIN_VERSION', '20200111');

define('FAST_TOC_DEFAULTS', [
    'fast_toc_root_selector' => 'body',
    'fast_toc_minimal_header_count' => 5,
    'fast_toc_list_type' => 'regular',
    'fast_toc_enabled_default' => 1,
    'fast_toc_post_types' => [],
    'fast_toc_show_counter' => 0
]);

function fast_toc_get_option($option) {
    return get_option($option, array_key_exists($option, FAST_TOC_DEFAULTS) ? FAST_TOC_DEFAULTS[$option] : null);
}

add_action('wp_enqueue_scripts', function() {
    
    if (is_singular()) {

        if (in_array(get_post_type(), fast_toc_get_option('fast_toc_post_types'))) {
            wp_enqueue_script('fast_toc', plugin_dir_url(__FILE__) . 'toc.js', [], FAST_TOC_PLUGIN_VERSION);
            $showToc = get_post_meta(get_the_ID(), 'fast_toc_show_toc', true);
            $jsVar = [
                'show_toc' => $showToc !== '' ? ($showToc === 'true' ? true : false) : (fast_toc_get_option('fast_toc_enabled_default') == 1 ? true : false),
                'root_selector' => fast_toc_get_option('fast_toc_root_selector'),
                'title' => fast_toc_get_option('fast_toc_title'),
                'selector_ignore' => fast_toc_get_option('fast_toc_selector_ignore'),
                'minimal_header_count' => fast_toc_get_option('fast_toc_minimal_header_count'),
                'list_type' => fast_toc_get_option('fast_toc_list_type'),
                'show_counter' => fast_toc_get_option('fast_toc_show_counter')
            ];
            wp_add_inline_script('fast_toc', 'window.FAST_TOC=' . json_encode($jsVar) . ';', 'before');

            wp_enqueue_style('fast_toc', plugin_dir_url(__FILE__) . 'toc.css', [], FAST_TOC_PLUGIN_VERSION);
        }
    }
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'options-reading.php') {
        wp_enqueue_script('fast_toc_admin', plugin_dir_url(__FILE__) . 'toc-admin.js', null, FAST_TOC_PLUGIN_VERSION, true);
    }
});

add_action('init', function() {

    $postTypes = fast_toc_get_option('fast_toc_post_types');
    foreach ($postTypes as $postType) {
        register_post_meta($postType, 'fast_toc_show_toc', array(
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
    $enabledByDefault = fast_toc_get_option('fast_toc_enabled_default') == 1;
    wp_add_inline_script('plugin-sidebar-js', 'window.fast_toc_enabled_default=' . json_encode($enabledByDefault) . ';', 'before');

});

add_action('enqueue_block_editor_assets', function() {
    if (in_array(get_post_type(), fast_toc_get_option('fast_toc_post_types'))) {
        wp_enqueue_script('plugin-sidebar-js');
    }
});

add_action('admin_init', function() {

    global $pagenow;

    register_setting('reading', 'fast_toc_root_selector');
    register_setting('reading', 'fast_toc_selector_ignore');
    register_setting('reading', 'fast_toc_enabled_default');
    register_setting('reading', 'fast_toc_title');
    register_setting('reading', 'fast_toc_post_types');
    register_setting('reading', 'fast_toc_minimal_header_count');
    register_setting('reading', 'fast_toc_list_type');
    register_setting('reading', 'fast_toc_show_counter');

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
                $setting = fast_toc_get_option('fast_toc_post_types');
                echo '<fieldset><p>';
                foreach ($postTypes as $postType) {
                    ?>
                    <label>
                    <input type="checkbox" name="fast_toc_post_types[]" value="<?php echo $postType->name; ?>" <?php checked(true, in_array($postType->name, $setting)); ?>>
                    <?php echo $postType->label; ?>
                    </label><br>
                    <?php
                }
                ?><p class="description">Make Fast TOC available for the selected post types.</p></p></fieldset><?php
            },
            'reading',
            'fast_toc_settings_section'
        );

        add_settings_field(
            'fast_toc_enabled_default',
            'Enable',
            function() {
                $setting = fast_toc_get_option('fast_toc_enabled_default');
                ?>
                <fieldset>
                <label>
                <input id="fast_toc_enabled_default" type="checkbox" name="fast_toc_enabled_default" <?php checked($setting, '1'); ?> value="1">
                Enable Fast TOC by default for all selected post types
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
                $setting = fast_toc_get_option('fast_toc_root_selector');
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
                $setting = fast_toc_get_option('fast_toc_selector_ignore');
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
                $setting = fast_toc_get_option('fast_toc_title');
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
                $setting = fast_toc_get_option('fast_toc_minimal_header_count');
                ?>
                <input min="1" id="fast_toc_minimal_header_count" type="number" name="fast_toc_minimal_header_count" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
                <p class="description">Show the TOC only when there are at least this number of headers.</p>
                <?php
            },
            'reading',
            'fast_toc_settings_section'
        );

        add_settings_field(
            'fast_toc_list_type',
            'List type',
            function() {
                $setting = fast_toc_get_option('fast_toc_list_type');
                ?>
                <fieldset>
                <?php
                foreach ([
                    ['regular', 'Regular'],
                    ['collapsible', 'Collapsible'],
                    ['flat', 'Flat', 'Use this option if the hierarchy of the headers is incorrect.']
                ] as $item) {
                    ?>
                    <p>
                    <label>
                        <input onchange="window.onListTypeChange(this)" <?php checked($setting, $item[0]); ?> type="radio" name="fast_toc_list_type" value="<?php echo esc_attr($item[0]); ?>">
                        <?php echo esc_html($item[1]); ?>
                    </label>
                    <?php
                    if (!empty($item[2])) {
                        ?>
                        (<span class="description"><?php echo esc_html($item[2]); ?></span>)
                        <?php
                    }
                    ?>
                    </p>
                    <?php
                }
                ?>
                </fieldset>
                <?php
            },
            'reading',
            'fast_toc_settings_section'
        );

        add_settings_field(
            'fast_toc_show_counter',
            'Show numbers',
            function() {
                $setting = fast_toc_get_option('fast_toc_show_counter');
                ?>
                <fieldset>
                <label>
                <input id="fast_toc_show_counter" type="checkbox" name="fast_toc_show_counter" <?php checked($setting, '1'); ?> value="1">
                Show numbers
                </label>
                </fieldset>
                <?php
            },
            'reading',
            'fast_toc_settings_section'
        );

    }
    
});

if (is_admin()) {
    $plugin = plugin_basename(__FILE__);
    add_filter('plugin_action_links_' . $plugin, 'fast_toc_add_plugin_settings_links');
    add_filter('network_admin_plugin_action_links_' . $plugin, 'fast_toc_add_plugin_settings_links');
}
  
function fast_toc_add_plugin_settings_links($links) {
    if (!is_network_admin()) {
        $link = '<a href="' . admin_url('options-reading.php') . '">'.__('Plugin settings').'</a>';
        array_unshift($links, $link);
    } else {
        // switch_to_blog(1);
        $link = '<a href="' . admin_url('options-reading.php').'">' . __('Plugin settings').'</a>';
        // restore_current_blog();
        array_unshift($links, $link);
    }
    return $links;
}
