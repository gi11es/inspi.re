document.observe('dom:loaded', initSettings);

if (window.opera) window.onunload = shutDown;
else window.onbeforeunload = shutDown;

$('name').observe('keyup', nameChanged).observe('change', nameChanged);
$('custom_url').observe('keyup', customURLChanged).observe('change', customURLChanged);

var nameTimer = 0;
var nameValue = '';
var nameOngoing = false;

var customURLTimer = 0;
var customURLValue = '';
varcustomURLOngoing = false;

var retryTime = 4000;
var asynchronous = true;

function initSettings(event) {
	nameValue = $F('name');
	customURLValue = $F('custom_url');
	
	$('custom_url_valid').hide();
	$('custom_url_invalid').hide();
	$('custom_url_progress').show();
	submitCustomURL();
}

function checkFields() {
	var val = $('name').getValue();
	if (val instanceof Error) {
		$('name_too_long').show();
	} else {
		$('name_too_long').hide();
	}
	
	var val = $('description').getValue();
	if (val instanceof Error) {
		$('description_too_long').show();
	} else {
		$('description_too_long').hide();
	}
	
	var val = $F('custom_url');
	var cleanval = val.replace(/[\s\/?#.\\!@#&%\=;,|+*^~]+/, '');
	
	if (val != cleanval) {
		$('custom_url').setValue(cleanval);
	}
}

function nameChanged(event) {
	checkFields();
	if (nameValue != $F('name')) {
		nameValue = $F('name');
		clearTimeout(nameTimer);
		nameTimer = setTimeout(submitName, retryTime);
	}	
}

function submitName() {
	nameOngoing = true;
	new Ajax.Request($('var_request_update_name').getValue(), {
                parameters: {
                        name: $F('name')
                },
                onSuccess: function(transport) {
                	var link = $('header_user_name_dynamic');
                	if (link) link.update(transport.responseText);
                 	clearTimeout(nameTimer);
					nameOngoing = false;
                },
                onFailure: function(transport) {
                	nameValue = '%%%';
                	nameChanged();
                },
                asynchronous: asynchronous
        });
}

function customURLChanged(event) {
	checkFields();
	if (customURLValue != $F('custom_url')) {
		customURLValue = $F('custom_url');
		clearTimeout(customURLTimer);
		customURLTimer = setTimeout(submitCustomURL, 2000);
		$('custom_url_valid').hide();
		$('custom_url_invalid').hide();
		$('custom_url_progress').show();
	}	
}

function submitCustomURL() {
	customURLOngoing = true;
	new Ajax.Request($('var_request_update_custom_url').getValue(), {
                parameters: {
                        custom_url: $F('custom_url')
                },
                onSuccess: function(transport) {
                	$('custom_url_progress').hide();
                	if (transport.responseText == '1') {
                		$('custom_url_valid').show();
                	} else if (transport.responseText == '0') {
                		$('custom_url_invalid').show();
                	}
                	
                 	clearTimeout(customURLTimer);
					customURLOngoing = false;
                },
                asynchronous: asynchronous
        });
}

function submitAll() {
	nameOngoing = true;
	
	var markup = 0;
	
	if ($('markup').checked) markup = $F('markup_value');
	
	var custom_url = '';
	if ($('custom_url')) custom_url = $F('custom_url');
	
	new Ajax.Request($('var_request_update_profile').getValue(), {
                parameters: {
                        name: $F('name'),
                        communityfiltericons: $('community_filter_icons').checked,
                        displayrank: $('display_rank').checked,
                        hideads: $('hide_ads').checked,
                        alertemail: $('alert_email').checked,
                        description: $F('description'),
                        markup: markup,
                        allowsales: $('allow_sales').checked,
                        translate: $('translation').checked,
                        custom_url: custom_url
                },
                onSuccess: function(transport) {
                 	clearTimeout(nameTimer);
					nameOngoing = false;
                },
                onFailure: function(transport) {
                	nameValue = '%%%';
                	nameChanged();
                },
                asynchronous: asynchronous
        });
}

 function shutDown() {
 	if (nameTimer != 0 || nameOngoing) {
 		clearTimeout(nameTimer);
 	}
 	
 	asynchronous = false;
 	submitAll();
 }