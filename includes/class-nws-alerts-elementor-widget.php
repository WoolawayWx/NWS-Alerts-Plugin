<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class NWS_Alerts_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nws_alerts';
    }

    public function get_title() {
        return __('NWS Alerts Block', 'nws-alerts-plugin');
    }

    public function get_icon() {
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
            >
                <path
                    fill="currentColor"
                    d="M6 12h12a3 3 0 1 0 0-6c-.64 0-1.174-.461-1.436-1.045a5 5 0 0 0-9.128 0C7.174 5.539 6.64 6 6 6a3 3 0 0 0 0 6"
                />
                <path
                    fill="currentColor"
                    fill-rule="evenodd"
                    d="m10.235 14l-.462.786c-.787 1.338-1.18 2.007-.893 2.51c.288.504 1.065.504 2.62.504c.236 0 .354 0 .427.073s.073.191.073.427v1.864c0 .74 0 1.109.184 1.16c.185.05.372-.27.747-.907l1.296-2.203c.787-1.338 1.18-2.007.893-2.51c-.288-.504-1.065-.504-2.62-.504c-.236 0-.354 0-.427-.073S12 14.936 12 14.7V14z"
                    clip-rule="evenodd"
                />
            </svg>
        ');
    }
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        echo do_shortcode('[nws_alerts_plugin]');
    }
}

// Register the widget
function register_nws_alerts_elementor_widget($widgets_manager) {
    require_once(plugin_dir_path(__FILE__) . 'class-nws-alerts-elementor-widget.php');
    $widgets_manager->register(new NWS_Alerts_Elementor_Widget());
}
add_action('elementor/widgets/register', 'register_nws_alerts_elementor_widget');
