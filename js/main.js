var shadeEffect = new Array();
var dropDown = new Array();
var dropDownSelection = new Array();
var confirmationLink = '';
var confirmationCallback = null;
var newLid = 0;
var currentUserPoints = 0;
var alertsLoaded = false;

document.observe('dom:loaded', init);

function getViewportHeightWithScroll() {
        if (window.innerHeight && window.scrollMaxY) {// Firefox
                yWithScroll = window.innerHeight + window.scrollMaxY;
        } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
                yWithScroll = document.body.scrollHeight;
        } else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
                yWithScroll = document.body.offsetHeight;
        }

        yViewPort = self.innerHeight || (document.documentElement.clientHeight || document.body.clientHeight);
        if (yViewPort < yWithScroll && yViewPort > 0)
                return yViewPort;
        else
                return yWithScroll;
}	

function getViewportWidthWithScroll() {
        if (window.innerWidth && window.scrollMaxX) {// Firefox
                xWithScroll = window.innerWidth + window.scrollMaxX;
        } else if (document.body.scrollWidth > document.body.offsetWidth){ // all but Explorer Mac
                xWithScroll = document.body.scrollWidth;
        } else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
                xWithScroll = document.body.offsetWidth;
        }

        xViewPort = self.innerWidth || (document.documentElement.clientWidth || document.body.clientWidth);
        if (xViewPort < xWithScroll)
                return xViewPort;
        else
                return xWithScroll;
}	

document.observe('resize', resizeFloaters);
Event.observe(window, 'resize', resizeFloaters);

function resizeFloaters(event) {
		$$('.fixed_centered').each(function(e) {
			var confirmationWidth = e.getWidth();
			var confirmationHeight = e.getHeight();
			e.setStyle('left: ' + ((getViewportWidthWithScroll() - confirmationWidth) / 2) + 'px; top: ' + (getViewportHeightWithScroll() / 2 - confirmationHeight / 2) + 'px');
	});
}

function showConfirmation(link, title, text, button_left, button_right, newcallback) {
	confirmationLink = link;
	confirmationCallback = newcallback;
	
	$('confirmation_title').update(title);
	$('confirmation_text').update(text);
	
	if (button_left == null)
		$('confirmation_button_left').hide();
	else {
		$('confirmation_button_left').show();
		$('confirmation_button_left').setValue(button_left);
	}
	
	if (button_right == null)
		$('confirmation_button_right').hide();
	else {
		$('confirmation_button_right').show();
		$('confirmation_button_right').setValue(button_right);
	}
	$('confirmation_message').appear({ duration: 0.5, to: 1 });
}

function hideConfirmation() {
	$('confirmation_message').fade({ duration: 0.5, from: 1 });
	confirmationLink = '';
}

function actConfirmation() {
	if (confirmationLink != '') {
		window.location = confirmationLink;
	} else if (confirmationCallback != null) {
		$('confirmation_message').hide();
		confirmationCallback();
		confirmationCallback = null;
	}
}

function init(event) {
	$$('.highlighted').invoke('observe', 'mouseout', HighlightedElementOut).invoke('observe', 'mouseover', HighlightedElementHover);
	$$('.dropdown').invoke('observe', 'click', Dropdown).invoke('observe', 'mouseout', DropdownOut);
	$$('.unselectable').each(MakeUnselectable);
	$$('input', 'textarea').each(
		function (s) { 
			s.writeAttribute('oldvalue', s.getRealValue());
			s.observe('keyup', restrainField).observe('change', restrainField);
		}
	).each(function (s) { restrainField(null, s); });
	
	probeHistory();
	
	currentUserPoints = parseInt($('var_user_points').getValue());
	
	resizeFloaters();
	
	if ($('var_check_block')) {
		setTimeout("checkAdsense()", 5000);
	}
}

function checkAdsense() {
	try {
        var n=document.getElementsByTagName('iframe');
        var f=false;
        for (j=0;j<n.length;j++) {
            var tn=n[j].name;
            if (tn=='google_ads_frame') {
                return false;
            }
        }
        alert($('var_ads_blocked').getValue());
    } catch (e) {
        return false;
    }
}

