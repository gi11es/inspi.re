var openingeffect = new Array();

function showPrivateMessage(pmid) {
	var message = $('message_' + pmid);
	var messageheader = $('message_header_' + pmid);
	var openlink = $('open_' + pmid);
	
	if (message && openlink) {
		if (message.visible() && !openingeffect[pmid]) {
			openingeffect[pmid] = new Effect.BlindUp('message_' + pmid, { duration: 0.3, beforeStart: function(effect) { openingeffect[pmid] = true; }, afterFinish: function(effect) { openingeffect[pmid] = false; }});
			openlink.update($('var_open_text').getValue());
		} else if (!openingeffect[pmid]) {
			openingeffect[pmid] = new Effect.BlindDown('message_' + pmid, { duration: 0.3, beforeStart: function(effect) { openingeffect[pmid] = true; }, afterFinish: function(effect) { openingeffect[pmid] = false; }});
			openlink.update($('var_close_text').getValue());
		}
		
		if (messageheader.hasClassName('insightful_header')) {
			messageheader.removeClassName('insightful_header');
			// AJAX request to update the unread message counter in the webste's header
			new Ajax.Request($('var_request_update_private_message_status').getValue(), {
					parameters: {
							pmid: pmid
					},
					onSuccess: function(transport) {
						$('private_message_counter').replace(transport.responseText);
						new Effect.Pulsate($('private_message_counter'), { pulses: 2, duration: 0.8 });
					}});
		}
		
		openlink.blur();
	}
}

function changeMessagesAmount() {
	$('messages_change_amount').hide();
	$('messages_current_amount').hide();
	$('messages_change_input').show();
}

function cancelMessagesAmount() {
	$('messages_change_input').hide();
	$('messages_change_amount').show();
	$('messages_current_amount').show();
}

function saveMessagesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('private_messages_per_page'),
							paging: 'HOME_PRIVATE_MESSAGES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}