document.observe('dom:loaded', initHallOfFame);

function keyPress(e) {
	var code;
	if (!e) var e = window.event;
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	
	if (Event.element(e).type != 'textarea' && Event.element(e).type != 'text'  && !e.altKey && !e.shiftKey && !e.ctrlKey && !e.metaKey) switch (code) {
		case 37:
		case 63234:
			if ($('var_previous_page')) window.location = $('var_previous_page').getValue();
			if (e.altKey) {
				e.cancelBubble = true;
				e.returnValue = false;
				if (e.stopPropagation) e.stopPropagation();
				return false;
			}
			return;
		case 8:
			if ($('var_previous_page')) window.location = $('var_previous_page').getValue();
			e.cancelBubble = true;
			e.returnValue = false;
			if (e.stopPropagation) e.stopPropagation();
			return false;
		case 39:
		case 63235:
			if ($('var_next_page')) window.location = $('var_next_page').getValue();
			if (e.altKey) {
				e.cancelBubble = true;
				e.returnValue = false;
				if (e.stopPropagation) e.stopPropagation();
				return false;
			}
			return;
	}
}

function initHallOfFame() {
	document.onkeydown = keyPress;
}