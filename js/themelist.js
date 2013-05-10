document.observe('dom:loaded', initThemeList);

function upOver(event) {
	var element = Event.element(event);
	element.writeAttribute('src', $('var_graphics_path').getValue() + 'up.gif');
}

function upOut(event) {
	var element = Event.element(event);
	if (!element.hasClassName('effective_vote'))
		element.writeAttribute('src', $('var_graphics_path').getValue() + 'up-grey.gif');
}

function downOver(event) {
	var element = Event.element(event);
	element.writeAttribute('src', $('var_graphics_path').getValue() + 'down.gif');
}

function downOut(event) {
	var element = Event.element(event);
	if (!element.hasClassName('effective_vote'))
		element.writeAttribute('src', $('var_graphics_path').getValue() + 'down-grey.gif');
}

// Scrolls smoothly towards a DOM element
function scrollToSmoothly(element, duration) {
	var delta =  $(element).offsetTop - document.viewport.getScrollOffsets().top - 78;
	new Effect.ScrollTo(window, {y: delta, duration: duration });
}

function showTheme(theme, scrolling) {
	if ($(theme)) {
		if (scrolling) scrollToSmoothly($(theme), 0.7);
		new Effect.Pulsate(theme, { delay: 0.7, pulses: 2, duration: 0.8 });
	}
}

function upClick(event) {
	var element = Event.element(event);
	var id = element.identify();
	if (id.startsWith('up_')) {
		var tid = parseInt(id.substring(3));
		new Ajax.Request($('var_request_cast_theme_vote').getValue(), {
					parameters: {
							tid: tid,
							points: 1,
							page_offset: $('var_page_offset').getValue()
					},
					onSuccess: function(transport) {
						element.writeAttribute('src', $('var_graphics_path').getValue() + 'up.gif');
						element.addClassName('effective_vote');
						var effectiveSibling = null;
						element.siblings().each(function (sibling) { if (sibling.hasClassName('effective_vote')) effectiveSibling = sibling; });
						
						if (effectiveSibling != null) {
							effectiveSibling.removeClassName('effective_vote');
							effectiveSibling.writeAttribute('src', $('var_graphics_path').getValue() + 'down-grey.gif');
						}
						
						$('theme_list').replace(transport.responseText);
						if ($('theme_' + tid)) showTheme('theme_' + tid, false);
						observeButtons();
						reloadPoints();
					}
                });
	}
}

function downClick(event) {
	var element = Event.element(event);
	var id = element.identify();
	if (id.startsWith('down_')) {
		var tid = parseInt(id.substring(5));
		new Ajax.Request($('var_request_cast_theme_vote').getValue(), {
					parameters: {
							tid: tid,
							points: -1,
							page_offset: $('var_page_offset').getValue()
					},
					onSuccess: function(transport) {
						element.writeAttribute('src', $('var_graphics_path').getValue() + 'down.gif');
						element.addClassName('effective_vote');
						var effectiveSibling = null;
						element.siblings().each(function (sibling) { if (sibling.hasClassName('effective_vote')) effectiveSibling = sibling; });
						
						if (effectiveSibling != null) {
							effectiveSibling.removeClassName('effective_vote');
							effectiveSibling.writeAttribute('src', $('var_graphics_path').getValue() + 'up-grey.gif');
						}
						
						$('theme_list').replace(transport.responseText);
						if ($('theme_' + tid)) showTheme('theme_' + tid, false);
						observeButtons();
						reloadPoints();
					}
                });
	}
}

function scrolling(event) {
	var offset = $('theme_list_header').cumulativeOffset();
	var original_top = offset['top'];
	var original_left = offset['left'];
	var original_left_value = $('theme_list_header').getStyle('left');

	var document_offset = document.viewport.getScrollOffsets();
	if (document_offset['top'] > original_top) {
		$('theme_list_header_floater').setStyle({position: 'fixed', left: (original_left - document_offset['left']) + 'px'});
	} else {
		$('theme_list_header_floater').setStyle({position: 'relative', left: original_left_value});
	}
}

function changeThemesAmount() {
	$('themes_change_amount').hide();
	$('themes_current_amount').hide();
	$('themes_change_input').show();
}

function cancelThemesAmount() {
	$('themes_change_input').hide();
	$('themes_change_amount').show();
	$('themes_current_amount').show();
}

function saveThemesAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('themes_per_page'),
							paging: 'THEME_LIST_THEMES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function initThemeList() {
	observeButtons();
	document.observe('scroll', scrolling).observe('resize', scrolling);
	Event.observe(window, 'scroll', scrolling);
	Event.observe(window, 'resize', scrolling);
	scrolling();
	if ($('var_scrollto')) showTheme($('var_scrollto').getValue(), true);
}

function observeButtons() {
	$$('.up_vote').invoke('observe', 'mouseout', upOut).invoke('observe', 'mouseover', upOver).invoke('observe', 'click', upClick);
	$$('.down_vote').invoke('observe', 'mouseout', downOut).invoke('observe', 'mouseover', downOver).invoke('observe', 'click', downClick);
}