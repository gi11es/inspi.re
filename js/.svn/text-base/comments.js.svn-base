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
                paging: 'MESSAGING_COMMENTS'
        },
        onSuccess: function(transport) {
            window.location = $('var_reload_url').getValue();
        }});
}