<?php
namespace Simple_Podcast_Player\Widget;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Podcast_Player extends Widget_Base {

    public function get_name() {
        return 'podcast-player';
    }

    public function get_title() {
        return esc_html__( 'Podcast Player', 'simple-podcast-player' );
    }

    public function get_icon() {
        return 'eicon-headphones';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'spp-player' ];
    }

    public function get_script_depends() {
        return [ 'spp-player' ];
    }

    protected function register_controls() {
        // Controls added in Tasks 3 and 4
    }

    protected function render() {
        // Render added in Task 5
    }
}
