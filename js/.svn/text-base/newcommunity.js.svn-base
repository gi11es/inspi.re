$('community_name_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
$('frequency_field').observe('keyup', updateSubmit).observe('change', updateSubmit);

function updateSubmit() {
	if (checkFields()) {
		$('new_community_submit').enable();
	} else {
		$('new_community_submit').disable();
	}
}

function checkFields() {
	var result = true;

	try {
		var val = $('community_name_input').getValue();
		$('community_name_too_short').hide();
	} catch(err) {
		$('community_name_too_short').show();
		result = false;
	}
	
	try {
		var val = $('frequency_field').getValue();
		if (val == '' || parseFloat(val) <= 0.0) {
			$('frequency_invalid').show();
			result = false;
		} else $('frequency_invalid').hide();
	} catch(err) {
		$('frequency_invalid').show();
		result = false;
	}
	
	return result;
}

updateSubmit();