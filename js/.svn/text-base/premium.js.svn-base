function switchToCurrency(currency) {
	new Ajax.Request($('var_request_currency_payment').getValue(), {
					parameters: {
							currency: currency
					},
					onSuccess: function(transport) {
						$('donation_options').update(transport.responseText);
					}
                });
}