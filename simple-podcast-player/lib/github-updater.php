<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SPP_GitHub_Updater {

    private $slug;
    private $file;
    private $repo;
    private $version;

    public function __construct( $file, $repo, $version ) {
        $this->file    = $file;
        $this->slug    = plugin_basename( $file );
        $this->repo    = $repo;
        $this->version = $version;

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
        add_filter( 'plugins_api', [ $this, 'plugin_info' ], 10, 3 );
    }

    private function get_latest_release() {
        $cached = get_transient( 'spp_github_release' );
        if ( $cached ) return $cached;

        $url      = "https://api.github.com/repos/{$this->repo}/releases/latest";
        $response = wp_remote_get( $url, [ 'headers' => [ 'User-Agent' => 'WordPress' ] ] );

        if ( is_wp_error( $response ) ) return false;

        $data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( empty( $data->tag_name ) ) return false;

        set_transient( 'spp_github_release', $data, 6 * HOUR_IN_SECONDS );
        return $data;
    }

    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_latest_release();
        if ( ! $release ) return $transient;

        $latest = ltrim( $release->tag_name, 'v' );
        if ( version_compare( $latest, $this->version, '>' ) ) {
            $transient->response[ $this->slug ] = (object) [
                'slug'        => dirname( $this->slug ),
                'plugin'      => $this->slug,
                'new_version' => $latest,
                'url'         => $release->html_url,
                'package'     => $release->zipball_url,
            ];
        }

        return $transient;
    }

    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( $args->slug !== dirname( $this->slug ) ) return $result;

        $release = $this->get_latest_release();
        if ( ! $release ) return $result;

        return (object) [
            'name'          => 'Simple Podcast Player',
            'slug'          => dirname( $this->slug ),
            'version'       => ltrim( $release->tag_name, 'v' ),
            'download_link' => $release->zipball_url,
        ];
    }
}
