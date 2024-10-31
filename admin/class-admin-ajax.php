<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'WRL_Admin_Ajax' ) ) {
    class WRL_Admin_Ajax{
        public function __construct()
        {
            add_action('wp_ajax_wrl_submit_register_data',[&$this,'ajax_submit_register_data']);
            add_action('wp_ajax_wrl_submit_login_data',[&$this,'ajax_submit_login_data']);
            add_action('wp_ajax_wrl_logout',[&$this,'ajax_logout']);
            add_action('wp_ajax_wrl_load_product_table',[&$this,'ajax_load_product_table']);
            add_action('wp_ajax_wrl_populate_product_data',[&$this,'ajax_populate_product_data']);
            add_action('wp_ajax_wrl_add_product',[&$this,'ajax_add_product']);
            add_action('wp_ajax_wrl_delete_product',[&$this,'ajax_delete_product']);
            add_action('wp_ajax_wrl_edit_product',[&$this,'ajax_edit_product']);
            add_action('wp_ajax_wrl_update_product',[&$this,'ajax_update_product']);
            add_action('wp_ajax_wrl_unlink_product',[&$this,'ajax_unlink_product']);

        }
        public static function ajax_unlink_product(){
            check_ajax_referer( 'wrl-unlink-nonce', 'nonce' );
            $productId=intval($_POST['pid']);
            delete_post_meta($productId,'_wrl_product_id');
            delete_post_meta($productId,'_wrl_product_sku');
            delete_post_meta($productId,'_wrl_product_name');
            wp_send_json_success();
        }
        public static function ajax_update_product(){
            check_ajax_referer( 'wrl-add-or-edit-product-nonce', 'security' );
            $formData=$_POST['formData'];
            $postedData=[];
            if(!empty($formData) && is_array($formData)){
                foreach($formData as $k=>$eachData){
                    
                    if(strpos($k,'wrl-city') > -1 && !empty($eachData)){
                       
                        $locationName=trim(sanitize_text_field(WC_Retrieval_Live::get_string_between($k,'{','}')));
                        $postedData['locationStock'][]=[
                            'address'=>$locationName,
                            'quantity'=>sanitize_text_field($eachData)
                        ];
                    }
                }
            
                $postedData['title']=sanitize_text_field($formData['wrl-product-title']);
                $postedData['volume']=sanitize_text_field($formData['wrl-product-volume']);
                $postedData['sku']=sanitize_text_field($formData['wrl-product-sku']);
                $productId=sanitize_text_field($formData['wrl_product_edit_id']);
            }
            $endpoint='editProductApi/'.$productId;

            $productUpdated=WC_Retrieval_Live::postRemoteDataWithToken($endpoint,json_encode($postedData),'POST',true);
       
            if(!is_wp_error($productUpdated)){
                $productAddedArray=$productUpdated ? json_decode($productUpdated,true) : [];
                if(isset($productAddedArray['message']) && $productAddedArray['message']=='Product updated successfully!'){
                    wp_send_json_success(['msg'=>esc_attr($productAddedArray['message'])]);
                }
               }
               wp_send_json_error(['msg'=>esc_attr__('There are some error','retrieval')]);

        }
        public static function ajax_edit_product(){
            check_ajax_referer( 'wrl-product-edit-nonce', 'security' );
            $productId=intval($_POST['pid']);
            $endPoint='showProductApi/'.$productId;
            $editRequest=WC_Retrieval_Live::postRemoteDataWithToken($endPoint,[],'GET');
            wp_send_json_success(json_decode($editRequest,true));
        }
        public static function ajax_delete_product(){
            check_ajax_referer( 'wrl-product-delete-nonce', 'security' );
            $productId=intval($_POST['pid']);
            $endPoint='deleteProductApi/'.$productId;
            $deleteRequest=WC_Retrieval_Live::postRemoteDataWithToken($endPoint,[],'DELETE');
            if(!is_wp_error($deleteRequest)){
                $deleteRequestArray=$deleteRequest ? json_decode($deleteRequest,true) : [];
                if(isset($deleteRequestArray['message']) && $deleteRequestArray['message']=='Product deleted successfully.'){
                    $params = array(
                        'meta_query' => array(
                            array('key' => '_wrl_product_id', 
                                  'value' => strval($productId), 
                                  'compare' => '=',
                            )
                        ),  
                        'posts_per_page' => -1 
                    
                    );
                    $products =wc_get_products($params);
                  
                    if( !empty($products) ) {
                        foreach($products as $product ) {
                            delete_post_meta($product->get_id(),'_wrl_product_id');
                            delete_post_meta($product->get_id(),'_wrl_product_sku');
                            delete_post_meta($product->get_id(),'_wrl_product_name');
                        }
                    }
                   

                    wp_send_json_success(['msg'=>$deleteRequestArray['message']]);
                }
            }
            wp_send_json_error();
        }
        public static function ajax_add_product(){
            check_ajax_referer( 'wrl-add-or-edit-product-nonce', 'security' );
            $formData=$_POST['formData'];
            $postedData=[];
           
            if(!empty($formData) && is_array($formData)){
                $count=0;

                foreach($formData as $k=>$eachData){
                    
                    if(strpos($k,'wrl-city') > -1 && !empty($eachData)){
                       
                            $locationName=trim(sanitize_text_field(WC_Retrieval_Live::get_string_between($k,'{','}')));
                   
                        $adressString='locationStock['.$count.'][address]';
                        $quantityString='locationStock['.$count.'][quantity]';
                        $postedData[$adressString]=$locationName;
                        $postedData[$quantityString]=sanitize_text_field($eachData);
                           $count+=1;
                    }
                }
            
                $postedData['title']=sanitize_text_field($formData['wrl-product-title']);
                $postedData['volume']=sanitize_text_field($formData['wrl-product-volume']);
                $postedData['sku']=sanitize_text_field($formData['wrl-product-sku']);
            }


            $productAdded=WC_Retrieval_Live::postRemoteDataWithToken('addProductApi',($postedData));
          

           if(!is_wp_error($productAdded)){
            $productAddedArray=$productAdded ? json_decode($productAdded,true) : [];
            if(isset($productAddedArray['message'])){
                wp_send_json_error(['msg'=>$productAddedArray['message']]);
            }else{
                if(isset($productAddedArray['id'])){
                    $wooProductId=intval(esc_attr($formData['wrl-woo-product']));
                   
                    update_post_meta($wooProductId,'_wrl_product_id',sanitize_text_field($productAddedArray['id']));
                    update_post_meta($wooProductId,'_wrl_product_sku',sanitize_text_field($productAddedArray['sku']));
                    update_post_meta($wooProductId,'_wrl_product_name',sanitize_text_field($productAddedArray['title']));


                    wp_send_json_success(['msg'=>esc_attr__('Product added successfully.','retrieval')]);
                }
            }
           }
           wp_send_json_error(['msg'=>esc_attr__('There are some error','retrieval')]);

        }
        public static function ajax_populate_product_data(){
            check_ajax_referer( 'wrl-populate-product-nonce', 'security' );
            $productId=intval($_POST['pid']);
            $product=wc_get_product($productId);
            $checkId=get_post_meta($productId,'_wrl_product_id',true);
            if(!empty($checkId)){
                wp_send_json_error(['msg'=>esc_attr__('This product has been linked. You can unlink from Woocommerce Product edit page.','retrieval')]);
            }
            wp_send_json_success(['title'=>$product->get_title(),'sku'=>$product->get_sku()]);
        }
        public static function ajax_load_product_table(){
            check_ajax_referer( 'wrl-load-product-nonce', 'security' );
            $productRequest=WC_Retrieval_Live::getRemoteData('getUserProductsApi');
            $productArray=$productRequest ? json_decode($productRequest,true) : [];
          
            ob_start();
            ?>
                <?php if(!empty($productArray) && !empty($productArray[0])){ ?>
                    <table class="pure-table pure-table-horizontal" style="width:100%">
                        <thead>
                            <tr>
                                <th>
                                    <?php esc_attr_e('ID','retrieval') ?>
                                </th>
                                <th>
                                    <?php esc_attr_e('Title','retrieval') ?>
                                </th>
                                <th>
                                    <?php esc_attr_e('Market','retrieval') ?>
                                </th>
                                <th>
                                    <?php esc_attr_e('SKU','retrieval') ?>
                                </th>
                                <th>
                                    <?php esc_attr_e('Action','retrieval') ?>
                                </th>
                            </tr>
                            <tbody>
                                <?php foreach($productArray[0] as $product){
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            esc_attr_e($product['id'])
                                             ?>
                                        </td>
                                        <td>
                                            <?php
                                            esc_attr_e($product['title'])
                                             ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo esc_attr((implode(',',($product['market']))));
                                             ?>
                                        </td>
                                        <td>
                                            <?php
                                            esc_attr_e($product['sku'])
                                             ?>
                                        </td>
                                        <td>
                                            <a class="wrl_button wrl_edit" data-Id="<?php esc_attr_e($product['id'])?>" href="#" title="<?php esc_attr_e('Edit','retrieval')?>"><span class="dashicons dashicons-edit-page"></span></a>
                                            <a href="#" class="wrl_button wrl_delete" data-Id="<?php esc_attr_e($product['id'])?>" title="<?php esc_attr_e('Delete','retrieval')?>"><span class="dashicons dashicons-trash"></span></a>

                                        </td>
                                    </tr>
                               <?php } ?>
                            </tbody>
                        </thead>
                    </table>
                    <?php }else{ ?>
                      
                    <div class="notice notice-error">
                        <p class="description"><?php esc_attr_e('There is no product.','retrieval')?></p>
                    </div>
                    <?php  } ?>
            <?php
            wp_send_json_success(['html'=>ob_get_clean()]);
        }
        public static function ajax_logout(){
            check_ajax_referer( 'wrl-logout-nonce', 'security' );
            delete_option('wrl_login');
            delete_option('wrl_loggedin_user');
            delete_option('wrl_api_token');
            wp_send_json_success();
        }
        public static function ajax_submit_login_data(){
            check_ajax_referer( 'wrl-login-nonce', 'security' );
            $formData=$_POST['formData'];
        
          $postedData=[
            'email'     =>   sanitize_text_field($formData['email']),
            'password'  =>   sanitize_text_field($formData['password']),   
          ];
          $remoreResponse=WC_Retrieval_Live::postRemoteData('login',$postedData);
          $remoreResponseArray=$remoreResponse ? json_decode($remoreResponse,true) : [];
          if(!empty($remoreResponseArray['user'])){
            $accessToken=$remoreResponseArray['access_token'];

            $apiTokenRequest=WC_Retrieval_Live::generateApiToken($accessToken);
            $apiTokenArray=$apiTokenRequest ? json_decode($apiTokenRequest,true) : [];
          
            if(!empty($apiTokenArray['token'])){
            update_option('wrl_api_token',sanitize_text_field($apiTokenArray['token']));
            update_option('wrl_login',true);
            update_option('wrl_loggedin_user',sanitize_text_field($remoreResponseArray['user']['name']));
            wp_send_json_success();
            }
          }
          wp_send_json_error(
            [
                'msg'=>esc_attr__('Invalid Credentials.','retrieval')
            ]
            );
        }
        public static function ajax_submit_register_data(){
            check_ajax_referer( 'wrl-register-nonce', 'security' );
            $formData=$_POST['formData'];
            $postedData=[
                'name'              =>  sanitize_text_field($formData['name']),
                'email'             =>  sanitize_text_field($formData['email']),
                'password'          =>  sanitize_text_field($formData['password']),
                'confirm_password'  =>  sanitize_text_field($formData['password']),
                'role'              =>  'client',
                'company'           =>  sanitize_text_field($formData['company'])
            ];
            $remoreResponse=WC_Retrieval_Live::postRemoteData('signup',$postedData);
        //  print_r($remoreResponse);
            $remoreResponseArray=$remoreResponse ? json_decode($remoreResponse,true) : [];
          
           if(!empty($remoreResponseArray['message']) && $remoreResponseArray['message']=='Account created successfully'){
            wp_send_json_success(
                [
                    'msg'=>esc_attr__('Account created successfully','retrieval')
                ]
            );
           }
           wp_send_json_error(
            [
                'msg'=>esc_attr__('There is some issue with account creation. Please try again.','retrieval')
            ]
           );
          
        }
    }
    new WRL_Admin_Ajax();
}