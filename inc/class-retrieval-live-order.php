<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Retrieval_Live_Order' ) ) {
    class Retrieval_Live_Order{
        public function __construct() {
            //add_action( 'woocommerce_checkout_create_order_line_item',     [ $this, 'link_order_line_item_to_package' ], 10, 2 );
            add_action( 'woocommerce_checkout_create_order_shipping_item', [ $this, 'set_order_shipping_item_pickup_data' ], 11, 4 );
            add_action('woocommerce_checkout_update_order_meta',[&$this,'add_location_data']);
            add_action( 'woocommerce_order_details_after_order_table_items', [ $this, 'add_order_pickup_data' ], 5, 1 );
            add_action( 'woocommerce_email_after_order_table',               [ $this, 'add_order_pickup_data_email' ], 5, 3 );
            add_filter( 'woocommerce_order_hide_shipping_address', [&$this, 'hide_order_shipping_address' ] );
            add_filter( 'woocommerce_order_shipping_method', [ &$this, 'filter_shipping_method_labels' ], 10, 1 );
            add_action('woocommerce_after_order_itemmeta',[&$this,'woocommerce_after_order_itemmeta'],10,3);
            add_action('woocommerce_checkout_order_processed',[&$this,'post_data_to_server'],10,1);
        }
        public function post_data_to_server($order_id){
            $locations=$_POST['wrl_post_to_server_location'];
           
            if ( ! empty( $_POST['wrl_post_to_server'] ) && is_array( $_POST['wrl_post_to_server'] ) ) {
                $items=$_POST['wrl_post_to_server'];
                foreach ($items as $productId=>$qty){
                    $productId=intval($productId);
                    $sku=get_post_meta($productId,'_wrl_product_sku',true);
                    $name=get_post_meta($productId,'_wrl_product_name',true);

                    $location=sanitize_text_field($locations[$productId]);
                    $siteTitle=get_bloginfo('name');
                    $product=wc_get_product($productId);
                    $order=wc_get_order($order_id);
                    $price=$product->get_price();
                    $postedData=[
                        "sku" => $sku,
                        "quantity"    => sanitize_text_field($qty),
                        "location"     => $location,
                        "product_name"=> $name,
                        'product_price'=>$price,
                        'store_name'=>$siteTitle,
                        'order_number'=>$order_id,
                        'contact_email'=>$order->get_billing_email(),
                        'total_price'=>floatval($price) * intval($qty)
                    ];
                    $remoteRequest=WC_Retrieval_Live::postRemoteData('orderDetailWoocommerce',
                        $postedData
                    );
                   
                }
            }
        }
        public  function woocommerce_after_order_itemmeta($item_id,$item,$return){
        
        }
        public function add_location_data($order_id){
            if ( ! empty( $_POST['wrl_pickup_items'] ) && is_array( $_POST['wrl_pickup_items'] ) ) {
                    $pickupItems=(array) $_POST['wrl_pickup_items'];
                    $pickupItems=array_map( 'sanitize_text_field',$pickupItems );
                    $pickupItems=array_map( 'esc_attr',$pickupItems );
                    update_post_meta($order_id,'_wrl_pickup_items',$pickupItems);
            }
            if ( ! empty( $_POST['_wrl_shipping_method_pickup_location'] ) && is_array( $_POST['_wrl_shipping_method_pickup_location'] ) ) {
                    $pickupLocations=(array) $_POST['_wrl_shipping_method_pickup_location'];
                    $pickupLocations=array_map( 'sanitize_text_field',$pickupLocations );
                    $pickupLocations=array_map( 'esc_attr',$pickupLocations );
                    update_post_meta($order_id,'_wrl_pickup_locations',$pickupLocations);
            }
        }
        public function  add_order_pickup_data($order){
            $settings=get_option('woocommerce_retrieval_live_settings');

            $title=!empty($settings['title']) ? $settings['title'] : esc_attr__( 'Retrieval Live Pickup', 'retrieval' );
            $orderLocations=get_post_meta($order->get_id(),'_wrl_pickup_locations',true);
            $pickupItems=get_post_meta($order->get_id(),'_wrl_pickup_items',true);
            if(empty($orderLocations) || empty($pickupItems)) return false;
            ?>
            <tr class="wc-retrieval-live">
                <th><?php echo esc_attr($title); ?>:</th>
                <td>
                <?php
                foreach ($orderLocations as $k=>$orderLocation){
                    ?>
                    <div>
                        <strong><?php esc_attr_e('Pickup Location','retrieval')?>:</strong> <small><?php esc_attr_e($orderLocation) ?></small>
                    </div>
                    <div>
                        <strong><?php esc_attr_e('Pickup Items','retrieval')?>:</strong> <small><?php esc_attr_e($pickupItems[$k]) ?></small>
                    </div>
                 <?php
                }
                ?>
                </td>
            </tr>
            <?php
        }
        public function  add_order_pickup_data_email($order,$sent_to_admin = false, $plan_text = false){
            $settings=get_option('woocommerce_retrieval_live_settings');

            $title=!empty($settings['title']) ? $settings['title'] : esc_attr__( 'Retrieval Live Pickup', 'retrieval' );
            $orderLocations=get_post_meta($order->get_id(),'_wrl_pickup_locations',true);
            $pickupItems=get_post_meta($order->get_id(),'_wrl_pickup_items',true);
            if(empty($orderLocations) || empty($pickupItems)) return false;
            ?>

                <h3><?php echo esc_attr($title); ?>:</h3>
                <div>
                    <?php
                    foreach ($orderLocations as $k=>$orderLocation){
                        ?>
                        <div>
                            <strong><?php esc_attr_e('Pickup Location','retrieval')?>:</strong> <small><?php esc_attr_e($orderLocation) ?></small>
                        </div>
                        <div>
                            <strong><?php esc_attr_e('Pickup Items','retrieval')?>:</strong> <small><?php esc_attr_e($pickupItems[$k]) ?></small>
                        </div>
                        <?php
                    }
                    ?>
                </div>

            <?php
        }
        public function link_order_line_item_to_package( $order_item, $cart_item_key ) {

            if ( ! empty( $_POST['wrl_pickup_items'] ) && is_array( $_POST['wrl_pickup_items'] ) ) {

                $cart_item_keys = [];

                foreach ( $_POST['wrl_pickup_items'] as $package_key => $item_keys ) {

                    // we always ensure this is an array
                    $item_keys = explode( ',', $item_keys );

                    foreach ( $item_keys as $item_key ) {
                        // prefixing the package key with a string is a conservative workaround to prevent index oddities with index key 0 and data type handling in PHP (so we are sure these are strings now)
                        $cart_item_keys[ trim( $item_key ) ] = "package_{$package_key}";
                    }
                }

                if ( isset( $cart_item_keys[ $cart_item_key ] ) ) {
                    // this sets the meta value as "package_{$package_key}"
                    $order_item->update_meta_data( '_wrl_pickup_package_key', sanitize_text_field($cart_item_keys[ $cart_item_key ]));
                }
            }

        }
        public function set_order_shipping_item_pickup_data( $shipping_item,$package_key,$package,$order ) {

            if ( isset( $_POST['_wrl_shipping_method_pickup_location'][ $package_key ] )  && isset( $_POST['wrl_pickup_items'][ $package_key ] )) {

             

                $shipping_item->update_meta_data( esc_attr__('Location','retrieval'), sanitize_text_field($_POST['_wrl_shipping_method_pickup_location'][ $package_key ]));
                $shipping_item->update_meta_data( esc_attr__('Items','retrieval'), sanitize_text_field($_POST['wrl_pickup_items'][ $package_key ]));

            }
        }
        public function hide_order_shipping_address( $hidden_address_shipping_methods ) {

            $hidden_address_shipping_methods[] = 'retrieval_live';

            return $hidden_address_shipping_methods;
        }
        public function filter_shipping_method_labels( $labels_string ) {
            $array_labels    = explode( ', ', wp_strip_all_tags( $labels_string ) );

            return implode( ', ', array_unique( $array_labels ) );

        }
    }
    new Retrieval_Live_Order();
}