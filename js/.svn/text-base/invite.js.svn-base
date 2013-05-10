document.observe('dom:loaded', initInvite);

function isEmailListValid(e) {
        var re_two = /[\w-\.]+@([\w-]+\.)+[\w-]{2,4}/;
        
        var results = re_two.exec(e);
        
        return (results != null);
}

function highlightItem(element) {
	new Effect.Pulsate(element, { delay: 0.7, pulses: 2, duration: 0.8 });
	element.removeClassName('highlight_item');
}

function checkForm() {
	var valid = true;
	
	if (isEmailListValid($F('email_list_input'))) {
		$('email_list_empty').hide();
	} else {
		$('email_list_empty').show();
		valid = false;
	}
	
	var val = $('email_list_input').getValue();
	if (val instanceof Error) {
		$('email_list_too_long').show();
		valid = false;
	} else {
		$('email_list_too_long').hide();
	}
	
	var val = $('invite_text_input').getValue();
	if (val instanceof Error) {
		$('invite_text_too_long').show();
		valid = false;
	} else {
		$('invite_text_too_long').hide();
	}
	
	if (valid) {
		$('new_invite_submit').enable();
	} else {
		$('new_invite_submit').disable();
	}
}

function checkPostcardForm() {
	var valid = true;
	
	var val = $('postcard_text_input').getValue();
	if (val instanceof Error) {
		$('postcard_text_too_long').show();
		valid = false;
	} else {
		$('postcard_text_too_long').hide();
	}
	
	if (valid) {
		$('new_postcard_submit').enable();
	} else {
		$('new_postcard_submit').disable();
	}
}

function selectAffiliate() {
	$('affiliate_link').select();
}

function initInvite(event) {
	$('affiliate_link').observe('click', selectAffiliate).observe('focus', selectAffiliate);
	$w("email_list_input invite_text_input").each(function(name) { $(name).observe("keyup", checkForm).observe("change", checkForm).observe("blur", checkForm); });
	checkForm();
	$$('.highlight_item').each(function (e) { highlightItem(e); });
	
	$w("address_input postcard_text_input").each(function(name) { $(name).observe("keyup", checkPostcardForm).observe("change", checkPostcardForm).observe("blur", checkPostcardForm); });
	checkPostcardForm();
}