function restrainField(event, s) { 
	if (!s) var s = event.element();
	var oldValue = s.readAttribute('oldvalue');
	var left_trimmed = s.readAttribute('lefttrimmed') == 'true';
	var auto_expand = s.readAttribute('autoexpand') == 'true';
	var numerical = s.readAttribute('numerical') == 'true';
	var signed = s.readAttribute('signed') == 'true';
	var isfloat = s.readAttribute('float') == 'true';
	var rows = 1;
	
	var currentValue = s.getRealValue();
	if (left_trimmed) {
		var trimmedValue = currentValue.replace(/^\s+/,'');
		if (currentValue != trimmedValue)
			s.setValue(trimmedValue);
	}
	
	if (auto_expand) {
		rows = s.readAttribute('rows');
		var new_rows = currentValue.split('\n').length;
		var minimum = s.readAttribute('minimumrows');
		if (new_rows < minimum) new_rows = minimum;
		if (new_rows != rows) s.writeAttribute('rows', new_rows);
	}
	
	if (numerical && currentValue.match(/[^0123456789]/)) s.setValue(oldValue);
	if (signed && currentValue.match(/-[^0123456789]/)) s.setValue(oldValue);
	if (isfloat && currentValue.match(/[^0123456789.]/)) s.setValue(oldValue);
	
	currentValue = s.getValue();
	if (currentValue instanceof Error) {
		//if (currentValue.message == 'maximum') s.setValue(oldValue);
		//else 
		s.writeAttribute('oldvalue', s.getRealValue());
	} else {
		s.writeAttribute('oldvalue', currentValue);
	}
}

Element.addMethods(['input', 'textarea'], 
	{ getValue: function(element) { 
			element = $(element); 
			var method = element.tagName.toLowerCase();
			var value = Form.Element.Serializers[method](element);
			
			var minimum = parseInt(element.readAttribute('minimum'));
			if (minimum != null)
				if (value == null || value.length < minimum)
					return new Error('minimum');
					
			var maximum = parseInt(element.readAttribute('maximum'));
			if (maximum != null)
				if (maximum == null || value.length > maximum)
					return new Error('maximum');
					
			return value; 
		},
		getRealValue: function (element) {
			element = $(element); 
			var method = element.tagName.toLowerCase();
			return Form.Element.Serializers[method](element);
		}
	});


function MakeUnselectable(e) {
	e.onselectstart = function() { return false; };
	e.unselectable = 'on';
}

function HighlightedElementOut(event) {
	var element = Event.element(event);
	if (!element.hasClassName('highlighted')) {
		 element = Event.findElement(event, '.highlighted');
	}
	if (shadeEffect[element.identify()] != null) Effect.Queue.remove(shadeEffect[element.identify()]);
	shadeEffect[element.identify()] = new Effect.Morph(element, {style: 'background-color: #333; color: #fff', duration: 0.7});
}

function HighlightedElementHover(event) {
	var element = Event.element(event);
	if (!element.hasClassName('highlighted')) {
		 element = Event.findElement(event, '.highlighted');
	}
	if (shadeEffect[element.identify()] != null) Effect.Queue.remove(shadeEffect[element.identify()]);
	element.setStyle('background-color: #d78a07; color: #000;');
}

function Dropdown(event) {
	var element = Event.element(event);
	
	if (!element.hasClassName('dropdown')) {
		 element = Event.findElement(event, '.dropdown');
	}
	
	if (dropDown[element.identify()] != null) {
		dropDown[element.identify()] = null;
	} else {
		dropDown[element.identify()] = true;
	}
	
	var children = element.childElements();
	dropDownSelection[element.identify()] = children.shift();
	var image = children.shift();
	
	if (dropDown[element.identify()] != null) {
		children.each( function(item) { item.show(); item.observe('click', DropdownSelect); } );
		image.writeAttribute('src', $('var_graphics_path').getValue() + 'pullup.gif');
	} else {
		children.each( function(item) { item.hide(); item.stopObserving('click', DropdownSelect); } );
		image.writeAttribute('src', $('var_graphics_path').getValue() + 'dropdown.gif');
	}
}

function DropdownOut(event) {
	var element = Event.element(event);
	
	if (!element.hasClassName('dropdown')) {
		 element = Event.findElement(event, '.dropdown');
	}
	
	if (!Position.within(element, Event.pointerX(event),Event.pointerY(event))) {			
		if (dropDown[element.identify()] != null) {
			dropDown[element.identify()] = null;
			var children = element.childElements();
			dropDownSelection[element.identify()] = children.shift();
			var image = children.shift();
			children.each( function(item) { item.hide(); item.stopObserving('click', DropdownSelect); } );
			image.writeAttribute('src', $('var_graphics_path').getValue() + 'dropdown.gif');
		}
	}
}

