document.observe('dom:loaded', checkForm);

var image_cross = "<img src='" + $("var_graphics_path").getValue() + "x-red.gif'>";
var image_check = "<img src='" + $("var_graphics_path").getValue() + "checkmark.gif'>";
var image_loader = "<img src='" + $("var_graphics_path").getValue() + "ajax-loader.gif'>";

var password_ok = false;
var verify_password_ok = false;
var email_ok = false;
var verify_email_ok = false;

// Preload images
var img_cross = new Image();
img_cross.src = $("var_graphics_path").getValue() + "x-red.gif";
var img_check = new Image();
img_check.src = $("var_graphics_path").getValue() + "checkmark.gif";
var img_loader = new Image();
img_loader.src = $("var_graphics_path").getValue() + "ajax-loader.gif";

var latestCheckedEmail = "";
var checkedEmails = new Array();

$w("password_original verify_password email verify_email").each(function(name) { $(name).observe("keyup", checkForm).observe("change", checkForm).observe("blur", checkForm); });

function checksubmit() {
        if (password_ok && verify_password_ok && email_ok && verify_email_ok)
                $("submitbutton").enable();
        else
                $("submitbutton").disable();
}

function checkpassword() {
	var value = $("password_original").getValue();
	if (value instanceof Error && value.message == 'minimum') {
		$("password_length").show();
		$("icon_password").update(image_cross);
		password_ok = false;
	} else {
		$("password_length").hide();
		$("icon_password").update(image_check);
		password_ok = true;
	}
}

function checkverifypassword() {
        if ($F("password_original") != $F("verify_password") || $F("verify_password") == "") {
                $("icon_verify_password").update(image_cross);
                verify_password_ok = false;
        }
        else {
                $("icon_verify_password").update(image_check);
                verify_password_ok = true;
        }
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

function checkemail() {
	
        if (!isEmailValid($F("email").strip())) {
                $("icon_email").update(image_cross);
                email_ok = false;
        }
        else if (checkedEmails[$F("email").strip()]) {
        	if (checkedEmails[$F("email").strip()] == 2) {
				$("icon_email").update(image_check);
				$("email_unavailable").hide();
				email_ok = true;
			} else {
				$("icon_email").update(image_cross);
				$("email_unavailable").show();
				email_ok = false;
			}
        } else { 
             checkemailavailability();
        }
}

function checkverifyemail() {
        if ($F("email") != $F("verify_email")  || $F("verify_email") == "") {
                $("icon_verify_email").update(image_cross);
                verify_email_ok = false;      
        }
        else {
                $("icon_verify_email").update(image_check);
                verify_email_ok = true;
        }
}

function checkemailavailability() {
        $("icon_email").update(image_loader);
        $("submitbutton").disable();

        new Ajax.Request($("var_request_check_email").getValue(), {
                parameters: {
                        email: $F("email").strip()
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
                        
                        if (checkedemail == $F('email').strip()) {
                        	if (checkedEmails[checkedemail] == 2) {
                        		$("icon_email").update(image_check);
                        		$("email_unavailable").hide();
                        		email_ok = true;
                       		} else {
                            	$("icon_email").update(image_cross);
                            	$("email_unavailable").show();
                            	email_ok = false;
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
        checkpassword();
        checkverifypassword();
        checkemail();
        checkverifyemail();
        checksubmit();
}