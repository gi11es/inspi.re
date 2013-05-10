document.observe('dom:loaded', initCommunity);

function initCommunity(event) {
	if ($('left_confirmation')) {
		new Effect.Pulsate($('left_confirmation'), { delay: 0.7, queue: 'front', pulses: 2, duration: 0.8 });
		//new Effect.Fade($('left_confirmation'), { delay: 3.0, queue: 'end', duration: 1.0 });
	} else if ($('joined_confirmation')) {
		new Effect.Pulsate($('joined_confirmation'), { delay: 0.7, queue: 'front', pulses: 2, duration: 0.8 });
		//new Effect.Fade($('joined_confirmation'), { delay: 3.0, queue: 'end', duration: 1.0 });
	}
}

function changeMembersAmount() {
	$('members_change_amount').hide();
	$('members_current_amount').hide();
	$('members_change_input').show();
}

function cancelMembersAmount() {
	$('members_change_input').hide();
	$('members_change_amount').show();
	$('members_current_amount').show();
}

function saveMembersAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('members_per_page'),
							paging: 'COMMUNITY_MEMBERS'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}