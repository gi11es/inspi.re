function changeFavoritesAmount() {
	$('favorites_change_amount').hide();
	$('favorites_current_amount').hide();
	$('favorites_change_input').show();
}

function cancelFavoritesAmount() {
	$('favorites_change_input').hide();
	$('favorites_change_amount').show();
	$('favorites_current_amount').show();
}

function saveFavoritesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('favorites_per_page'),
							paging: 'HOME_FAVORITES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

