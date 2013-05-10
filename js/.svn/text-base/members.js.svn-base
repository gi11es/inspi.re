var connection = null;
var cleaningChildren = false;
var firstRun = true;
var channels = new Hash();

function onNewsUpdate(item, effects) {
	var element = new Element('div', {'class' : 'real_time_update'});
	element.update(item);
	element.hide();
	$('real_time_updates').insert({top: element});
	if (effects)
		new Effect.BlindDown(element, {duration: 0.4, scaleFrom: 0, scaleTo: 100});
	else
		element.show();

	if (!cleaningChildren) {
		cleaningChildren = true;
		var children = $('real_time_updates').childElements();
		
		while (children.length > 6) {
			children.last().remove();
			children = $('real_time_updates').childElements();
		}
		cleaningChildren = false;
	}
}

function onUserOn(item) {
	var profile_picture = item;

	var regexp = /id="([^"]+)"/;
	var id = regexp.exec(profile_picture);
	
	if (id.length == 2) {
		if (!$(id[1])) {
			$('live_users').insert(item);
			new Effect.Pulsate(id[1], {duration: 0.5, pulses: 1});
		}
	}
}

function onUserRegister(item) {
	var profile_picture = item;

	var regexp = /id="([^"]+)"/;
	var id = regexp.exec(profile_picture);
	
	if (id.length == 2) {
		if (!$(id[1])) {
			$('registration_recent_members').childElements().last().remove();
			$('registration_recent_members').insert({top: item});
			new Effect.Pulsate(id[1], {duration: 0.5, pulses: 1});
		}
	}
}

function onUserOff(item) {
	var uid = item;
	
	if ($('user_' + uid) && uid != $('var_uid').getValue()) {
		new Effect.Shrink('user_' + uid, {duration: 0.4});
		Element.remove.delay(0.4, 'user_' + uid);
	}
}

function onMessage(messages, effects) {
	messages.each(function(message) {	
		if (message.message_id > channels.get(message.channel))
			channels.set(message.channel, message.message_id);
	
		if (message.channel == $('var_comet_channel_activity').getValue() + $('var_lid').getValue())
			onNewsUpdate(message.text, effects);
		else if (message.channel == $('var_comet_channel_user_on').getValue() && !firstRun)
			onUserOn(message.text);
		else if (message.channel == $('var_comet_channel_user_off').getValue() && !firstRun)
			onUserOff(message.text);
		else if (message.channel == $('var_comet_channel_user_registered').getValue() && !firstRun)
			onUserRegister(message.text);
	});
}

function connectCOMET() {
	new Ajax.Request( $('var_comet_url').getValue() + (firstRun?'?last=5':''), {
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

function initCOMET() {
	channels.set($('var_comet_channel_activity').getValue() + $('var_lid').getValue(), 0);
	channels.set($('var_comet_channel_user_on').getValue(), 0);
	channels.set($('var_comet_channel_user_off').getValue(), 0);
	channels.set($('var_comet_channel_user_registered').getValue(), 0);

	connectCOMET();
}

Event.observe(window, 'load', function () {
	if ($('var_comet_url')) {
		setTimeout('initCOMET()', 200);
	}
});
