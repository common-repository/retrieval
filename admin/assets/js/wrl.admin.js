jQuery(function($){
    const convertFormToJSON=function(form){
        return $(form)
        .serializeArray()
        .reduce(function (json, { name, value }) {
          json[name] = value;
          return json;
        }, {});
    }
    $(document).on('click','.wrlValidate',function(e){
        e.preventDefault();
       let self=$(this),
            sku=$('#_wrl_product_sku').val(),
            name=$("#_wrl_product_name").val(),
            selfText=self.text();
         if(sku=='' || name=='' || self.hasClass('saving')) return false;
         self.text(wrl_vars.validate_text).addClass('saving');
         var data = {
              'action': 'wrl_validate_product',
              'sku':sku,
              'name':name,
              'nonce': wrl_vars.nonce
              };
              $.post(wrl_vars.ajax_url, data).done(function (response){
                console.log(response);
                $.alert({
                    title: response.success ? wrl_vars.validTitle : wrl_vars.invalidTitle,
                    content: response.data.msg,
                    type: response.success ? 'green' : 'red',
                    useBootstrap : false,
                    boxWidth : '30%'
                });
                self.text(selfText);
                self.removeClass('saving');
              }).fail(function(xhr, status, error) {
                  self.text(selfText);
                  self.removeClass('saving');

              })
    });
    let windowWidth=$(window).width();
    $(window).resize(function(){
        windowWidth=$(window).width();
    });
    $(document).on('click','#wrlShowRegister',function(e){
        e.preventDefault();
        $('#wrlRegisterContainer').show();
        $(':input','#frmWrlRegister')
        .not(':button, :submit, :reset, :hidden')
        .val('')
        .prop('checked', false)
        .prop('selected', false);
        $('#wrlLoginContainer').hide();
    });
    $(document).on('click','#wrlShowLogin',function(e){
        e.preventDefault();
        $('#wrlLoginContainer').show();
        $(':input','#frmWrlRegister')
        .not(':button, :submit, :reset, :hidden')
        .val('')
        .prop('checked', false)
        .prop('selected', false);
        $('#wrlRegisterContainer').hide();
    });
    $(document).on('submit','#frmWrlRegister',function(e){
        e.preventDefault();
        const form = $(e.target);
        const jsonData = convertFormToJSON(form);
        var data={
            action:'wrl_submit_register_data',
            formData:jsonData,
            security:wrl_vars.nonces.register
        };
        $("#wrlLoader").show();
        $.post(wrl_vars.ajax_url,data).done(function(response){
      
           
            $("#wrlLoader").hide();
          
            if(response.success){
            $.alert({
                title: wrl_vars.successText,
                content:  response.data.msg,
                useBootstrap:false,
                boxWidth:  windowWidth > 760 ? '30%' : '90%',
                type: 'green'
            });
            $('#wrlShowLogin').trigger('click');
            }else{
                $.alert({
                    title: wrl_vars.errorText,
                    content:  response.data.msg,
                    useBootstrap:false,
                    boxWidth:  windowWidth > 760 ? '30%' : '90%',
                    type: 'red'
                });
            }

        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
    $(document).on('submit','#frmWrlLogin',function(e){
        e.preventDefault();
        const form = $(e.target);
        const jsonData = convertFormToJSON(form);
        var data={
            action:'wrl_submit_login_data',
            formData:jsonData,
            security:wrl_vars.nonces.login
        };
        $("#wrlLoader").show();
        $.post(wrl_vars.ajax_url,data).done(function(response){
            $("#wrlLoader").hide();
            if(response.success){
                location.reload();
            }else{
                $.alert({
                    title: wrl_vars.errorText,
                    content:  response.data.msg,
                    useBootstrap:false,
                    boxWidth:  windowWidth > 760 ? '30%' : '90%',
                    type: 'red'
                });
            }
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
    $(document).on('click','.wrl-logout-button',function(e){
        e.preventDefault();
        $("#wrlLoader").show();
        var data={
            action:'wrl_logout',
            security:wrl_vars.nonces.logout
        };
        $.post(wrl_vars.ajax_url,data).done(function(response){
            if(response.success){
                location.reload();  
            }else{
                $("#wrlLoader").hide();
            }
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
    $(document).on('change','.wrlCityCheck',function(e){
        var self=$(this);
       
        var inputNumber=self.closest('.city-checkbox-container').find('.city-input-container');
        if(self.is(':checked')){
            $(inputNumber).show();
            $(inputNumber).find('input').prop('required',true);
        }else{
            console.log( $(inputNumber));
            $(inputNumber).hide();
            $(inputNumber).find('input').prop('required',false);
        }
    });
    var loadWrlProducts=function(){
        
        var data={
            action:'wrl_load_product_table',
            security:wrl_vars.nonces.load_product
        };
       
        $.post(wrl_vars.ajax_url,data).done(function(response){
            console.log(response);
            if(response.success){
                $('.wrlProductTable').html(response.data.html);
            }
        });
    };
    if(typeof wrlProductPage!='undefined'){
        loadWrlProducts();
    }
    $(document).on('change','#wrl-woo-product',function(){
        $("#wrlLoader").show();
        var data={
            action:'wrl_populate_product_data',
            pid:$(this).val(),
            security:wrl_vars.nonces.product_data
        };
        $.post(wrl_vars.ajax_url,data).done(function(response){
            if(response.success){
               $('#wrl-product-title').val(response.data.title);
               $('#wrl-product-sku').val(response.data.sku);
            }else{
                if(typeof response.data.msg!=='undefined'){
                    alert(response.data.msg);
                    $('#wrl-woo-product').val('');
                }
            }
                $("#wrlLoader").hide();
            
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
    $(document).on('submit','.wrlProductForm',function(e){
        e.preventDefault();
        if ($('.wrlCityCheck').filter(':checked').length < 1){
            $.alert({
                title: wrl_vars.errorText,
                content:  wrl_vars.checkbox_msg,
                useBootstrap:false,
                boxWidth:  windowWidth > 760 ? '30%' : '90%',
                type: 'red'
            });
            return;
        }

        const form = $(e.target);
        const jsonData =convertFormToJSON(form);
     
        $("#wrlLoader").show();
        var data={
            action:wrlProductAction,
            formData:jsonData,
            security:wrl_vars.nonces.product_add
        };
        $.post(wrl_vars.ajax_url,data).done(function(response){
            $('.wrlCityInput').prop('required',false);
            $('.wrlProductForm')[0].reset();
            wrlProductAction='wrl_add_product';
            $('.city-input-container').hide();
            $('#wrl-woo-product').prop('required',true);
            $('#wrl-woo-product').show();
            $('#wrl-product-sku').show();
            $('#wrl-woo-product').prev().show();
            $('#wrl-product-sku').prev().show();
            if(response.success){
                $.alert({
                    title: wrl_vars.successText,
                    content: typeof response.data.msg!='undefined' ? response.data.msg :'',
                    useBootstrap:false,
                    boxWidth:  windowWidth > 760 ? '30%' : '90%',
                    type: 'green'
                });

                loadWrlProducts();
            }else{
                $.alert({
                    title: wrl_vars.errorText,
                    content: typeof response.data.msg!='undefined' ? response.data.msg :'',
                    useBootstrap:false,
                    boxWidth:  windowWidth > 760 ? '30%' : '90%',
                    type: 'red'
                });
            }
            $("#wrlLoader").hide();
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    })
    $(document).on('click','.wrl_delete',function(e){
        e.preventDefault();
        var productId=$(this).data('id');
      
        var cnf=confirm(wrl_vars.delete_msg);
        if(!cnf)return false;
        $("#wrlLoader").show();
        var data={
            action:'wrl_delete_product',
            pid:productId,
            security:wrl_vars.nonces.product_delete
        };
        $.post(wrl_vars.ajax_url,data).done(function(response){
            if(response.success){
                $.alert({
                    title: wrl_vars.successText,
                    content: typeof response.data.msg!=='undefined' ? response.data.msg :'',
                    useBootstrap:false,
                    boxWidth:  windowWidth > 760 ? '30%' : '90%',
                    type: 'green'
                });
                loadWrlProducts();
            }
            $("#wrlLoader").hide();
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
    $(document).on('click','.wrl_edit',function(e){
        e.preventDefault();
        var productId=$(this).data('id');
        $("#wrlLoader").show();
        var data={
            action:'wrl_edit_product',
            pid:productId,
            security:wrl_vars.nonces.product_edit
        };
        $.post(wrl_vars.ajax_url,data).done(function(response){
            
            if(response.success){
                $('.wrlCityCheck').prop('checked',false).trigger('change');
                $('.wrlCityInput').val('');
                wrlProductAction='wrl_update_product';
                $('#wrl-product-title').val(response.data[0].title);
                $('#wrl-product-sku').val(response.data[0].sku);
                $('#wrl-product-volume').val(response.data[0].volume);
                $('#wrl_product_edit_id').val(response.data[0].id);
                $('#wrl-woo-product').prop('required',false);
                $('#wrl-woo-product').hide();
                $('#wrl-product-sku').hide();

                $('#wrl-woo-product').prev().hide();
                $('#wrl-product-sku').prev().hide();
                if(!$.isEmptyObject(response.data[0].quantity)){
                    $.each(response.data[0].quantity,function(k,v){
                   
                        $('.wrlCityCheck[value="'+v.address+'"]').prop('checked',true).trigger('change');
                    
                      $('.wrlCityCheck[value="'+v.address+'"]').closest('.city-checkbox-container').find('.wrlCityInput').val(v.quantity);
                    })
                }
               
            }
            $("#wrlLoader").hide();
        }).fail(function(){
            $("#wrlLoader").hide();
        })
    });
});