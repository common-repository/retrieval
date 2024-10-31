<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Retrieval_Live_Checkout' ) ) {
    class Retrieval_Live_Checkout{
        private static $packages_count_output = false;
        public function __construct() {
            add_filter( 'woocommerce_shipping_package_details_array', [ $this, 'maybe_hide_pickup_package_item_details' ], 10, 2 );
            add_action( 'woocommerce_review_order_after_cart_contents', [ $this, 'packages_count' ], 40 );
            add_filter('woocommerce_shipping_packages',[&$this,'woocommerce_shipping_packages'],1);

        }
        public function maybe_hide_pickup_package_item_details( $item_details, $package ) {

            if ( ! empty( $package['location'] ) || ( isset( $package['ship_via'] ) && 'retrieval_live' ) ) {
                $item_details = [];
            }

            return $item_details;
        }
        public function packages_count() {
          
            $packages = WC()->shipping()->get_packages();
            if (    true !== self::$packages_count_output
                && is_checkout() && !empty($packages)) {
                $shipping_method_id = 'retrieval_live';
                $packages_to_ship   = 0;
                $packages_to_pickup = 0;
                foreach ( $packages as $package ) {
                    if ( isset( $package['ship_via'] ) && in_array( $shipping_method_id, $package['ship_via'], true ) ) {
                        $packages_to_pickup++;
                    } else {
                        $packages_to_ship++;
                    }
                }?>

                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input
                            type="hidden"
                            id="wrl-packages-to-ship"
                            value="<?php echo esc_attr($packages_to_ship); ?>"
                        />
                        <input
                            type="hidden"
                            id="wrl-packages-to-pickup"
                            value="<?php echo esc_attr($packages_to_pickup); ?>"
                        />
                    </td>
                </tr>
          <?php
                self::$packages_count_output = true;
            }
        }
        public function woocommerce_shipping_packages( $packages ) {
            $local_pickup_plus_id   = 'retrieval_live';
            $append_ship_only_items = [];
            $pickup_packages        = [];
            $pluginSettings=WC_Retrieval_Live::getSettings();
            if ( ! empty( $packages ) && $pluginSettings['enabled'] == 'yes') {
                $sessionKey='wrl_cart_items';
                $session_data = WC()->session->get( $sessionKey,[] );
                foreach ( $packages as $index => $package ) {
                    if ( ! isset( $package['ship_via'] ) && isset( $package['rates'][ $local_pickup_plus_id ] ) ) {
                        $package['ship_via']=[$local_pickup_plus_id];

                    }
                    $contents=$package['contents'];
                    if(!empty($contents)){
                        $pickedUpItems=[];
                        foreach ($contents as $k=>$content){
                            if(isset($session_data[$k])){
                                array_push($pickedUpItems,$k);
                            }
                        }
                        if(empty($pickedUpItems)){
                            unset( $packages[ $index ]['rates'][ $local_pickup_plus_id ] );
                        }
                    }

                }
            }
            return $packages;
        }
    }
    new Retrieval_Live_Checkout();
}