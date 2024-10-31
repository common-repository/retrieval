<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Retrieval_Live_Cart' ) ) {
    class Retrieval_Live_Cart{
        public static  $retrieve_live_id='retrieval_live';
        private static $pickup_package_form_output = [];
        private static $packages_count_output = false;
        public function __construct() {
            add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'handle_packages' ], 1 );
            add_action( 'woocommerce_get_item_data', [ $this, 'add_cart_item_pickup_location_field' ], 999, 2 );
            add_action( 'woocommerce_cart_emptied', array( $this, 'clear_session_data' ) );
            add_action('woocommerce_after_shipping_rate',[&$this,'output_pickup_package_form'],999,2);
            add_filter('woocommerce_shipping_package_name',[&$this,'rename_shipping_method_title'],10,3);
            add_action( 'woocommerce_cart_is_empty', [ $this, 'clear_session_data' ] );
            add_action('woocommerce_remove_cart_item',[&$this,'remove_location_data_from_cart'],10,2);
        }

        public function handle_packages($packages){
            $pluginSettings=WC_Retrieval_Live::getSettings();

            if ( ! empty( $packages ) && $pluginSettings['enabled'] == 'yes') {
                $ship_items=[];
                $pick_items=[];
                $cart_items=WC()->cart->cart_contents;
                $sessionKey='wrl_cart_items';
                $session_data = WC()->session->get( $sessionKey,[] );
                $index=0;
                $new_packages=[];

                foreach ($cart_items as $cart_item_key => $cart_item){
                    if ( $cart_item['data'] instanceof \WC_Product && ! $cart_item['data']->needs_shipping() ) {
                        continue;
                    }
                    if(isset($session_data[$cart_item_key])){
                        $pick_items[$cart_item_key]=$cart_item;
                        $pick_items[$cart_item_key]['location']=$session_data[$cart_item_key];
                    }else{
                        $ship_items[$cart_item_key]=$cart_item;
                    }
                }

                if(!empty($pick_items)){

                    $same_pickup_locations=[];
                    foreach ($pick_items as $item_key=>$pick_item){
                        $location=$pick_item['location'];
                        $same_pickup_locations[(string) $location][$item_key]=$pick_item;
                    }

                    foreach ($same_pickup_locations as $location=>$pickup_items_array){
                        
                        $new_packages[ $index ]=$this->create_package($pickup_items_array);
                        $new_packages[$index]['location']=$location;
                        $new_packages[ $index ]['ship_via'] = [ self::$retrieve_live_id ];
                        $index++;
                    }

                }
                if ( ! empty( $ship_items ) ) {

                    // the index value here right one unit above the last pickup package, so the shipping package will be always the last package
                    $new_packages[ $index ] = $this->create_package( $ship_items);

                    // also wipe pickup data from session for this package
                   // wc_local_pickup_plus()->get_session_instance()->delete_package_pickup_data( $index );
                }

                $packages = $new_packages;
            }
            return $packages;
        }
        private  function create_package( $items ) {
          //  print_r($items);
            return [
                'contents'        => $items,
                'contents_cost'   => array_sum( wp_list_pluck( $items, 'line_total' ) ),
                'applied_coupons' => WC()->cart->get_applied_coupons(),
                'user'            => [
                    'ID' => get_current_user_id(),
                ],
                'destination'     => $this->get_package_destination_address(
                    [
                        'country'   => WC()->customer->get_billing_country(),
                        'state'     => WC()->customer->get_billing_state(),
                        'postcode'  => WC()->customer->get_billing_postcode(),
                        'city'      => WC()->customer->get_billing_city(),
                        'address'   => WC()->customer->get_billing_address(),
                        'address_2' => WC()->customer->get_billing_address_2(),
                    ],
                    [
                        'country'   => WC()->customer->get_shipping_country(),
                        'state'     => WC()->customer->get_shipping_state(),
                        'postcode'  => WC()->customer->get_shipping_postcode(),
                        'city'      => WC()->customer->get_shipping_city(),
                        'address'   => WC()->customer->get_shipping_address(),
                        'address_2' => WC()->customer->get_shipping_address_2(),
                    ]
                ),
                'cart_subtotal'   => WC()->cart->get_displayed_subtotal(),
            ];
        }
        private function get_package_destination_address( $billing_address, $shipping_address ) {

            // grab the locales so we can check if the state and/or postcode are required for this particular shipping country
            $locale = WC()->countries->get_country_locale();

            // assume state and postcode are provided
            $state_provided = $postcode_provided = true;

            // check if a specific rule is set for this country making the state not required; o/w check if state is provided
            if ( ! isset( $locale[ WC()->customer->get_shipping_country() ]['state']['required'] ) || $locale[ WC()->customer->get_shipping_country() ]['state']['required'] ) {
                $state_provided = '' !== $shipping_address['state'];
            }

            // check if a specific rule is set for this country making the postcode not required; o/w check if postcode is provided
            if ( isset( $locale[ WC()->customer->get_shipping_country() ]['postcode']['required'] ) && ! $locale[ WC()->customer->get_shipping_country() ]['postcode']['required'] ) {
                $postcode_provided = '' !== $shipping_address['postcode'];
            }

            $set_shipping_address = array_diff_assoc( $billing_address, $shipping_address );

            return ! empty( $set_shipping_address ) && $state_provided && $postcode_provided ? $shipping_address : $billing_address;
        }
        public function  add_cart_item_pickup_location_field($item_data, $cart_item){
            if ( isset( $cart_item['key'] ) && in_the_loop() && is_cart() ) {
            $cartKey=$cart_item['key'];
            $productId=intval($cart_item['product_id']);
            $quantity=$cart_item['quantity'];
            $wrlProductSku=get_post_meta($productId,'_wrl_product_sku',true);
            if(empty($wrlProductSku)) return $item_data;
                $sessionKey='wrl_cart_items';
                $session_data = WC()->session->get( $sessionKey,[] );
                $selectedLocation=$session_data[$cartKey] ?? '';
                $wrl_locations=WC_Retrieval_Live::getLocations();
                $postedData=[
                    "name" =>   sanitize_text_field(get_post_meta($productId,'_wrl_product_name',true)),
                    "sku"  =>    sanitize_text_field(get_post_meta($productId,'_wrl_product_sku',true)), 
                    "quantity"=>sanitize_text_field($quantity)
                ];
                $wrl_locations=WC_Retrieval_Live::postRemoteDataWithToken('getProductLocations',$postedData);
              
               $wrl_locations=json_decode($wrl_locations,true);
                $avilableLocations=[];
               if(!empty($wrl_locations['locations'])){
                $avilableLocations=$wrl_locations['locations'];
               }
            ?>
            <div class="wrlAddressPickerContainer">
                <select data-id="<?php esc_attr_e($productId) ?>" data-qty="<?php esc_attr_e($quantity) ?>" data-key="<?php esc_attr_e($cartKey) ?>"  class="wrlAddressPicker <?php echo empty($selectedLocation) ? "wrl_hide" : ""?>">
                    <option value=""><?php esc_attr_e('Select Address','retrieval')?></option>
           <?php if(!empty($avilableLocations)){
               $selectedLocation=$session_data[$cartKey] ?? '';
               foreach ($avilableLocations as $location){
                   $selected=$selectedLocation==$location ? "selected" : "";
            ?>
                   <option <?php esc_attr_e($selected) ?> value="<?php esc_attr_e($location) ?>"><?php esc_attr_e($location) ?></option>
                <?php
               }
           } ?>
                </select>
                <?php
                $buttonText=empty($selectedLocation) ? esc_attr__('Click here to choose pickup address','retrieval') :esc_attr__('Cancel Pickup','retrieval')
                ?>
                <a data-key="<?php esc_attr_e($cartKey) ?>" class="wrlPickupButton <?php echo !empty($selectedLocation) ? "showing": "" ?>" href="#"><?php esc_attr_e($buttonText)?></a>
            </div>
            <?php
            }
            return $item_data;
        }
        public function output_pickup_package_form( $shipping_rate, $package_index ) {
            //$package=$this->get_shipping_package($package_index);

            $is_local_pickup      = $shipping_rate === self::$retrieve_live_id || ( $shipping_rate instanceof \WC_Shipping_Rate && $shipping_rate->method_id === self::$retrieve_live_id );
            if ( $is_local_pickup && ! array_key_exists( $package_index, self::$pickup_package_form_output ) ) {
                self::$pickup_package_form_output[ $package_index ] = true;

                $packageData=WC()->shipping()->get_packages()[$package_index];
                $location=$packageData['location'] ?? null;
                if(!empty($location)){
                    ?>
                    <p><?php esc_attr_e($location) ?></p>
                    <input type="hidden" name="_wrl_shipping_method_pickup_location[<?php esc_attr_e($package_index) ?>]" value="<?php esc_attr_e($location) ?>">
                    <input
                            type="hidden"
                            name="wrl_pickup_items[<?php echo esc_attr( $package_index); ?>]"
                            value="<?php esc_attr_e($this->get_package_text($packageData)); ?>"
                            data-pickup-object-id="<?php echo esc_attr( $package_index ); ?>"
                    />
                    <?php
                        $packageContents=$packageData['contents'];
                        if(!empty($packageContents) && is_array($packageContents)){
                            $items=[];
                            foreach ($packageContents as $packageContent){
                                $productId=$packageContent['product_id'];
                                $product=wc_get_product($productId);
                                $items[]=$product->get_title(). ' x '. $packageContent['quantity'];
                                echo '<input type="hidden" name="wrl_post_to_server['.esc_attr($productId).']" value="'.esc_attr($packageContent['quantity']).'" />';
                                echo '<input type="hidden" name="wrl_post_to_server_location['.esc_attr($productId).']" value="'.esc_attr($location).'" />';
                            }

                           echo '<small>'.esc_attr(implode(',',$items)).'</small>';
                        }
                    ?>
                <?php
                }
            }
        }
        public static function get_package_text($packageData){
            $packageContents=$packageData['contents'];
            $items=[];
            if(!empty($packageContents) && is_array($packageContents)){
                $items=[];
                foreach ($packageContents as $packageContent){
                    $productId=$packageContent['product_id'];
                    $product=wc_get_product($productId);
                    $items[]=$product->get_title(). ' x '. $packageContent['quantity'];
                }

            }
            return implode(',',$items);
        }
        public static function get_cart_items($package){
            $items=[];
            if ( ! empty( $package['contents'] ) && is_array( $package['contents'] ) ) {
                foreach ( array_keys( $package['contents'] ) as $cart_item_key  ) {
                    $items[] = $cart_item_key;
                }
            }

            return $items;
        }

        public function get_shipping_package( $package_id = 0 ) {

            $packages = WC()->shipping()->get_packages();

            if ( empty( $packages ) ) {
                $packages = WC()->cart->get_shipping_packages();
            }

            return ! empty( $packages[ $package_id ] ) ? $packages[ $package_id ] : null;
        }
        public function  rename_shipping_method_title($text,$i,$package){
           // print_r($package);
            if(!empty($package['location'])){
                $text=esc_attr__('Local Pickup','retrieval').' '.($i > 0 ? $i :'');
            }
            return $text;
        }
        public function clear_session_data() {

            if ( WC()->session ) {

                WC()->session->set( 'wrl_cart_items', [] );
            }
        }
        public function remove_location_data_from_cart($cart_item_key, $cart){
            $sessionKey='wrl_cart_items';
            $session_data = WC()->session->get( $sessionKey,[] );
            if(!empty($session_data)){
                if(!empty($session_data[$cart_item_key])){
                    unset($session_data[$cart_item_key]);
                    WC()->session->set( $sessionKey, $session_data);
                }
            }
        }
        
    }
    new Retrieval_Live_Cart();
}