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
        $this->start_controls_section( 'section_content', [
            'label' => esc_html__( 'Content', 'simple-podcast-player' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'audio_file', [
            'label'      => esc_html__( 'Audio File', 'simple-podcast-player' ),
            'type'       => Controls_Manager::MEDIA,
            'media_type' => 'audio',
        ] );

        $this->add_control( 'episode_title', [
            'label'       => esc_html__( 'Episode Title', 'simple-podcast-player' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'Defaults to file name', 'simple-podcast-player' ),
        ] );

        $this->end_controls_section();

        $this->start_controls_section( 'section_style', [
            'label' => esc_html__( 'Style', 'simple-podcast-player' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'accent_color', [
            'label'     => esc_html__( 'Accent Color', 'simple-podcast-player' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [
                '{{WRAPPER}} .spp-player' => '--spp-accent: {{VALUE}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'title_typography',
            'selector' => '{{WRAPPER}} .spp-title',
        ] );

        $this->add_control( 'background_color', [
            'label'     => esc_html__( 'Background', 'simple-podcast-player' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .spp-player' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'border_radius', [
            'label'      => esc_html__( 'Border Radius', 'simple-podcast-player' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 6, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .spp-player' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        // Render added in Task 5
    }
}
