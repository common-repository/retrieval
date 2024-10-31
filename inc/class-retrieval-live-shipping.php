<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Retrieval_Live_Pickup' ) ) {
    class Retrieval_Live_Pickup extends WC_Shipping_Method {
        public $token="";
        public $url="";
        public $itemSelection="";
        public function __construct() {
            $this->id  = 'retrieval_live'; 
            $this->method_title = __( 'Retrieval Pickup', 'retrieval' );  
            $this->method_description = __( 'Pickup with Retrieval Live', 'retrieval' ); 
            $this->init();
            $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
            $this->title = isset( $this->settings['title'] ) ? esc_attr($this->settings['title']) : esc_attr__( 'Retrieval Live Pickup', 'retrieval' );        
        }
        public function init() {
         
            $this->init_form_fields(); 
            $this->init_settings(); 

           
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }
        public function init_form_fields() {
            $this->form_fields=[
                'enabled' => [
                    'title' => esc_attr__( 'Enable', 'retrieval' ),
                    'type' => 'checkbox',
                    'description' => esc_attr__( 'Enable this pickup.', 'retrieval' ),
                    'default' => 'yes'
                ],
                'title' => [
                    'title' => esc_attr__( 'Title', 'retrieval' ),
                      'type' => 'text',
                      'description' => esc_attr__( 'Title to be display on site', 'retrieval' ),
                      'default' => esc_attr__( 'Retrieval Pickup', 'retrieval' )
                ]

            ];
        }
        public function calculate_shipping( $packages=[] ) {
            $this->add_rate( [
                'id'       => $this->id,      // default value (the method ID)
                'label'    => $this->title, // this might include a discount notice the customer will understand
                'cost'     => 0,       // if there's a discount, dot not set a negative fee, later we will register a separate fee item as discount
                'taxes'    => false,   // default values (taxes will be automatically calculated)
                'calc_tax' => 'per_order',                 // applies to pickup package as a whole, regardless of items to be picked up
            ]);
        }
        
    }
}