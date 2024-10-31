<?php
defined( 'ABSPATH' ) or exit;
if (!class_exists('WRL_Scripts_Styles')) {
    class WRL_Scripts_Styles{
        public function __construct(){
            add_action('wp_enqueue_scripts', [&$this, 'enqueue_styles']);
        }
        public function enqueue_styles(){
            if(is_cart() || is_checkout()){
                wp_enqueue_style('wrl-frontend', WRL_PLUGIN_ABSOLUTE_PATH . 'assets/css/frontend.cart.css', false);
                wp_enqueue_style('wrl-jquery-confirm',WRL_PLUGIN_ABSOLUTE_PATH . 'assets/css/jquery-confirm.min.css',[],'3.3.2');
                wp_enqueue_script('wrl_jquery_confirm',WRL_PLUGIN_ABSOLUTE_PATH.'assets/js/jquery-confirm.min.js',['jquery'],'3.3.2',true);
                wp_register_script( 'wrl_frontend_main',WRL_PLUGIN_ABSOLUTE_PATH.'assets/js/wrl.frontend.js' , [], '1.0.0', true );

                wp_localize_script('wrl_frontend_main','wrl_vars',[
                    'ajax_url'=>admin_url('admin-ajax.php'),
                    'validTitle'=>__('Valid','retrieval'),
                    'invalidTitle'=>__('Invalid','retrieval'),
                    'cancelText'=>__('Cancel Pickup','retrieval'),
                    'selectText'=>__('Click here to choose pickup address','retrieval'),
                    'searchLocationText'=>__('Search Location','retrieval'),
                    'nonce'=>wp_create_nonce('wrl-nonce')
                ]);
                wp_enqueue_script('wrl_frontend_main');
            }
        }
    }
    new WRL_Scripts_Styles();
}