<?php
/*
Plugin Name: NWS Alerts Plugin
Description: Add NWS Alerts to your website using NWS API.
Version: 0.2.0.0
Author: Cade Woolaway / Central Crossing FPD
License: GPL2
Plugin URI: https://github.com/WoolawayWx/NWS-Alerts-Plugin
*/

if (!defined('ABSPATH')) {
    exit;
}

define('NWS_ALERTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NWS_ALERTS_PLUGIN_URL', plugin_dir_url(__FILE__));

function nws_alerts_plugin_enqueue_scripts() {
    wp_register_script(
        'nws-alerts-plugin-script', 
        NWS_ALERTS_PLUGIN_URL . 'assets/js/NWSAlerts.js', 
        array('jquery'), 
        '1.0', 
        true
    );
    wp_enqueue_script('nws-alerts-plugin-script');
    
    wp_enqueue_style('nws-alerts-plugin-style', NWS_ALERTS_PLUGIN_URL . 'assets/css/nws-alerts.css');

    $options = get_option('nws_alerts_plugin_settings');
    $county_zones = isset($options['nws_alerts_plugin_county_zones']) ? $options['nws_alerts_plugin_county_zones'] : '';

    wp_localize_script('nws-alerts-plugin-script', 'nwsPluginData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'pluginUrl' => NWS_ALERTS_PLUGIN_URL,
        'countyZones' => $county_zones,
    ));
}
add_action('wp_enqueue_scripts', 'nws_alerts_plugin_enqueue_scripts');

function nws_alerts_plugin_shortcode() {
    return '<div id="nws-alerts-plugin-container"></div>';
}
add_shortcode('nws_alerts_plugin', 'nws_alerts_plugin_shortcode');

function nws_alerts_plugin_add_admin_menu() {
    add_menu_page(
        'NWS Alerts Settings', 
        'NWS Alerts', 
        'manage_options', 
        'nws-alerts-plugin', 
        'nws_alerts_plugin_settings_page' 
    );
}
add_action('admin_menu', 'nws_alerts_plugin_add_admin_menu');

function nws_alerts_plugin_register_settings() {
    register_setting('nws_alerts_plugin_settings_group', 'nws_alerts_plugin_settings');

    add_settings_section(
        'nws_alerts_plugin_settings_section',
        'NWS Alerts Settings',
        'nws_alerts_plugin_settings_section_callback',
        'nws-alerts-plugin'
    );

    add_settings_field(
        'nws_alerts_plugin_county_zones',
        'County Zones',
        'nws_alerts_plugin_county_zones_callback',
        'nws-alerts-plugin',
        'nws_alerts_plugin_settings_section'
    );

    add_settings_field(
        'nws_alerts_plugin_custom_colors',
        'Custom Alert Colors',
        'nws_alerts_plugin_custom_colors_callback',
        'nws-alerts-plugin',
        'nws_alerts_plugin_settings_section'
    );
}
add_action('admin_init', 'nws_alerts_plugin_register_settings');

function nws_alerts_plugin_settings_section_callback() {
    ?>
    <h2>Setting Up Alerts</h2>
    <p>Locate the counties that you'd like to add via the <a href="https://alerts.weather.gov" target="_blank" rel="nofollow">NWS Alerts page (click here)</a></p>
    <p>Put them in, separated by commas, in the box below.</p>
    <p>Example: <code>MOC009, MOC209</code></p>
    <?php
}

function nws_alerts_plugin_county_zones_callback() {
    $options = get_option('nws_alerts_plugin_settings');
    $county_zones = isset($options['nws_alerts_plugin_county_zones']) ? json_decode($options['nws_alerts_plugin_county_zones'], true) : array();
    ?>
    <table id="county-zones-table">
        <thead>
            <tr>
                <th>County Zone ID</th>
                <th>County Zone Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($county_zones)) : ?>
                <?php foreach ($county_zones as $zone_id => $zone_name) : ?>
                    <tr>
                        <td><input type="text" name="county_zone_ids[]" value="<?php echo esc_attr($zone_id); ?>" /></td>
                        <td><input type="text" name="county_zone_names[]" value="<?php echo esc_attr($zone_name); ?>" /></td>
                        <td><button type="button" class="remove-zone">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <button type="button" id="add-zone">Add County Zone</button>
    <input type="hidden" name="nws_alerts_plugin_settings[nws_alerts_plugin_county_zones]" id="county-zones-json" value="<?php echo esc_attr($options['nws_alerts_plugin_county_zones']); ?>" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = document.getElementById('county-zones-table').getElementsByTagName('tbody')[0];
            var addZoneButton = document.getElementById('add-zone');
            var countyZonesJsonInput = document.getElementById('county-zones-json');

            addZoneButton.addEventListener('click', function() {
                var newRow = table.insertRow();
                var cell1 = newRow.insertCell(0);
                var cell2 = newRow.insertCell(1);
                var cell3 = newRow.insertCell(2);
                cell1.innerHTML = '<input type="text" name="county_zone_ids[]" />';
                cell2.innerHTML = '<input type="text" name="county_zone_names[]" />';
                cell3.innerHTML = '<button type="button" class="remove-zone">Remove</button>';
            });

            table.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('remove-zone')) {
                    var row = e.target.closest('tr');
                    row.parentNode.removeChild(row);
                }
            });

            document.querySelector('form').addEventListener('submit', function() {
                var countyZoneIds = document.getElementsByName('county_zone_ids[]');
                var countyZoneNames = document.getElementsByName('county_zone_names[]');
                var countyZones = {};

                for (var i = 0; i < countyZoneIds.length; i++) {
                    if (countyZoneIds[i].value && countyZoneNames[i].value) {
                        countyZones[countyZoneIds[i].value] = countyZoneNames[i].value;
                    }
                }

                countyZonesJsonInput.value = JSON.stringify(countyZones);
            });
        });
    </script>
    <?php
}

