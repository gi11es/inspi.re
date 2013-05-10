document.observe('dom:loaded', initChangeEmail);

var image_cross = "<img src='" + $("var_graphics_path").getValue() + "x-red.gif'>";
var image_check = "<img src='" + $("var_graphics_path").getValue() + "checkmark.gif'>";
var image_loader = "<img src='" + $("var_graphics_path").getValue() + "ajax-loader.gif'>";

var new_email_ok = false;

// Preload images
var img_cross = new Image();
img_cross.src = $("var_graphics_path").getValue() + "x-red.gif";
var img_check = new Image();
img_check.src = $("var_graphics_path").getValue() + "checkmark.gif";
var img_loader = new Image();
img_loader.src = $("var_graphics_path").getValue() + "ajax-loader.gif";

var latestCheckedEmail = "";
var checkedEmails = new Array();

function checksubmit() {
        if (new_email_ok)
                $("submitbutton").enable();
        else
                $("submitbutton").disable();
}

function isEmailValid(e) {
        var ok = "1234567890qwertyuiop[]asdfghjklzxcvbnm.@-_QWERTYUIOPASDFGHJKLZXCVBNM";
        var re = /(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/;
        var re_two = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

        for(i=0; i < e.length ;i++){
                        if(ok.indexOf(e.charAt(i))<0){ 
                        return (false);
                }	
        }

        if (!e.match(re) && e.match(re_two))
                return true;		

        return false;
}

function checknewemail() {
	
        if (!isEmailValid($F("new_email").strip())) {
                $("icon_new_email").update(image_cross);
                new_email_ok = false;
        }
        else if (checkedEmails[$F("new_email").strip()]) {
        	if (checkedEmails[$F("new_email").strip()] == 2) {
				$("icon_new_email").update(image_check);
				$("email_unavailable").hide();
				new_email_ok = true;
			} else {
				$("icon_new_email").update(image_cross);
				$("email_unavailable").show();
				new_email_ok = false;
			}
        } else { 
             checkemailavailability();
        }
}

function checkemailavailability() {
        $("icon_new_email").update(image_loader);
        $("submitbutton").disable();

        new Ajax.Request($("var_request_check_email").getValue(), {
                parameters: {
                        email: $F("new_email").strip()
                },
                onSuccess: function(transport) {
                        var response = transport.responseText;
                        var resparray = response.split(" ");
                        var checkedemail = resparray[1];

                        if (resparray[0] == "available") {
                        	checkedEmails[checkedemail] = 2;
                        } else {
                        	checkedEmails[checkedemail] = 1;
                        }
                        
                        if (checkedemail == $F('new_email').strip()) {
                        	if (checkedEmails[checkedemail] == 2) {
                        		$("icon_new_email").update(image_check);
                        		$("email_unavailable").hide();
                        		new_email_ok = true;
                       		} else {
                            	$("icon_new_email").update(image_cross);
                            	$("email_unavailable").show();
                            	new_email_ok = false;
                        	}
                        	checksubmit();
                        }
                },
                onFailure: function (transport) {
                	checkemailavailability();
                }
        });

}

function checkForm() {
        checknewemail();
}

function initChangeEmail() {
	if ($("new_email")) {
		$("new_email").observe("keyup", checkForm).observe("keydown", checkForm).observe("change", checkForm).observe("blur", checkForm);
		checkForm();
	}
}