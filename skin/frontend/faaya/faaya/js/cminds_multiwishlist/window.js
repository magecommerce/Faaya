/**
 * Created by Waldemar on 2017-01-18.
 */
function ShowWishlistPopup(url, productId){
    var win = new Window({
        zIndex:3000,
        destroyOnClose: true,
        recenterAuto:false,
        resizable: false,
        width:613,
        height:585,
        minimizable: false,
        maximizable: false,
        draggable: false,
        onClose : function() {
            $('popup-background').remove();
            $('cminds-wishlist-popup').hide()
        }

    });
    
    var background = new Element('div', { id : 'popup-background', 'style': "display: none;z-index: 1000;left: 0px;width: 100%;height: 2500px;background: rgb(0,0,0);opacity: 0.5;margin-left: 0px;margin-top: 0px;position: absolute;top: 0;display: table;"});
    win.setContent(url, false, false);
    var ajaxurl = jQuery('#multiwishlist-ajax-url').val();
    $('multiwishlist-product-id').value = productId;
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: { pid:productId},
        success: function(data){
            data = JSON.parse(data);
            jQuery('.popup-right').html(data['img']);
            jQuery(".suggested").html("");
            for (var i = 0; i < data['name'].length; i++) {
                jQuery('.suggested').append('<li>' +data['name'][i]+ '</li>');
            }
            if(!data['loggedin']){
                jQuery(".loggedin").show();
            }else{
                jQuery(".loggedin").hide();
            }
            document.body.appendChild(background);
            win.showCenter();
        },
        error: function(xhr){
            data = JSON.parse(xhr.responseText);
            jQuery('.popup-right').html(data['img']);
            jQuery(".suggested").html("");
            for (var i = 0; i < data['name'].length; i++) {
                jQuery('.suggested').append('<li>' +data['name'][i]+ '</li>');
            }
            if(!data['loggedin']){
                jQuery(".loggedin").show();
            }else{
                jQuery(".loggedin").hide();
            }
            document.body.appendChild(background);
            win.showCenter();
        }
    })
    setTimeout(function(){ 
        equalheightnoRow('.multiwishlist_popup > div');    
     }, 3000);
 
    
    event.preventDefault();
}
