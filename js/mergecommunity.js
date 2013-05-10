document.observe('dom:loaded', initMergeCommunity);

function initMergeCommunity(event) {
	$('merge_community').options[0].selected = true;
	$('merge_community_submit').disable();
	$('merge_community').observe('keyup', updateSelection).observe('change', updateSelection);
	$('merge_community_submit').observe('click', mergeCommunity);
}

function updateSelection(event) {
	var validoption = false;
	
	$$('#merge_community option').each(function(elem){
                if (elem.selected && elem.value != '') validoption = true;
        });
        
    if (validoption) $('merge_community_submit').enable();
    else $('merge_community_submit').disable();
}

function mergeCommunity() {
	var merge_token = '';
	$$('#merge_community option').each(function(elem){
                if (elem.selected) merge_token = elem.value;
        });
        
    if (merge_token != '') showConfirmation($('var_merge_link').getValue() + merge_token, $('var_confirmation_title').getValue(), $('var_confirmation_text').getValue(), $('var_confirmation_button_left').getValue(), $('var_confirmation_button_right').getValue());
}