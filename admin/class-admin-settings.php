<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'WRL_Admin_Products' ) ) {
    class WRL_Admin_Products{
        public function __construct(){
            add_action('woocommerce_product_options_shipping',[&$this,'display_wrl_data']);
        }
        public static function display_wrl_data(){
            $wrlId=get_post_meta( get_the_ID(), '_wrl_product_id', true );
            $wrlSku=get_post_meta( get_the_ID(), '_wrl_product_sku', true );
            $wrlName=get_post_meta( get_the_ID(), '_wrl_product_name', true );
            if($wrlId && $wrlName && $wrlSku){
                ?>
                <div id="wrlLinkedData">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2"><h2><?php esc_attr_e( 'Retrieval Data', 'retrieval' ) ?></h2></td>

                    </tr>
                    <tr>
                        <th>
                           <?php esc_attr_e( 'ID', 'retrieval' ) ?>
                        </th>
                        <td>
                        <?php echo esc_attr($wrlId) ?>
                        </td>
                        
                    </tr>
                    <tr>
                        <th>
                           <?php esc_attr_e( 'SKU', 'retrieval' ) ?>
                        </th>
                        <td>
                        <?php echo esc_attr($wrlSku) ?>
                        </td>
                        
                    </tr>
                    <tr>
                        <th>
                           <?php esc_attr_e( 'Title', 'retrieval' ) ?>
                        </th>
                        <td>
                        <?php echo esc_attr($wrlName) ?>
                        </td>
                        
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <a data-id="<?php echo get_the_ID() ?>" href="#" class="button wrlUnlinkProduct">  <?php esc_attr_e( 'Unlink', 'retrieval' ) ?></a>
                        </td>
                    </tr>
                </table>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery(document).on('click','.wrlUnlinkProduct',function(e){
                            e.preventDefault(); 
                            let self=jQuery(this),
                                productId=self.data('id');
                                var data = {
                                'action': 'wrl_unlink_product',
                                'pid':productId,
                                'nonce': '<?php echo wp_create_nonce('wrl-unlink-nonce') ?>'
                                 };
                                 jQuery('#wrlLinkedData').html('')
                                 jQuery.post(ajaxurl, data).done(function (response){

                                 }).fail(function(xhr, status, error) {
               

                                })
                     
                        });
                    });
                </script>
                <?php
            }
        }
    }
    new WRL_Admin_Products();
}