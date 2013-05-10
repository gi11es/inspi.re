document.observe('scroll', scrolling).observe('resize', scrolling);
Event.observe(window, 'scroll', scrolling);
Event.observe(window, 'resize', scrolling);

function scrolling(event) {
	var offset = $('board_header').cumulativeOffset();
	var original_top = offset['top'];
	var original_left = offset['left'];
	var original_left_value = $('board_header').getStyle('left');

	var document_offset = document.viewport.getScrollOffsets();
	if (document_offset['top'] > original_top) {
		$('board_header_floater').setStyle({position: 'fixed', left: (original_left - document_offset['left']) + 'px'});
	} else {
		$('board_header_floater').setStyle({position: 'relative', left: original_left_value});
	}
}

scrolling();

function changeThreadsAmount() {
	$('threads_change_amount').hide();
	$('threads_current_amount').hide();
	$('threads_change_input').show();
}

function cancelThreadsAmount() {
	$('threads_change_input').hide();
	$('threads_change_amount').show();
	$('threads_current_amount').show();
}

function saveThreadsAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('threads_per_page'),
							paging: 'BOARD_THREADS'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}