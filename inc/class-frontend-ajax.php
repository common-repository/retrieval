<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'WRL_Frontend_Ajax' ) ) {
    class WRL_Frontend_Ajax{
        public function __construct() {
            add_action('wp_ajax_wrl_add_location_to_session',[&$this,'wrl_add_location_to_session']);
            add_action('wp_ajax_nopriv_wrl_add_location_to_session',[&$this,'wrl_add_location_to_session']);
            add_action('wp_ajax_wrl_remove_location_from_session',[&$this,'wrl_remove_location_from_session']);
            add_action('wp_ajax_nopriv_wrl_remove_location_from_session',[&$this,'wrl_remove_location_from_session']);
        }
        public function wrl_remove_location_from_session(){
            check_ajax_referer('wrl-nonce','security');
            $cartKey=sanitize_text_field($_POST['cartKey']);
            $sessionKey='wrl_cart_items';
            $session_data = WC()->session->get( $sessionKey,[] );
            if(!empty($session_data)){
                if(!empty($session_data[$cartKey])){
                    unset($session_data[$cartKey]);
                    WC()->session->set( $sessionKey, $session_data);
                }
            }
            wp_send_json_success();
        }
        public function wrl_add_location_to_session(){
            check_ajax_referer('wrl-nonce','security');
            $productId=intval($_POST['productId']);
            $cartKey=sanitize_text_field($_POST['cartKey']);
            $qty=intval($_POST['qty']);
            $location=sanitize_text_field($_POST['location']);
            $wrlSku=get_post_meta($productId,'_wrl_product_sku',true);
            $wrlName=get_post_meta($productId,'_wrl_product_name',true);
            $postedData=  [
                "address" => $location,
                "name"    => $wrlName,
                "sku"     => $wrlSku,
                "quantity"=> $qty
            ];
            $remoteCheck=WC_Retrieval_Live::postRemoteData('getProductAtLocation',$postedData);
           
            $remoteCheckArray=$remoteCheck ? json_decode($remoteCheck,true) : [];
            if(!empty($remoteCheckArray['message']) && $remoteCheckArray['message']=='Product is available'){
                $sessionKey='wrl_cart_items';
                $session_data = WC()->session->get( $sessionKey,[] );
                if(!empty($session_data)){
                    if(!empty($session_data[$cartKey])){
                        unset($session_data[$cartKey]);
                    }
                }
                WC()->session->set( $sessionKey, array_merge( $session_data, [ (string) $cartKey => $location ] ) );
                wp_send_json_success();
            }else{
                wp_send_json_error(['msg'=>esc_attr__('Product is not available at chosen location','retrieval')]);
            }
            exit;
        }
    }
    new WRL_Frontend_Ajax();
}