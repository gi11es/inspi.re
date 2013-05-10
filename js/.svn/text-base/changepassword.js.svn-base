var image_cross = "<img src='" + $("var_graphics_path").getValue() + "x-red.gif'>";
var image_check = "<img src='" + $("var_graphics_path").getValue() + "checkmark.gif'>";

var password_ok = false;
var verify_password_ok = false;

// Preload images
var img_cross = new Image();
img_cross.src = $("var_graphics_path").getValue() + "x-red.gif";
var img_check = new Image();
img_check.src = $("var_graphics_path").getValue() + "checkmark.gif";

function checksubmit() {
        if (password_ok && verify_password_ok)
                $("submitbutton").enable();
        else
                $("submitbutton").disable();
}

function checkpassword() {
        if ($F("password").length < 6) {
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
        if ($F("password") != $F("verify_password") || $F("verify_password") == "") {
                $("icon_verify_password").update(image_cross);
                verify_password_ok = false;
        }
        else {
                $("icon_verify_password").update(image_check);
                verify_password_ok = true;
        }
}

function infiniteFormTimer() {
        checkpassword();
        checkverifypassword();
        checksubmit();
        formTimerID = setTimeout("infiniteFormTimer()", 1000);
}

if ($("password")) infiniteFormTimer();