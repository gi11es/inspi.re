google.load("language", "1");

document.observe('dom:loaded', initDiscussionThread);
document.observe('scroll', scrolling).observe('resize', scrolling);
Event.observe(window, 'scroll', scrolling);
Event.observe(window, 'resize', scrolling);

var alreadyTranslated = new Array();

function translate() {
	var language = $('var_language').getValue();
	
	$$('.post_text').each(
		function (e) { 
			google.language.translate(e.innerHTML, "", language,  
				function(result) {  
					var parentid = e.up().identify();
					if (result.translation && !alreadyTranslated[parentid]) {
						if (e.innerHTML != result.translation)
							e.update('<b>' + $('var_translation_original').getValue() + '</b><br/><i>' + e.innerHTML + '</i><br/><br/><b>' + $('var_translation_translated').getValue() + '</b><br/>' + result.translation);
						alreadyTranslated[parentid] = true;
					} else if (!alreadyTranslated[parentid]) {
						e.update('<b>' + $('var_translation_failed').getValue() + '</b><br/><i>' + e.innerHTML + '</i>');
						alreadyTranslated[parentid] = true;
					}
				}
			); 
		}
	);		
}

function translateDiscussionPost(id) {
	var language = $('var_language').getValue();
	
	if ($('post_' + id)) {
		var e = $('post_' + id).down('.post_text');
		google.language.translate(e.innerHTML, "", language,  
				function(result) {  
					var parentid = e.up().identify();
					if (result.translation && !alreadyTranslated[parentid]) {
						if (e.innerHTML != result.translation)
							e.update('<b>' + $('var_translation_original').getValue() + '</b><br/><i>' + e.innerHTML + '</i><br/><br/><b>' + $('var_translation_translated').getValue() + '</b><br/>' + result.translation);
						alreadyTranslated[parentid] = true;
					} else if (!alreadyTranslated[parentid]) {
						e.update('<b>' + $('var_translation_failed').getValue() + '</b><br/><i>' + e.innerHTML + '</i>');
						alreadyTranslated[parentid] = true;
					}
				}
			);
	}
}

function scrolling(event) {
	var offset = $('thread_header').cumulativeOffset();
	var original_top = offset['top'];
	var original_left = offset['left'];
	var original_left_value = $('thread_header').getStyle('left');

	var document_offset = document.viewport.getScrollOffsets();
	if (document_offset['top'] > original_top) {
		$('thread_header_floater').setStyle({position: 'fixed', left: (original_left - document_offset['left']) + 'px'});
	} else {
		$('thread_header_floater').setStyle({position: 'relative', left: original_left_value});
	}
}

// Scrolls smoothly towards a DOM element
function scrollToSmoothly(element, duration) {
	var delta =  $(element).offsetTop - document.viewport.getScrollOffsets().top - 78;
	new Effect.ScrollTo(window, {y: delta, duration: duration });
}

function showPost(post) {
	scrollToSmoothly($(post), 0.7);
	new Effect.Pulsate(post, { delay: 0.7, pulses: 2, duration: 0.8 });
}

function hidePointsTransfer() {
	$('transfer_points').fade({ duration: 0.5, from: 1 });
}

function showPointsTransfer(oid) {
	currentInsightfulOid = oid;
	$('transfer_points').appear({ duration: 0.5, to: 1 });
}

function transferPoints() {
	new Ajax.Request($('var_request_transfer_points').getValue(), {
					parameters: {
							oid: currentInsightfulOid
					},
					onSuccess: function(transport) {
						sendingComment = false;
						var result = transport.responseText.evalJSON();
						if (result['status'] == 2) {
							window.location = result['url'];
						}
						
						hidePointsTransfer();
						reloadPoints();
					}
				});
}

function initDiscussionThread(event) {
	scrolling();
	if ($('var_scrollto')) showPost($('var_scrollto').getValue());
	if ($('var_translate')) translate();
}

function changePostsAmount() {
	$('posts_change_amount').hide();
	$('posts_current_amount').hide();
	$('posts_change_input').show();
}

function cancelPostsAmount() {
	$('posts_change_input').hide();
	$('posts_change_amount').show();
	$('posts_current_amount').show();
}

function savePostsAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('posts_per_page'),
							paging: 'DISCUSSION_THREAD_POSTS'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}