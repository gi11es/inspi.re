function changeResultsAmount() {
	$('results_change_amount').hide();
	$('results_current_amount').hide();
	$('results_change_input').show();
}

function cancelResultsAmount() {
	$('results_change_input').hide();
	$('results_change_amount').show();
	$('results_current_amount').show();
}

function saveResultsAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('search_results_per_page'),
							paging: 'MEMBERS_SEARCH'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}