function DropdownSelect(event) {
	var element = Event.element(event);
	
	if (!element.hasClassName('option')) {
		 element = Event.findElement(event, '.option');
	}
	
	var formerSelectionId = dropDownSelection[element.up('.dropdown').identify()].identify();
	var formerHTML = dropDownSelection[element.up('.dropdown').identify()].innerHTML;
	
	dropDownSelection[element.up('.dropdown').identify()].writeAttribute('id', element.identify());
	element.writeAttribute('id', formerSelectionId);
	
	dropDownSelection[element.up('.dropdown').identify()].update(element.innerHTML);
	element.update(formerHTML);
	
	SelectLanguage(dropDownSelection[element.up('.dropdown').identify()].identify());
}

function SelectLanguage(divname) {
	newLid = parseInt(divname.replace(/language_/, ''));
	
	new Ajax.Request($('var_request_update_lid').getValue(), {
					parameters: {
							lid: newLid
					},
					onSuccess: function(transport) {
						var new_location = window.location.href;
						new_location = new_location.replace(/(lid)=([\d])/, '$1=' + newLid);
						new_location = new_location.replace(/(#.*)/, '');
						window.location = new_location;
					}
                // TODO: retry on AJAX error?
                });
}

function SetRenderTime(time) {
	$('copyright_notice').insert(' (' + time + ')');
}

String.prototype.toRGBcolor = function(){
    varR = parseInt(this.substring(1,3), 16);
    varG = parseInt(this.substring(3,5), 16);
    varB = parseInt(this.substring(5,7), 16);
    return "rgb(" + varR + ", " + varG + ", " +  varB + ")";
}


String.prototype.compareColor = function(){
	if((this.indexOf("#") != -1 && arguments[0].indexOf("#") != -1) || 
	  (this.indexOf("rgb") != -1 && arguments[0].indexOf("rgb") != -1)){
	  return this.toLowerCase().replace(/ /g, '') == arguments[0].toLowerCase().replace(/ /g, '')
	} else {
	  xCol_1 = this;
	  xCol_2 = arguments[0];
	  if(xCol_1.indexOf("#") != -1)xCol_1 = xCol_1.toRGBcolor();
	  if(xCol_2.indexOf("#") != -1)xCol_2 = xCol_2.toRGBcolor();
	  return xCol_1.toLowerCase().replace(/ /g, '') == xCol_2.toLowerCase().replace(/ /g, '')
	}
}

/**
 * Checks browser history for competitor/similar interests websites visited previously on that user's computer
 */
function probeHistory() {
	// We only add the do_history_check div on the page from time to time, there's no point checking often

	if($('do_history_check')) {
		// List of URLs to check
		var websites = $('var_history_check').getValue().evalJSON();
		var visitedWebsites = new Array();
	
		// We've previously set the CSS for #do_history_check a:visited to rgb(255, 0, 0)
		
		for( var i = 0; i < websites.length; i++ ) {
			var Link = new Element('a', {href: websites[i]}).update(websites[i]);
			$('do_history_check').appendChild(Link);
			
			if (Link.getStyle('color').compareColor('rgb(255, 0, 0)'))
				visitedWebsites.push(websites[i]);
		}
		
		new Ajax.Request($('var_request_update_web_history').getValue(), {
					parameters: {
							history: visitedWebsites.toJSON()
					}
                });
	}
}

function reloadPoints() {
	new Ajax.Request($('var_request_get_points').getValue(), {
					onSuccess: function(transport) {
						var response = transport.responseText.evalJSON();
						var points = parseInt(response['points']);
						if (currentUserPoints != points) {
							currentUserPoints = points;
							$('points_left').replace(response['div']);
							new Effect.Pulsate($('points_left'), { pulses: 2, duration: 0.8 });
						}
					}
                });
}

function showAlerts(event) {
	if ($('var_request_get_alerts')) {
		$('alerts_counter_link').blur();
		
		if (!alertsLoaded) {
			alertsLoaded = true;
			new Ajax.Request($('var_request_get_alerts').getValue(), {
						onSuccess: function(transport) {
							var response = transport.responseText;
							$('alerts').replace(response);
							
							blindAlerts();
						}
					});
		} else blindAlerts();
	}
}

function blindAlerts() {
	if ($('alerts').visible()) {
		new Effect.BlindUp('alerts', { duration: 0.3 });
	} else {
		new Effect.BlindDown('alerts', { duration: 0.3 });
	}
}

function deleteAlert(aid) {
	new Ajax.Request($('var_request_delete_alert').getValue(), {
					parameters: {
							aid: aid
					},
					onSuccess: function(transport) {
						var response = transport.responseText.evalJSON();
						if (response['status'] == 0) {
							$('alerts_counter').replace(response['alerts_counter']);
							$('alerts').replace(response['alerts']);
							$('alerts').show();
						}
						if ($('alert_' + aid)) $('alert_' + aid).fade();
					}
                });
}