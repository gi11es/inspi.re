var marker = null;
var connection = null;
var cleaningChildren = false;
var firstRun = true;
var channels = new Hash();

document.observe('dom:loaded', initHome);

function onMapLoad() {
	this.getLayerManager().addLayer(new FE.Layer.DayNight());
	var cameraIcon = new FE.Icon('http://freeearth.poly9.com/images/magnolia.png');
	var qc = new FE.LatLng($('var_latitude').getValue(),$('var_longitude').getValue());
	this.panTo(qc, 8, 'easeinoutquart');
	this.zoomTo(20000000.0, 8, 'easinoutquart');
	marker = new FE.Pushpin(qc, cameraIcon);
	this.addOverlay(marker);
	setTimeout('showInfoWindow()', 8000);
}

function showInfoWindow(event) {
	marker.openInfoWindowHtml('<div id=\'earth_caption\'>YOU ARE HERE!</div>', 150, 50);
}

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

function highlightItem(element) {
	new Effect.Pulsate(element, { delay: 0.7, pulses: 2, duration: 0.8 });
	element.removeClassName('highlight_item');
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
							amount: $F('home_entries_per_page'),
							paging: 'HOME_ENTRIES'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function redirectPrize(event) {
	window.location = $('var_prize_url').getValue();
}

function onMessage(messages, effects) {
	messages.each(function(message) {
		if (message.message_id > channels.get(message.channel))
			channels.set(message.channel, message.message_id);
			
		if (message.channel == $('var_comet_channel').getValue() + $('var_lid').getValue()) {
			var element = new Element('div', {'class' : 'real_time_update'});
			element.update(message.text);
			element.hide();
			$('real_time_updates').insert({top: element});
			if (effects)
				new Effect.BlindDown(element, {duration: 0.4, scaleFrom: 0, scaleTo: 100});
			else
				element.show();
		
			if (!cleaningChildren) {
				cleaningChildren = true;
				var children = $('real_time_updates').childElements();
				
				while (children.length > 3) {
					children.last().remove();
					children = $('real_time_updates').childElements();
				}
				cleaningChildren = false;
			}
		}
	});
}

function connectCOMET() {
	new Ajax.Request( $('var_comet_url').getValue() + (firstRun?'?last=3':''), {
			method: 'post',
			parameters: channels,
			onSuccess: function (transport) {
				if (firstRun) {
					firstRun = false;
					onMessage(transport.responseText.evalJSON(), false);
				} else {
					onMessage(transport.responseText.evalJSON(), true);
				}
				
				connectCOMET();
			}
		});
}

function initHome() {
	if ($('earth')) {
		var map = new FE.Map($('earth'));
		map.onLoad = onMapLoad;
		map.load();
	}
	
	$$('.highlight_item').each(function (e) { highlightItem(e); });
	
	if ($('prize')) $('prize').observe('click', redirectPrize);

	if ($('var_home_obsessed')) $('too_much_refresh').show();
}

function initCOMET() {
	channels.set($('var_comet_channel').getValue() + $('var_lid').getValue(), 0);
	connectCOMET();
}

Event.observe(window, 'load', function () {
	if ($('var_comet_url')) {
		setTimeout('initCOMET()', 200);
	}
});
