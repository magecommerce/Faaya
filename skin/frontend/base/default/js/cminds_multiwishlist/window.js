/**
 * Created by Waldemar on 2017-01-18.
 */
function ShowWishlistPopup(url, productId){
    var win = new Window({
        zIndex:3000,
        destroyOnClose: true,
        recenterAuto:false,
        resizable: false,
        width:450,
        height:450,
        minimizable: false,
        maximizable: false,
        draggable: false,
        onClose : function() {
            $('popup-background').remove();
            $('cminds-wishlist-popup').hide()
        }
    });
    win.setContent(url, false, false);
    $('multiwishlist-product-id').value = productId;
    var background = new Element('div', { id : 'popup-background', 'style': "display: none;z-index: 1000;left: 0px;width: 100%;height: 2500px;background: rgb(0,0,0);opacity: 0.5;margin-left: 0px;margin-top: 0px;position: absolute;top: 0;display: table;"});
    document.body.appendChild(background);
    win.showCenter();
    event.preventDefault();
}
