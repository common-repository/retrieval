jQuery(function ($){
   $(document).on('click','.wrlPickupButton',function (e){
      e.preventDefault();
      let self=$(this),
          shopTable=self.closest('.shop_table');
          selectbox=self.prev();
      if(self.hasClass('showing')){
         let data={
            action: "wrl_remove_location_from_session",
            cartKey:self.data('key'),
            security:wrl_vars.nonce
         };
         shopTable.block({
            message: null,
            overlayCSS: {
               background: "#fff",
               opacity: .6
            }
         });
         $.post(wrl_vars.ajax_url, data).done(function (response){
            if(response.success){
               selectbox.val('');
               self.removeClass('showing');
               selectbox.addClass('wrl_hide');
               self.text(wrl_vars.selectText);
               $("[name='update_cart']").removeAttr('disabled');
               jQuery("[name='update_cart']").trigger("click");

            }
         }).always(function(){
            shopTable.unblock();
         });

      }else{
         selectbox.removeClass('wrl_hide');
         self.addClass('showing');
         self.text(wrl_vars.cancelText);
      }
   });


   $(document).on('change','.wrlAddressPicker',function(){
      let selectedValue=$(this).val(),
          self=$(this),
          shopTable=self.closest('.shop_table');
      if(selectedValue!=''){
         shopTable.block({
            message: null,
            overlayCSS: {
               background: "#fff",
               opacity: .6
            }
         });
         let data={
            action: "wrl_add_location_to_session",
            productId:self.data('id'),
            cartKey:self.data('key'),
            location:selectedValue,
            qty:self.data('qty'),
            security:wrl_vars.nonce
         };
         $.post(wrl_vars.ajax_url, data).done(function (response){

            if(!response.success){
               $.alert({
                  title: wrl_vars.invalidTitle,
                  content: response.data.msg,
                  type: 'red',
                  useBootstrap : false,
                  boxWidth : '30%'
               });
               self.val('');
            }else{
               $( document.body ).trigger( 'wc_fragment_refresh' );
               jQuery("[name='update_cart']").trigger("click");

            }
         }).always(function(){
            shopTable.unblock();
         });
      }
   });
   $('.woocommerce-shipping-totals').each(function (){

      if($(this).find("input.shipping_method").val()=='retrieval_live'){
         $(this).find('.woocommerce-shipping-destination').hide();
         $(this).find('.woocommerce-shipping-calculator').hide();
      }
   });
   $( document.body ).on( 'updated_cart_totals', function(){
      $('.woocommerce-shipping-totals').each(function (){

         if($(this).find("input.shipping_method").val()=='retrieval_live'){
            $(this).find('.woocommerce-shipping-destination').hide();
            $(this).find('.woocommerce-shipping-calculator').hide();
         }
      });
   })

   });