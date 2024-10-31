<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'WRL_Admin' ) ) {
    class WRL_Admin{
        public function __construct() {
            require_once(WRL_PLUGIN_ADMIN_DIR.'/class-admin-settings.php');
            require_once(WRL_PLUGIN_ADMIN_DIR.'/class-admin-ajax.php');

            add_action('admin_menu',[&$this, 'wrl_plugin_setup_menu']);

            add_action('admin_init',[&$this,'wrl_activation_redirect']);
         
        }
        public static function wrl_activation_redirect(){
            if ( get_option( 'wrl_activation_redirect', false ) ) {
             
                delete_option( 'wrl_activation_redirect' );
                wp_safe_redirect( admin_url( 'admin.php?page=wrl-options' ) );
                exit;
            }
        }
        public static function wrl_plugin_setup_menu(){
            $mainMenu=add_menu_page(
                esc_attr('Dashboard','retrieval'),
                 esc_attr('Retrieval','retrieval'),
                  'manage_options', 'wrl-options',
                   [__CLASS__,'wrl_settings_init'],
                   'data:image/svg+xml;base64,'.WC_Retrieval_Live::$menuIcon,
                   90 );
                   add_action( 'load-' . $mainMenu, [__CLASS__,'load_admin_scripts']);
                   $wrlToken=get_option('wrl_api_token');
                   if(!empty($wrlToken)){
                   $subMenu=add_submenu_page('wrl-options', esc_attr('Products','retrieval'),esc_attr('Products','retrieval') , 'manage_options', 'wrl-product-options', [__CLASS__,'wrl_product_init']);
                   add_action( 'load-' . $subMenu, [__CLASS__,'load_admin_scripts']);
                   }
        }
        public static function load_admin_scripts(){
            add_action( 'admin_enqueue_scripts', [__CLASS__,'admin_scripts'] );
        }
        public static function wrl_settings_init(){
          //  $wrlToken=get_option('wrl_api_token');
          //  print_r('pp');
            ?>
             <div class="wrl_admin_heading">
             <img src="<?php echo WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/img/logo.jpeg'?>" alt="<?php esc_attr_e('Retrieval','retrieval') ?>" />
             </div>  
             <div class="wrap wrl-wrap">
             <div id="wrlLoader" style="display: none;">
                <div class="loader"></div>
            </div>   
             <?php
                $isLoggedIn=esc_attr(get_option('wrl_login'));
             if($isLoggedIn){
                $wrlAccountName=esc_attr(get_option('wrl_loggedin_user'));
                ?>
              
                <p class="description">
                    <?php
                      printf(
                        esc_html__( 'You are currently logged into %s. Products from your Woocommerce store will be added into the
                        mentioned account.', 'retrieval' ),
                         sprintf(
                            '<b>%s</b>',
                            $wrlAccountName
                            ),
                    );
                     ?>
                </p>
                <p class="description">
                    <?php esc_html_e('If you wish to add your products to a different account, kindly logout and connect to a different account.','retrieval')?>
                </p>
                <div style="text-align: right;">
                <a class="button button-primary button-large wrl-logout-button" href="#"><?php esc_attr_e('Logout','retrieval')?></a>
               </div>
                
          <?php }else{ ?>
            <div id="wrlLoginContainer">
            <h3><?php esc_attr_e('Login with your Retrieval credentials','retrieval') ?></h3>
            <form action="#" class="pure-form pure-form-aligned" id="frmWrlLogin">
            <fieldset>
                    <div class="pure-control-group">
                     <label for="wrlLoginEmail"><?php esc_attr_e('Email','retrieval')?></label>
                     <input required type="email" autocomplete="email" id="wrlLoginEmail" class="pure-input-1-2" placeholder="<?php esc_attr_e('Email','retrieval')?>" name="email" />
                    </div>
                    <div class="pure-control-group">
                     <label for="wrlLoginPassword"><?php esc_attr_e('Password','retrieval')?></label>
                     <input required type="password" autocomplete="current-password" id="wrlLoginPassword" class="pure-input-1-2" placeholder="<?php esc_attr_e('Password','retrieval')?>" name="password" />
                    </div>
                    <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary"><?php esc_attr_e('Login','retrieval')?></button>
                    </div>
            </fieldset>
            </form>
                <p style="text-align: right;">
                <a id="wrlShowRegister" class="button" href="#"><?php esc_attr_e('Register Now','retrieval')?></a>
               </p>
            </div>
            <div id="wrlRegisterContainer" style="display: none;">
            <h3><?php esc_attr_e('Create a new account in Retrieval','retrieval') ?></h3>
            <form action="#" class="pure-form pure-form-aligned" id="frmWrlRegister">
                <fieldset>
                    <div class="pure-control-group">
                     <label for="wrlRegisterName"><?php esc_attr_e('Name','retrieval')?></label>
                     <input required type="text" id="wrlRegisterName" class="pure-input-1-2" placeholder="<?php esc_attr_e('Name','retrieval')?>" name="name" />
                    </div>
                    <div class="pure-control-group">
                     <label for="wrlRegisterEmail"><?php esc_attr_e('Email','retrieval')?></label>
                     <input required type="email" autocomplete="email" id="wrlRegisterEmail" class="pure-input-1-2" placeholder="<?php esc_attr_e('Email','retrieval')?>" name="email" />
                    </div>
                    <div class="pure-control-group">
                     <label for="wrlRegisterPassword"><?php esc_attr_e('Password','retrieval')?></label>
                     <input required type="password" autocomplete="new-password" id="wrlRegisterPassword" class="pure-input-1-2" placeholder="<?php esc_attr_e('Password','retrieval')?>" name="password" />
                    </div>
                    <div class="pure-control-group">
                     <label for="wrlRegisterCompany"><?php esc_attr_e('Company Name','retrieval')?></label>
                     <input required type="text" id="wrlRegisterCompany" class="pure-input-1-2" placeholder="<?php esc_attr_e('Compnay Name','retrieval')?>" name="company" />
                    </div>
                    <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary"><?php esc_attr_e('Signup','retrieval')?></button>
                    </div>
                </fieldset>   
            </form>
            <p style="text-align: right;">
                <a id="wrlShowLogin" class="button" href="#"><?php esc_attr_e('Login Now','retrieval')?></a>
               </p>   
            </div>
         <?php } ?>
          </div>
             
            <?php
        }
        public static function wrl_product_init(){
           
         
           $cities=WC_Retrieval_Live::getLocations();
        
            ?>
            <script type="text/javascript">
                var wrlProductPage=true;
                var wrlProductAction='wrl_add_product';
            </script>
            <div class="wrl_admin_heading">
             <img src="<?php echo WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/img/logo.jpeg'?>" alt="<?php esc_html_e('Retrieval','retrieval') ?>" />
             </div>  
            <div class="wrap wrl-wrap">
            <div id="wrlLoader" style="display: none;">
                <div class="loader"></div>
            </div>   
                <form class="wrlProductForm pure-form pure-form-stacked">
                    <?php if(!empty($cities)){
                        foreach($cities as $k=>$city){
                        ?>  
                        <div class="city-checkbox-container">
                            <label for="wrl-city-option-<?php esc_attr_e($k); ?>" class="pure-checkbox">
                            <input class="wrlCityCheck" type="checkbox" id="wrl-city-option-<?php esc_attr_e($k); ?>" value="<?php esc_attr_e($city); ?>" /> <?php esc_attr_e($city) ?>

                            </label>
                            <div class="city-input-container"  style="display: none;">
                            <label for="wrl-city-input-<?php esc_attr_e($city); ?>">
                            <?php
                            
                            esc_attr_e('Enter Quantity of Stock in this Market (in Units)','retrieval');
                             ?>
                            </label>
                            <input id="wrl-city-input-<?php esc_attr_e($k); ?>" placeholder="<?php  esc_attr_e('Stock (in Units)','retrieval'); ?>" type="number"  class="pure-input wrlCityInput" name="wrl-city{<?php echo esc_attr($city); ?>}" />
                            </div>
                         </div>
                      
                    
                   <?php }} ?>
                   <label for="wrl-woo-product">
                            <?php                             
                            esc_attr_e('Select Woocommerce Product','retrieval');
                            ?>
                        </label>
                        <select required id="wrl-woo-product" name="wrl-woo-product" style="height: auto;width:100%">
                            <option value="">---</option>
                        <?php 
                             $args = [
                                'post_type' => 'product',
                                'post_status' => 'publish',
                                'posts_per_page' => '-1',
                                'fields'=>['id','name']
                             ];
                             $products=wc_get_products($args);
                             if(!empty($products)){
                                foreach($products as $product){
                                   echo '<option value="'.esc_attr($product->id).'">'.esc_attr($product->name).'</option>';
                                }
                             }
                            ?>
                        </select>
                        <div class="pure-g">
                            <div class="pure-u-1 pure-u-md-1-2">
                                <label for="wrl-product-title">
                                <?php                             
                                     esc_attr_e('Product Title','retrieval');
                                    ?>
                                </label>
                                <input required style="width:90%" placeholder="<?php esc_attr_e('Product Title','retrieval'); ?>" type="text" name="wrl-product-title" id="wrl-product-title">
                            </div>
                            <div class="pure-u-1 pure-u-md-1-2">
                            <label for="wrl-product-sku">
                                <?php                             
                                     esc_attr_e('SKU Number','retrieval');
                                    ?>
                                </label>
                                <input required style="width:90%" placeholder="<?php esc_attr_e('SKU Number','retrieval'); ?>" type="text" name="wrl-product-sku" id="wrl-product-sku">

                            </div>
                        </div>
                        <label for="wrl-product-volume">
                                <?php                             
                                     esc_attr_e('Volume of One Individual Unit (in Cubic Feet)','retrieval');
                                    ?>
                                </label>
                                <input required placeholder="<?php esc_attr_e('Volume','retrieval'); ?>" type="text" name="wrl-product-volume" id="wrl-product-volume">
                                <input type="hidden" value="" name="wrl_product_edit_id" id="wrl_product_edit_id" />

                                <button type="submit" class="pure-button pure-button-primary"><?php esc_attr_e('Save Product','retrieval'); ?></button>

                </form>
                <div class="wrlProductTable">
                
                </div>
            </div>
            <?php
        }
        public static function admin_scripts(){
            wp_enqueue_style('wrl-pure-css', WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/css/pure-min.css', [],'2.0.6','all');
            wp_enqueue_style('wrl-pure-responsive', WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/css/grids-responsive-min.css',['wrl-pure-css'],'2.0.6','all');
            wp_enqueue_style('wrl-admin', WRL_PLUGIN_ABSOLUTE_PATH . 'admin/assets/css/wrl.admin.css', false);  
            wp_enqueue_style('wrl-jquery-confirm',WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/css/jquery-confirm.min.css',[],'3.3.2');
            wp_register_script( 'wrl_admin_main',WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/js/wrl.admin.js' , [], time(), true );
            wp_enqueue_script('wrl_jquery_confirm',WRL_PLUGIN_ABSOLUTE_PATH.'admin/assets/js/jquery-confirm.min.js',[],'3.3.2',true);
            wp_localize_script('wrl_admin_main','wrl_vars',[
                'ajax_url'=>admin_url('admin-ajax.php'),
                'validate_text'=>esc_attr__('Validating','retrieval'),
                'validTitle'=>esc_attr__('Valid','retrieval'),
                'successText'   =>  esc_attr__('Success','retrieval'),
                'errorText'   =>  esc_attr__('Error','retrieval'),
                'successText'   =>  esc_attr__('Success','retrieval'),
                'invalidTitle'=>esc_attr__('Invalid','retrieval'),
                'checkbox_msg'=>esc_attr__('Please select at least one location','retrieval'),
                'delete_msg'=>esc_attr__('Are you sure to delete this product?','retrieval'),
                'nonces'=>[
                   'login'      =>  wp_create_nonce('wrl-login-nonce'),
                   'register'   =>  wp_create_nonce('wrl-register-nonce'),
                   'logout'     =>  wp_create_nonce('wrl-logout-nonce'),
                   'load_product'=> wp_create_nonce('wrl-load-product-nonce'),
                   'product_data'=> wp_create_nonce('wrl-populate-product-nonce'),
                   'product_add'=> wp_create_nonce('wrl-add-or-edit-product-nonce'),
                   'product_delete'=> wp_create_nonce('wrl-product-delete-nonce'),
                   'product_edit'=> wp_create_nonce('wrl-product-edit-nonce')

                ]
            ]);
            wp_enqueue_script('wrl_admin_main');
        }
       
    }
    new WRL_Admin();
}