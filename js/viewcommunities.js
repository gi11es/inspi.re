var current_order = 0;
var current_page = 0;
var processing_done = false;
var restrict_language = true;
var restrict_labels = new Array();

document.observe('dom:loaded', initCommunities);

function switchLanguage(event) {
	restrict_language = !restrict_language;
	
	orderJoinableCommunities(current_order, current_page, restrict_language, restrict_labels);
	$('language_switch').blur();
}

function initCommunities(event) {
	$('language_switch').setValue(true);
	$('language_switch').observe('click', switchLanguage);
	current_order = $('var_current_order').getValue();
	current_page = $('var_current_page').getValue();
	$('order_' + current_order).setStyle({fontWeight : 'bold'});
	processing_done = true;
}

function selectLabel(clid) {
	var label = $('label_' + clid);
	if (label) {
		var change = false;
		if (label.hasClassName('filter_label_selected')) {
			label.addClassName('filter_label');
			label.removeClassName('filter_label_selected');
			change = true;
		} else if ($$('.filter_label_selected').length < 5) {
			label.addClassName('filter_label_selected');
			label.removeClassName('filter_label');
			change = true;
		}
		
		if (change) {
			restrict_labels = new Array();
			$$('.filter_label_selected').each(function(e) { restrict_labels.push(parseInt(e.identify().substr(6))); });
			orderJoinableCommunities(current_order, current_page, restrict_language, restrict_labels);
		}
		
		label.firstDescendant().blur();
	}
}

function orderJoinableCommunities(order, page, language, labels) {
	if (processing_done) {
		$('loader').show();
		processing_done = false;
		current_order = order;
		current_page = page;
		restrict_language = language;
		
		new Ajax.Request($('var_request_joinable_community_list').getValue(), {
					parameters: {
							order: current_order,
							page: current_page,
							restrict_language: restrict_language,
							restrict_labels: labels.toJSON()
					},
					onSuccess: function(transport) {
						var result = transport.responseText.evalJSON();
						$$('.order_option').each(function (e) {e.setStyle({fontWeight : 'normal'});});
						$('order_' + current_order).setStyle({fontWeight : 'bold'}).blur();
						$('joinable_community_list').replace(result['communitylist']);
						$('page_navigation').replace(result['paging']);
						processing_done = true;
						$('loader').hide();
					},
					onFailure: function(transport) {
						setTimeout('orderJoinableCommunities(' + current_order + ')', 5000);
					}
				});
	}
}

function changeCommunitiesAmount() {
	$('communities_change_amount').hide();
	$('communities_current_amount').hide();
	$('communities_change_input').show();
}

function cancelCommunitiesAmount() {
	$('communities_change_input').hide();
	$('communities_change_amount').show();
	$('communities_current_amount').show();
}

function saveCommunitiesAmount() {
	new Ajax.Request($('var_request_update_communities_per_page').getValue(), {
					parameters: {
							amount: $F('communities_per_page')
					},
					onSuccess: function(transport) {
						$('communities_current_amount').replace(transport.responseText);
						cancelCommunitiesAmount();
						orderJoinableCommunities(current_order, current_page, restrict_language, restrict_labels);
						
					}});
}