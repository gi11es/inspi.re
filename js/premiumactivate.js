function submitCode(currency) {
	new Ajax.Request($('var_request_premium_activate').getValue(), {
					parameters: {
							uid: $F('uid'),
							code: $F('activate_code')
					},
					onSuccess: function(transport) {
						$('activate_code').setValue('');
						$('activation_result').update(transport.responseText);
					}
                });
}