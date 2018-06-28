function openFeedbackWindow(ele,upd_ele,id)
{  if(popupShow)
        {
    Effect.Appear(ele);
    var back1 = document.getElementById ('backgroundpopup');
    back1.style.display = "block";
    popupShow = false;
        }
}
function closeFeedbackWindow(ele1){
    
    var val1=document.getElementById(ele1);    
    var background=document.getElementById('backgroundpopup');
    Effect.Fade(val1);
    Effect.Fade(background);
    $$('div.error-massage').each(function(ele){
        ele.hide();
    });
   // var divId = val1.id;
     popupShow = true;
//var parts = divId.split('feedback_information');
   //alert(parts[1]);
   $$('.trigger').each(function(ele){
   
    ele.setStyle({
    display: 'block'
  
    });
});
$$('.popuptrigger').each(function(ele){
   
    ele.setStyle({
    display: 'block'
  
    });
});
        }
    

function sendFeedback(feedback_form,url,divid,formid,buttonId){
    
    if(feedback_form && feedback_form.validate()){
        $('loader').show();
        $(buttonId).setAttribute('disabled', true);
        var parameters=$(formid).serialize(true);
        new Ajax.Request(url, {
                method: 'post',
                dataType: 'json',
                parameters: parameters,
                onSuccess: function(transport) {
                    if(transport.status == 200) {
                        var response=transport.responseText.evalJSON();
                        $('success_message').innerHTML=response.message;
                        if(response.result=='success'){
                            $('success_message').removeClassName('feedback-error-msg');
                            $('success_message').addClassName('feedback-success-msg');
                        }
                        else{
                            $('success_message').removeClassName('feedback-success-msg');
                            $('success_message').addClassName('feedback-error-msg');
                        }
                        $('loader').hide();
                        $('success_message').show();
                        Effect.toggle('success_message', 'appear',{ duration: 5.0});
                        setTimeout(function (){
                                closeFeedbackWindow(divid);
                                $(formid).reset();
                                $(buttonId).removeAttribute('disabled');
                            },6000);
                        return false;
                    }
                }
        });
        return false;
    }
}