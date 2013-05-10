function changeCompetitionsAmount() {
	$('competitions_change_amount').hide();
	$('competitions_current_amount').hide();
	$('competitions_change_input').show();
}

function cancelCompetitionsAmount() {
	$('competitions_change_input').hide();
	$('competitions_change_amount').show();
	$('competitions_current_amount').show();
}

function saveCompetitionsAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('competitions_per_page'),
							paging: 'HALL_OF_FAME_COMPETITIONS'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}