document.observe('dom:loaded', initBugReport);
var enabled = false;

function initBugReport() {
	$('text').focus();
	$('text').observe('keyup', updateSubmit).observe('change', updateSubmit);
}

function submitPost() {
	if (enabled) {
		new Ajax.Request($('var_request_new_bug_report').getValue(), {
					parameters: {
						text:  $('text').getValue()
					},
					onSuccess: function(transport) {
						var response = transport.responseText.evalJSON();
						$('text').setValue('');
						checkFields();
						$('text').blur();
						$('report_success').show();
						new Effect.Pulsate($('report_success'), { pulses: 2, duration: 0.8 });
					}
                });
		
	} else {
		if ($('post_too_short').visible())
			new Effect.Pulsate($('post_too_short'), { pulses: 2, duration: 0.8 });
		else if ($('post_too_long').visible())
			new Effect.Pulsate($('post_too_long'), { pulses: 2, duration: 0.8 });
	}
}

function updateSubmit() {
	if (checkFields()) {
		enabled = true;
	} else {
		enabled = false;
	}
}

function checkFields() {
	var result = true;

	var val = $('text').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('post_too_short').show();
		else
			$('post_too_long').show();
		result = false;
	} else {
		$('post_too_short').hide();
		$('post_too_long').hide();
	}
	
	return result;
}