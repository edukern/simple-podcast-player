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
        add_filter( 'plugin_action_links_' . $this->slug, [ $this, 'add_check_link' ] );
        add_action( 'admin_init', [ $this, 'maybe_force_check' ] );
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
            $package = $release->zipball_url;
            if ( ! empty( $release->assets ) ) {
                foreach ( $release->assets as $asset ) {
                    if ( substr( $asset->name, -4 ) === '.zip' ) {
                        $package = $asset->browser_download_url;
                        break;
                    }
                }
            }
            $transient->response[ $this->slug ] = (object) [
                'slug'        => dirname( $this->slug ),
                'plugin'      => $this->slug,
                'new_version' => $latest,
                'url'         => $release->html_url,
                'package'     => $package,
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
            'name'          => 'Keen Podcast Player',
            'slug'          => dirname( $this->slug ),
            'version'       => ltrim( $release->tag_name, 'v' ),
            'download_link' => $release->zipball_url,
        ];
    }

    public function add_check_link( $links ) {
        $url = wp_nonce_url(
            add_query_arg( 'spp_force_check', '1', admin_url( 'plugins.php' ) ),
            'spp_force_check'
        );
        $links[] = '<a href="' . esc_url( $url ) . '">Verificar Atualizações</a>';
        return $links;
    }

    public function maybe_force_check() {
        if ( ! isset( $_GET['spp_force_check'] ) ) return;
        if ( ! check_admin_referer( 'spp_force_check' ) ) return;
        if ( ! current_user_can( 'update_plugins' ) ) return;

        delete_transient( 'spp_github_release' );
        delete_site_transient( 'update_plugins' );

        wp_safe_redirect( admin_url( 'plugins.php' ) );
        exit;
    }
}
