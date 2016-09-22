<?php
/**
 * Created by PhpStorm.
 * User: xuantruong
 * Date: 12/27/15
 * Time: 1:39 PM
 */
namespace TranslateWidgetCf7;

class Factory {

    protected $settings;

    function __construct() {
        add_action( 'plugins_loaded', [ $this, 'pluginsLoaded' ], 0 );
        add_action( 'init', [ $this, 'init' ] );
    }

    function init() {

        // Contact form 7 translate with shortcode
        add_filter('wpcf7_contact_form_properties', [ $this, 'tl_wpcf7_add_other_shortcodes'] );
        add_shortcode( '_trans', [ $this, 'tl_etrans_shortcode' ] );
        return true;
    }

    function pluginsLoaded() {
        // Load resources
        $this->pluginLocalization();
    }

    /**
     *  Translate id with domain
     * @param $id
     * @param $domain
     * @param bool $echo
     * @return string
     */
    function _etrans($id, $domain, $echo = true ) {
        if( empty( $domain ))
            return $id;
        static $translate;
        $data_return = '';
        $domain_id = md5( $domain );
        $id_temp = $this->removeBreakLine( $id );
        try {
            if( !empty( $translate[ $domain_id ] )) {
                if( !empty( $translate[ $domain_id ][ $id_temp ])) {
                    $data_return = $translate[ $domain_id ][ $id_temp ];
                } else {
                    $data_return = $id;
                }
            } else {
                file_exists( $domain . '.php' )
                and
                $translate[$domain_id] = include $domain . '.php';
                !empty( $translate[$domain_id] )
                and $this->arrayChangeKey( $translate[$domain_id] );
                if( !empty( $translate[ $domain_id ][ $id_temp ])) {
                    $data_return = $translate[ $domain_id ][ $id_temp ];
                } else {
                    $data_return = $id;
                }
            }
        } catch( Exception $ex) { // Default return $id
            $data_return = $id;
        }
        if( !$echo )
            return $data_return;
        echo $data_return;
    }

    /**
     * Get return value from translation
     * @param $id
     * @param $domain
     * @return string
     */
    function _trans($id, $domain ) {
        return $this->_etrans( $id, $domain, false );
    }


    function tl_etrans_shortcode( $atts, $content ) {
        $atts = shortcode_atts( [
          'name' => 'etrans',
          'domain' => 'default'
        ], $atts );

        extract( $atts );

        if( $name != 'etrans' &&
          $domain != 'default' ) {
            return $this->_trans( $name, $domain );
        }
    }


    function tl_wpcf7_add_other_shortcodes( $properties ) {
        if( !is_admin() ) {
            $properties['form'] = $this->doShortcodeDeep( $properties['form'] );
            $this->tl_wpcf7_translate( $properties['messages'] );
        }
        return $properties;
    }

    function doShortcodeDeep( $data ) {
        $data = do_shortcode( $data );
        if( preg_match_all( '/(?<SC>\[_tran[^\[]+\])/', $data, $matches )) {
            if( !empty( $matches['SC'] )) {
                foreach( $matches['SC'] as $match ) {
                    $temp_sc = do_shortcode( $match );
                    $data = str_replace( $match, $temp_sc, $data );
                }
            }
        }
        return $data;
    }

    function tl_wpcf7_translate( &$messages ) {
        foreach( $messages as $k => $value ) {
            $messages[ $k ] = do_shortcode( $value );
        }
    }

    function arrayChangeKey( &$arr ) {
        foreach( $arr as $k => $item ) {
            $arr[ $this->removeBreakLine( $k ) ] = $item;
        }
    }

    function removeBreakLine( $content ) {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $content );
    }

    function scriptAndStyles()
    {

    }

    function _no_wpml_warning()
    {

    }

    function view()
    {

    }

    function removeDir( $dir )
    {
        if ( is_dir( $dir ) ) {
            $objects = scandir( $dir );
            foreach ( $objects as $object ) {
                if ($object != "." && $object != "..") {
                    if ( is_dir( $dir . "/" . $object ) )
                        $this->removeDir( $dir . "/" . $object );
                    else
                        unlink( $dir . "/" . $object );
                }
            }
            rmdir( $dir );
        }
    }

    function pluginActivate()
    {

    }

    function pluginDeactivate()
    {

    }

    // Localization
    function pluginLocalization()
    {
        load_plugin_textdomain( 'translate-widget-cf7-with-shortcode', false, basename( TRANSLATE_WIDGET_CF7_SHORTCODE_BASE ) . '/locale' );
    }
}