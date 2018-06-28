

window.onload = function(){ 
    var i;
    try
    {
//get all href links 
        var links = document.links;

        for (i = 0; i < links.length; i++) {
            // login links  
            if (links[i].href.search('/customer/account/login/') != -1) {
                links[i].href = 'javascript:fme_sociallogin();';
            }
            if (links[i].href.search('/customer/account/login/referer/') != -1) {
                links[i].href = 'javascript:fme_sociallogin();';
            }
            // wishlist link
            if (links[i].href.search('/wishlist/') != -1) {
                 links[i].href = 'javascript:fme_sociallogin();';
            }
            // my account link
            if (links[i].href.search('/customer/account/') != -1) {
                 links[i].href = 'javascript:fme_sociallogin();';
            }
            // background fade element.
            if($('bg_fade') == null) {
			var screen = new Element('div', {'id': 'bg_fade'});
			document.body.appendChild(screen);
		}
        }

        /*
         * bind in checkout field. 
         */
        if (document.getElementById("checkout-step-login"))
        {
            $$('.col-2 .buttons-set').each(function(e) {
                e.insert({bottom: '<div id="multilogin"><button type="button" class="button" style="" onclick="javascript:fme_sociallogin();" title="Social Login" name="headerboxLink1" id="headerboxLink1"><span><span>Social Login</span></span></button></div>'});
            });
        }
         Event.observe('bg_fade', 'click', function () {
          fme_socialloginclose();
        });

    }

    catch (exception)
    {
        alert(exception);
    }

}


function fme_sociallogin()
{ 
//fade open  
$('bg_fade').setStyle({visibility: 'visible',opacity: 0});
new Effect.Opacity( 'bg_fade', { duration: .2, from: 0, to: 0.4 } );

       // Window width and height 
		if(typeof window.innerHeight != 'undefined') {
			document.getElementById('header_logo_Div').style.top = Math.round(document.body.offsetTop + ((window.innerHeight - document.getElementById('header_logo_Div').getHeight()))/2)+'px';
			document.getElementById('header_logo_Div').style.left = Math.round(document.body.offsetLeft + ((window.innerWidth - document.getElementById('header_logo_Div').getWidth()))/2)+'px';
		} else {
			document.getElementById('header_logo_Div').style.top = Math.round(document.body.offsetTop + ((document.documentElement.offsetHeight - document.getElementById('header_logo_Div').getHeight()))/2)+'px';
			document.getElementById('header_logo_Div').style.left = Math.round(document.body.offsetLeft + ((document.documentElement.offsetWidth - document.getElementById('header_logo_Div').getWidth()))/2)+'px';
		}
$('header_logo_Div').setStyle({display: 'block'});                                 

}


function fme_socialloginclose()
{

$('header_logo_Div').setStyle({display: 'none'});                               
//fade close                   
$('bg_fade').setStyle({visibility: 'hidden',opacity: 0});
new Effect.Opacity( 'bg_fade', { duration: .2, from: 0.4, to: 0 } );
fme_clearall();


}

function fme_clearall()
{
//clear all
      $$('.popup_error_msg').each(function(msg) {
        msg.innerHTML=''; });
    $('register_error').setStyle({display: 'none'});
      $$('#socialpopup_main_div input').each(function(c){
    	$(c).setValue('');  $(c).removeClassName('validation-failed'); });

}


