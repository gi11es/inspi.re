var balance = 0;
var correct_email = false;
var correct_amount = false;

document.observe('dom:loaded', initTransferBalance);

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

function premiumAmountChange(event) {
	var amount = parseFloat($F('premium_amount'));
	if (isNaN(amount)) {
		$('premium_days').update('0');
	} else if (amount <= 5) {
		var days = Math.floor(amount * 6.2);
		$('premium_days').update(days);
	} else if (amount <= 25) {
		var days = Math.floor(amount * 7.32);
		$('premium_days').update(days);
	} else if (amount < 125) {
		var days = Math.floor(amount * 9.125);
		$('premium_days').update(days);
	} else {
		$('premium_days').update('<b>' + $('var_infinite_text').getValue() + '</b>');
	}
	
	if (!isNaN(amount) && amount > balance) {
		$('premium_amount_warning').show();
		$('premium_amount_submit').disable();
	} else {
		$('premium_amount_warning').hide();
		if (amount != 0 && !isNaN(amount))
			$('premium_amount_submit').enable();
		else
			$('premium_amount_submit').disable();
	}
}

function submitPremium(event) {
	var amount = parseFloat($F('premium_amount'));
	if (!isNaN(amount)) {
		new Ajax.Request($('var_request_generate_premium').getValue(), {
                parameters: {
                        amount: amount
                },
                onSuccess: function(transport) {
                 	window.location = $('var_premium_link').getValue() + transport.responseText;
                }
        });
	}
}

function paypalAmountChange(event) {
	var amount = parseFloat($F('paypal_amount'));
	
	var actual_amount = 0;
	
	if (!isNaN(amount))
		actual_amount = Math.ceil(100 * (amount + Math.min(1, 0.02 * amount))) / 100.0;
		
	$('paypal_actual_amount').update(actual_amount);
	
	if (!isNaN(amount) && actual_amount > balance) {
		$('paypal_amount_warning').show();
		correct_amount = false;
	} else if(isNaN(amount)) {
		$('paypal_amount_warning').hide();
		correct_amount = false;
	} else {
		$('paypal_amount_warning').hide();
		correct_amount = true;
	}
	
	if (correct_amount && correct_email) $('paypal_amount_submit').enable();
	else $('paypal_amount_submit').disable();
}

function paypalAddressChange(event) {
	if (!isEmailValid($F("paypal_address").strip())) {
		$('paypal_address_warning').show();
		correct_email = false;
	} else {
		$('paypal_address_warning').hide();
		correct_email = true;
	}
	
	if (correct_amount && correct_email) $('paypal_amount_submit').enable();
	else $('paypal_amount_submit').disable();
}

function submitPaypal(event) {
	var amount = parseFloat($F('paypal_amount'));
	
	if (!isNaN(amount) && amount > 0) {
		new Ajax.Request($('var_request_paypal_transfer').getValue(), {
                parameters: {
                        amount: amount,
                        account: $F("paypal_address").strip()
                },
                onSuccess: function(transport) {
                	var result = parseFloat(transport.responseText);
                	
                	if (!isNaN(result) && result > 0) {
						window.location = $('var_paypal_link').getValue() + result;
                	} else {
                		window.location = $('var_paypal_link').getValue() + '0';
                	}
                }
        });
	}
}

function initTransferBalance() {
	$('premium_amount').setValue('');
	$('premium_amount').observe('keyup', premiumAmountChange).observe('blur', premiumAmountChange).observe('change', premiumAmountChange);
	balance = parseFloat($('var_balance').getValue());
	$('premium_amount_submit').disable();
	$('premium_amount_submit').observe('click', submitPremium);
	
	$('paypal_amount').setValue('');
	$('paypal_amount').observe('keyup', paypalAmountChange).observe('blur', paypalAmountChange).observe('change', paypalAmountChange);

	$('paypal_address').setValue('');
	$('paypal_address').observe('keyup', paypalAddressChange).observe('blur', paypalAddressChange).observe('change', paypalAddressChange);

	$('paypal_amount_submit').disable();
	$('paypal_amount_submit').observe('click', submitPaypal);
}