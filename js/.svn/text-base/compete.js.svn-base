document.observe('dom:loaded', initCompete);

// Scrolls smoothly towards a DOM element
function scrollToSmoothly(element, duration) {
	var realOffset = element.cumulativeOffset();

	var delta =  realOffset['top'] - document.viewport.getScrollOffsets().top - 78;
	new Effect.ScrollTo(window, {y: delta, duration: duration });
}

function highlightItem(element) {
	scrollToSmoothly(element, 1);
	new Effect.Pulsate(element, { delay: 1.5, pulses: 2, duration: 0.8 });
	element.removeClassName('highlight_item');
}

function initCompete(event) {
	if ($('community_filter_title')) $('community_filter_title').observe('click', showHideFilters);
	
	$$('.highlight_item').each(function (e) { highlightItem(e); });
}

function showHideFilters(event) {
	if ($('community_filters').visible()) {
		new Effect.BlindUp('community_filters', { duration: 0.3 });
	} else {
		new Effect.BlindDown('community_filters', { duration: 0.3 });
	}
}

function hideCompetition(cid) {
	if ($('competition_' + cid)) {
		new Ajax.Request($('var_request_hide_competition').getValue(), {
					parameters: {
							cid: cid,
							hide: 'true'
					},
					onSuccess: function(transport) {
						$('competition_' + cid).hide();
					}});
	}
}

function unhideCompetition(cid) {
	if ($('competition_' + cid)) {
		new Ajax.Request($('var_request_hide_competition').getValue(), {
					parameters: {
							cid: cid,
							hide: 'false'
					},
					onSuccess: function(transport) {
						$('competition_' + cid).hide();
					}});
	}
}