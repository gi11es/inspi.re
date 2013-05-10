document.observe('dom:loaded', initEditCommunity);

var labels_ready = true;

function initEditCommunity(event) {
	$('community_name_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('community_description_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('community_rules_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('frequency_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('enter_length_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('vote_length_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('maximum_theme_count_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('maximum_theme_count_per_member_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('theme_minimum_score_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('maximum_theme_count_checkbox').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('maximum_theme_count_per_member_checkbox').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('theme_minimum_score_checkbox').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('theme_cost_field').observe('keyup', updateSubmit).observe('change', updateSubmit);
	
	var labels = new Array();
	$$('.community_label_selected').each(function(e) { labels.push(parseInt(e.identify().substr(6))); });
	$('labels_input').setValue(labels.toJSON());
	
	updateSubmit();
}

function updateSubmit() {
	if (checkFields()) {
		$('new_community_submit').enable();
	} else {
		$('new_community_submit').disable();
	}
}

function selectLabel(clid) {
	var label = $('label_' + clid);
	if (label) {
		var change = false;
		
		if (label.hasClassName('community_label_selected')) {
			label.addClassName('community_label');
			label.removeClassName('community_label_selected');
			change = true;
		} else if ($$('.community_label_selected').length < 5) {
			label.addClassName('community_label_selected');
			label.removeClassName('community_label');
			change = true;
		}
		
		if (change) {			
			var labels = new Array();
			$$('.community_label_selected').each(function(e) { labels.push(parseInt(e.identify().substr(6))); });
			$('labels_input').setValue(labels.toJSON());
		}
		
		label.firstDescendant().blur();
	}
}

function checkFields() {
	var result = true;

	var val = $('community_name_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('community_name_too_short').show();
		else
			$('community_name_too_long').show();
		result = false;
	} else {
		$('community_name_too_short').hide();
		$('community_name_too_long').hide();
	}
	
	var val = $('community_description_input').getValue();
	if (val instanceof Error) {
		$('community_description_too_long').show();
		result = false;
	} else {
		$('community_description_too_long').hide();
	}
	
	var val = $('community_rules_input').getValue();
	if (val instanceof Error) {
		$('community_rules_too_long').show();
		result = false;
	} else {
		$('community_rules_too_long').hide();
	}
	
	var val = $('frequency_field').getValue();
	if (val instanceof Error) {
		$('frequency_invalid').show();
		result = false;
	} else {
		if (val == '' || parseFloat(val) <= 0.0) {
			$('frequency_invalid').show();
			result = false;
		} else $('frequency_invalid').hide();
	}
	
	var val = $('enter_length_field').getValue();
	if (val instanceof Error) {
		$('enter_length_invalid').show();
		result = false;
	} else {
		if (val == '' || parseFloat(val) <= 0.0) {
			$('enter_length_invalid').show();
			result = false;
		} else $('enter_length_invalid').hide();
	}
	
	var val = $('vote_length_field').getValue();
	if (val instanceof Error) {
		$('vote_length_invalid').show();
		result = false;
	} else {
		if (val == '' || parseFloat(val) <= 0.0) {
			$('vote_length_invalid').show();
			result = false;
		} else $('vote_length_invalid').hide();
	}
	
	if ($('maximum_theme_count_checkbox').checked) {
		var val = $('maximum_theme_count_field').getValue();
		if (val instanceof Error) {
			$('maximum_theme_count_invalid').show();
			result = false;
		} else {
			if (val == '' || parseInt(val) <= 0) {
				$('maximum_theme_count_invalid').show();
				result = false;
			} else $('maximum_theme_count_invalid').hide();
		}
	} else {
		$('maximum_theme_count_invalid').hide();
	}
	
	if ($('maximum_theme_count_per_member_checkbox').checked) {
		var val = $('maximum_theme_count_per_member_field').getValue();
		if (val instanceof Error) {
			$('maximum_theme_count_per_member_invalid').show();
			result = false;
		} else {
			if (val == '' || parseInt(val) <= 0) {
				$('maximum_theme_count_per_member_invalid').show();
				result = false;
			} else $('maximum_theme_count_per_member_invalid').hide();
		}
	} else {
		$('maximum_theme_count_per_member_invalid').hide();
	}
	
	if ($('theme_minimum_score_checkbox').checked) {
		var val = $('theme_minimum_score_field').getValue();
		if (val instanceof Error) {
			$('theme_minimum_score_invalid').show();
			result = false;
		} else {
			if (val == '' || parseInt(val) > 0) {
				$('theme_minimum_score_invalid').show();
				result = false;
			} else $('theme_minimum_score_invalid').hide();
		}
	} else {
		$('theme_minimum_score_invalid').hide();
	}
	

	var val = $('theme_cost_field').getValue();
	if (val instanceof Error) {
		$('theme_cost_invalid').show();
		result = false;
	} else {
		if (val == '' || parseInt(val) < 0) {
			$('theme_cost_invalid').show();
			result = false;
		} else $('theme_cost_invalid').hide();
	}

	if (!labels_ready) result = false;
	
	return result;
}