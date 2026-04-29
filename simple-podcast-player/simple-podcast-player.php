<?php
/**
 * Plugin Name: Simple Podcast Player
 * Description: An Elementor widget for playing a single podcast episode from the WordPress media library.
 * Version:     1.0.0
 * Author:      Your Name
 * License:     GPL-2.0-or-later
 * Text Domain: simple-podcast-player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'spp_init', 20 );

function spp_init() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'spp_missing_elementor_notice' );
        return;
    }

    add_action( 'elementor/widgets/register', 'spp_register_widgets' );
    add_action( 'elementor/frontend/after_register_scripts', 'spp_register_assets' );
}

function spp_missing_elementor_notice() {
    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html__( 'Simple Podcast Player requires Elementor to be installed and active.', 'simple-podcast-player' )
    );
}

function spp_register_widgets( $widgets_manager ) {
    require_once __DIR__ . '/widgets/podcast-player.php';
    $widgets_manager->register( new \Simple_Podcast_Player\Widget\Podcast_Player() );
}

function spp_register_assets() {
    wp_register_style(
        'spp-player',
        plugins_url( 'assets/player.css', __FILE__ ),
        [],
        '1.0.0'
    );
    wp_register_script(
        'spp-player',
        plugins_url( 'assets/player.js', __FILE__ ),
        [],
        '1.0.0',
        true
    );
    wp_localize_script( 'spp-player', 'sppData', [
        'errorText' => __( 'Audio unavailable', 'simple-podcast-player' ),
    ] );
}
