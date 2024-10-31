<?php
/*
Plugin Name: Retrieval
Plugin URI: https://retrievalfulfillment.com
Description: Woocommerce Local Pickup with Retrieval
Version: 1.0.0
Requires at least: 4.5
Tested up to: 6.0.2
WC requires at least: 3.5
WC tested up to: 6.8.2
Author: Sami Younas
Author URI:  https://digigeeko.com
Text Domain: retrieval
Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!defined('WRL_PLUGIN_DIR'))
    define( 'WRL_PLUGIN_DIR', dirname(__FILE__) );
if (!defined('WRL_PLUGIN_ROOT_PHP'))
    define( 'WRL_PLUGIN_ROOT_PHP', dirname(__FILE__).'/'.basename(__FILE__)  );
if(!defined('WRL_PLUGIN_ABSOLUTE_PATH'))
    define('WRL_PLUGIN_ABSOLUTE_PATH',plugin_dir_url(__FILE__));
if (!defined('WRL_PLUGIN_ADMIN_DIR'))
    define( 'WRL_PLUGIN_ADMIN_DIR', dirname(__FILE__) . '/admin' );
if (!defined('WRL_TEXT_DOMAIN'))
    define( 'WRL_TEXT_DOMAIN', 'retrieval' );

if( !class_exists('WC_Retrieval_Live') ) {
    class WC_Retrieval_Live{
        public static $menuIcon='PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMjU2LjAwMDAwMHB0IiBoZWlnaHQ9IjI1Ni4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDI1Ni4wMDAwMDAgMjU2LjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMjU2LjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKZmlsbD0iIzAwMDAwMCIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTAgMTI4MCBsMCAtMTI4MCAxMjgwIDAgMTI4MCAwIDAgMTI4MCAwIDEyODAgLTEyODAgMCAtMTI4MCAwIDAKLTEyODB6IG0xMTUzIDkxMyBjNzMgLTcwIDEzMiAtMTMxIDEzMiAtMTM1IDAgLTQgLTE4MCAtMTExIC0zOTkgLTIzNyBsLTM5OQotMjI5IC0xNjMgMTQ3IC0xNjIgMTQ2IDgxIDQxIGM0NSAyMyAyMzUgMTIxIDQyMiAyMTggMTg3IDk2IDM0NCAxNzYgMzQ4IDE3Ngo1IDAgNjggLTU3IDE0MCAtMTI3eiBtMTg1IC04ODIgbC0zIC0yNDAgLTM4OCAyMjggYy0yMTMgMTI1IC0zOTIgMjMyIC0zOTcKMjM3IC02IDYgMTQyIDk5IDM4OCAyNDYgbDM5NyAyMzYgMyAtMjMzIGMxIC0xMjkgMSAtMzQyIDAgLTQ3NHogbTUxNSA0MjEKYzE3NiAtMTA0IDMxNSAtMTkyIDMwOSAtMTk2IC0xMiAtNyAtNzkyIDQ1NiAtNzkyIDQ3MSAwIDcgMzcgLTkgODMgLTM2IDQ1Ci0yNyAyMjUgLTEzNCA0MDAgLTIzOXogbS04ODEgLTUwNSBsMzY4IC0yMjIgMCAtMzU0IDAgLTM1MyAtMjIgMTQgYy0xMyA4Ci0xOTUgMTE1IC00MDUgMjM4IGwtMzgzIDIyNSAwIDM1OCAwIDM1OSAzOCAtMjIgYzIwIC0xMiAyMDIgLTEyMiA0MDQgLTI0M3oKbTExOTggLTkwIGwwIC0zNTQgLTM4MCAtMjQxIC0zODAgLTI0MSAwIDM0NCAwIDM0NCAzMiAyMyBjMjcgMjAgNzE5IDQ3NSA3MjYKNDc3IDEgMSAyIC0xNTggMiAtMzUyeiIvPgo8L2c+Cjwvc3ZnPgo=';
        public static $apiBase='https://api.retrievalfulfillment.com/api/';
        public function __construct() {
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                require_once(WRL_PLUGIN_ADMIN_DIR.'/class-admin.php');
                require_once(WRL_PLUGIN_DIR.'/inc/class-retrieval-live-cart.php');
                require_once(WRL_PLUGIN_DIR.'/inc/class-retrieval-live-checkout.php');
                require_once(WRL_PLUGIN_DIR.'/inc/class-retrieval-live-order.php');
                require_once(WRL_PLUGIN_DIR.'/inc/class-scripts-styles.php');
                require_once(WRL_PLUGIN_DIR.'/inc/class-frontend-ajax.php');
                add_action( 'woocommerce_shipping_init', [&$this,'add_shipping_class'] );
                add_filter( 'woocommerce_shipping_methods',[&$this,'set_retrieval_live_pickup'] );
                add_filter('plugin_action_links_'.plugin_basename(__FILE__), [&$this,'add_setting_link']);
            }else{
                add_action( 'admin_notices',[&$this,'add_notice_for_woocommerce']);
            }
         
        }
       
        public function add_shipping_class(){
            require_once(WRL_PLUGIN_DIR.'/inc/class-retrieval-live-shipping.php');
        }
        public function set_retrieval_live_pickup($methods){
            if(class_exists('Retrieval_Live_Pickup')){
            $methods[] = 'Retrieval_Live_Pickup';
            }
            return $methods;
        }
        public function add_notice_for_woocommerce(){
            ?>
            <div class="notice notice-error">
                <p><strong><?php esc_attr_e('Retrieval requires the WooCommerce plugin to be installed and active.', 'retrieval'); ?></strong></p>
               
            </div>
            <?php
        }
        public function add_setting_link($links){
            array_unshift($links, '<a href="' .
                    admin_url( 'admin.php?page=wc-settings&tab=shipping&section=retrieval_live' ) .
                    '">' . esc_attr__('Enable/Disbale','retrieval') . '</a>');
                    array_unshift($links, '<a href="' .
                    admin_url( 'admin.php?page=wrl-options' ) .
                    '">' . esc_attr__('Configuration','retrieval') . '</a>');
            return $links;
        }
        public static function generateApiToken($accessToken){
            $endPoint='generateApiToken';
            $url=self::$apiBase.$endPoint;
         
            $headers['headers']=[
                'Authorization'=>'Bearer '.$accessToken,
                'Content-Type'=>'application/json'
            ];
            $response=wp_remote_get($url,$headers);
            if(!is_wp_error($response)){
                return wp_remote_retrieve_body($response);
            }
            return false;  
        }
        public static function getRemoteData($endPoint){
            $token=esc_attr(get_option('wrl_api_token'));
            $url=self::$apiBase.$endPoint;
            $headers['headers']=[
                'Role'=>'client',
                'Authorization'=>'Bearer '.$token,
            ];
            $response=wp_remote_get($url,$headers);
             
            if(!is_wp_error($response)){
                return wp_remote_retrieve_body($response);
            }
            
              return false;  
        }
        public static function postRemoteData($endPoint,$postedData=[]){
            $url=self::$apiBase.$endPoint;
           
            $response=wp_remote_post($url,['body'=>$postedData]);
          
            if(!is_wp_error($response)){
                return wp_remote_retrieve_body($response);
            }
            return false;  


        }
        public static function postRemoteDataWithToken($endPoint,$postedData=[],$method='POST',$isJson=false){
            $token=get_option('wrl_api_token');
            $url=self::$apiBase.$endPoint;

        $headers=[
            'Authorization'=>'Bearer '.$token,
            'Role'=>'client'
        ];
          if($isJson){
          $headers['Content-Type']='application/json';
          }
       
            $response=wp_remote_post($url,['body'=>$postedData,'method'=>$method,'headers'=>$headers]);
            if(!is_wp_error($response)){
                return wp_remote_retrieve_body($response);
            }
            return false;  

        }
        public static function get_string_between($string, $start, $end){
            $string = ' ' . $string;
            $ini = strpos($string, $start);
            if ($ini == 0) return '';
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            return substr($string, $ini, $len);
        }
        
        
        public static function getSettings(){
            $settings=get_option('woocommerce_retrieval_live_settings');
            return [
                'enabled'=>$settings['enabled']=='yes' ? 'yes' : 'no',

            ];
        }
        public static function getLocations(){
            $allLocations=get_transient('wrl_locations');
            if(empty($allLocations)){
                $locationsData=self::getRemoteData('getLocations', 'GET', []);
                $allLocationsArray = json_decode($locationsData, true);
                if (!empty($allLocationsArray['locations'])) {
                    $allLocations = $allLocationsArray['locations'];
                    set_transient('wrl_locations',$allLocations,60 * 10);
                }
            }
            return $allLocations;
        }
        public static function install(){
            $wrlToken=get_option('wrl_api_token');
            if(empty($wrlToken)){
         
              add_option( 'wrl_activation_redirect', true );

            }
        }
    }
    register_activation_hook( __FILE__, ['WC_Retrieval_Live', 'install' ]);
    new WC_Retrieval_Live();
}

