function changeBringBackAmount() {
	$('bring_back_change_amount').hide();
	$('bring_back_current_amount').hide();
	$('bring_back_change_input').show();
}

function cancelBringBackAmount() {
	$('bring_back_change_input').hide();
	$('bring_back_change_amount').show();
	$('bring_back_current_amount').show();
}

function saveBringBackAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('bring_back_per_page'),
							paging: 'BRING_BACK'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}