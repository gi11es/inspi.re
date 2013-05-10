document.observe('dom:loaded', initMember);

function highlightItem(element) {
	new Effect.Pulsate(element, { delay: 0.7, pulses: 2, duration: 0.8 });
	element.removeClassName('highlight_item');
}

function initMember(event) {
	$$('.highlight_item').each(function (e) { highlightItem(e); });
}

function changeCommunitiesAmount() {
	$('communities_change_amount').hide();
	$('communities_current_amount').hide();
	$('communities_change_input').show();
}

function cancelCommunitiesAmount() {
	$('communities_change_input').hide();
	$('communities_change_amount').show();
	$('communities_current_amount').show();
}

function saveCommunitiesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('communities_per_page'),
							paging: 'PROFILE_COMMUNITIES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function changeEntriesAmount() {
	$('entries_change_amount').hide();
	$('entries_current_amount').hide();
	$('entries_change_input').show();
}

function cancelEntriesAmount() {
	$('entries_change_input').hide();
	$('entries_change_amount').show();
	$('entries_current_amount').show();
}

function saveEntriesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('entries_per_page'),
							paging: 'PROFILE_ENTRIES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function changeModeratedCommunitiesAmount() {
	$('moderated_communities_change_amount').hide();
	$('moderated_communities_current_amount').hide();
	$('moderated_communities_change_input').show();
}

function cancelModeratedCommunitiesAmount() {
	$('moderated_communities_change_input').hide();
	$('moderated_communities_change_amount').show();
	$('moderated_communities_current_amount').show();
}

function saveModeratedCommunitiesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('moderated_communities_per_page'),
							paging: 'PROFILE_MODERATED_COMMUNITIES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function changeAdministratedCommunitiesAmount() {
	$('administrated_communities_change_amount').hide();
	$('administrated_communities_current_amount').hide();
	$('administrated_communities_change_input').show();
}

function cancelAdministratedCommunitiesAmount() {
	$('administrated_communities_change_input').hide();
	$('administrated_communities_change_amount').show();
	$('administrated_communities_current_amount').show();
}

function saveAdministratedCommunitiesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('administrated_communities_per_page'),
							paging: 'PROFILE_ADMINISTRATED_COMMUNITIES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}