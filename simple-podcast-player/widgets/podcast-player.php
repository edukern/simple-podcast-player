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
        $settings  = $this->get_settings_for_display();
        $audio_url = ! empty( $settings['audio_file']['url'] ) ? $settings['audio_file']['url'] : '';

        if ( empty( $audio_url ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div style="padding:12px;background:#f0f0f0;color:#555;font-size:13px;border-radius:4px;">'
                    . esc_html__( 'Podcast Player: select an audio file in the Content tab.', 'simple-podcast-player' )
                    . '</div>';
            }
            return;
        }

        $title = ! empty( $settings['episode_title'] )
            ? $settings['episode_title']
            : pathinfo( $audio_url, PATHINFO_FILENAME );
        ?>
        <div class="spp-player">
            <audio class="spp-audio" src="<?php echo esc_url( $audio_url ); ?>" preload="metadata"></audio>

            <div class="spp-bar">
                <svg class="spp-headphones" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="var(--spp-accent, #333)" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/>
                    <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                </svg>

                <button class="spp-play-btn" aria-label="<?php esc_attr_e( 'Play', 'simple-podcast-player' ); ?>">
                    <svg class="spp-icon-play" width="12" height="12" viewBox="0 0 24 24"
                         fill="currentColor" aria-hidden="true">
                        <polygon points="5,3 19,12 5,21"/>
                    </svg>
                    <svg class="spp-icon-pause" width="10" height="10" viewBox="0 0 24 24"
                         fill="currentColor" aria-hidden="true">
                        <rect x="6" y="4" width="4" height="16"/>
                        <rect x="14" y="4" width="4" height="16"/>
                    </svg>
                </button>

                <span class="spp-title"><?php echo esc_html( $title ); ?></span>
                <span class="spp-duration" aria-live="polite"></span>
            </div>

            <div class="spp-progress-row">
                <div class="spp-track"
                     role="progressbar"
                     aria-label="<?php esc_attr_e( 'Playback progress', 'simple-podcast-player' ); ?>"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     aria-valuenow="0"
                     tabindex="0">
                    <div class="spp-fill"></div>
                </div>
                <button class="spp-speed" aria-label="<?php esc_attr_e( 'Playback speed', 'simple-podcast-player' ); ?>">1×</button>
            </div>
        </div>
        <?php
    }
}