function nws_alerts_plugin_custom_colors_callback() {
    $options = get_option('nws_alerts_plugin_settings');
    $custom_colors = isset($options['nws_alerts_plugin_custom_colors']) ? json_decode($options['nws_alerts_plugin_custom_colors'], true) : array();
    $default_colors = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'assets/json/colors.json'), true);
    ?>
    <table id="custom-colors-table">
        <thead>
            <tr>
                <th>Alert Type</th>
                <th>Background Color</th>
                <th>Text Color</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($default_colors as $alert_type => $colors) : ?>
                <tr>
                    <td><?php echo esc_html($alert_type); ?></td>
                    <td><input type="text" name="custom_colors[<?php echo esc_attr($alert_type); ?>][background]" value="<?php echo esc_attr($custom_colors[$alert_type]['background'] ?? $colors['background']); ?>" /></td>
                    <td><input type="text" name="custom_colors[<?php echo esc_attr($alert_type); ?>][text]" value="<?php echo esc_attr($custom_colors[$alert_type]['text'] ?? $colors['text']); ?>" /></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" name="nws_alerts_plugin_settings[nws_alerts_plugin_custom_colors]" id="custom-colors-json" value="<?php echo esc_attr(json_encode($custom_colors)); ?>" />
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            var customColors = {};
            var rows = document.querySelectorAll('#custom-colors-table tbody tr');
            rows.forEach(function(row) {
                var alertType = row.cells[0].textContent;
                var backgroundColor = row.cells[1].querySelector('input').value;
                var textColor = row.cells[2].querySelector('input').value;
                customColors[alertType] = {
                    background: backgroundColor,
                    text: textColor
                };
            });
            document.getElementById('custom-colors-json').value = JSON.stringify(customColors);
        });
    </script>
    <?php
}

function nws_alerts_plugin_register_block() {
    wp_register_script(
        'nws-alerts-block-js',
        plugins_url('blocks/build/index.js', __FILE__),
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/build/index.js'),
        false
    );

    register_block_type('nws-alerts-plugin/nws-alerts-block', array(
        'editor_script' => 'nws-alerts-block-js',
    ));
}
add_action('init', 'nws_alerts_plugin_register_block');
function nws_alerts_enqueue_frontend_scripts() {
    if (!is_admin()) { // Ensure script only loads on frontend
        wp_enqueue_script(
            'nws-alerts-frontend',
            plugin_dir_url(__FILE__) . 'assets/js/NWSAlerts.js',
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/NWSAlerts.js'),
            true
        );

        // Retrieve options from database
        $options = get_option('nws_alerts_plugin_settings', []);
        $county_zones = isset($options['nws_alerts_plugin_county_zones']) ? $options['nws_alerts_plugin_county_zones'] : '{}';
        $custom_colors = isset($options['nws_alerts_plugin_custom_colors']) ? $options['nws_alerts_plugin_custom_colors'] : '{}';

        // Ensure JSON decoding in case values are stored as JSON strings
        $county_zones = is_array($county_zones) ? $county_zones : json_decode($county_zones, true);
        $custom_colors = is_array($custom_colors) ? $custom_colors : json_decode($custom_colors, true);

        // Ensure the data is properly formatted as an array
        if (!is_array($county_zones)) {
            $county_zones = [];
        }
        if (!is_array($custom_colors)) {
            $custom_colors = [];
        }

        // Pass properly formatted data to JavaScript
        wp_localize_script('nws-alerts-frontend', 'nwsPluginData', array(
            'countyZones' => $county_zones,
            'customColors' => $custom_colors,
            'pluginUrl' => plugin_dir_url(__FILE__)
        ));
    }
}
add_action('wp_enqueue_scripts', 'nws_alerts_enqueue_frontend_scripts');

function nws_alerts_enqueue_editor_assets() {
    wp_enqueue_script(
        'nws-alerts-block-editor',
        plugin_dir_url(__FILE__) . 'blocks/build/index.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/build/index.js'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'nws_alerts_enqueue_editor_assets');

function nws_alerts_enqueue_scripts() {
    if (!did_action('elementor/loaded')) {
        return;
    }

    wp_enqueue_script(
        'nws-alerts-frontend',
        plugin_dir_url(__FILE__) . 'assets/js/NWSAlerts.js',
        array('jquery'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/NWSAlerts.js'),
        true
    );

    // Ensure data is passed correctly
    $options = get_option('nws_alerts_plugin_settings');
    $county_zones = isset($options['nws_alerts_plugin_county_zones']) ? $options['nws_alerts_plugin_county_zones'] : '{}';
    $custom_colors = isset($options['nws_alerts_plugin_custom_colors']) ? $options['nws_alerts_plugin_custom_colors'] : '{}';

    wp_localize_script('nws-alerts-frontend', 'nwsPluginData', array(
        'countyZones' => json_decode($county_zones, true),
        'customColors' => json_decode($custom_colors, true),
        'pluginUrl' => plugin_dir_url(__FILE__)
    ));
}
add_action('wp_enqueue_scripts', 'nws_alerts_enqueue_scripts');


function nws_alerts_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>NWS Alerts Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nws_alerts_plugin_settings_group');
            do_settings_sections('nws-alerts-plugin');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('elementor/widgets/widgets_registered', 'nws_alerts_register_elementor_widget');
function nws_alerts_register_elementor_widget() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-nws-alerts-elementor-widget.php';
}