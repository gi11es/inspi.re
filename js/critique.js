
google.load("language", "1");

document.observe('dom:loaded', initEntry);
var viewerUid = null;
var loading = false;
var sendingComment = false;
var flickeringEffect = null;
var currentInsightfulOid = null;
var currentReplyOid = 0;
var savedText = '';
var currentFavorite = false;
var ongoingFavorite = false;
var currentPurchaseable = false;
var ishome = false;
var moderator = false;
var administrator = false;
var currentWidth = 900;

var tooShort = true;
var minimumWordCount = 0;

var commentHeaderInitialized = false;

var alreadyTranslated = new Array();

function initCommentHeader() {
	if (!commentHeaderInitialized) {
		commentHeaderInitialized = true;
		$('comment_actions').show();
		$('comment_text').show();
	}
}

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

function translateComment(id) {
	var language = $('var_language').getValue();
	
	if ($('comment_' + id)) {
		var e = $('comment_' + id).down('.post_text');
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

// Scrolls smoothly towards a DOM element
function scrollToSmoothly(element, duration) {
	var realOffset = element.cumulativeOffset();

	var delta =  realOffset['top'] - document.viewport.getScrollOffsets().top - 78;
	new Effect.ScrollTo(window, {y: delta, duration: duration });
}

function highlightItem(element, scroll) {
	if (scroll) scrollToSmoothly(element, 0.7);
	new Effect.Pulsate(element, { delay: 0.7, pulses: 2, duration: 0.8 });
	element.removeClassName('highlight_item');
}

function postComment() {
	if (loaded) {
		var text = $F('comment_text');
		
		if (text != '' && !sendingComment) {
			$('post_please_wait').show();
			sendingComment = true;
			
			new Ajax.Request($('var_request_new_comment').getValue(), {
						parameters: {
								hash: currentHash,
								text: text,
								oid: currentReplyOid
						},
						onSuccess: function(transport) {
							$('comment_text').setValue('');
							currentReplyOid = 0;
							$('comment_clear_reply').hide();

							sendingComment = false;
							var result = transport.responseText.evalJSON();
							if (result['status'] == 1) {
								if (result['hash'] == currentHash) {
									$('comments').update(result['comments']);
									if ($('var_translate')) translate();
																		
									$('comments_header').replace(result['comments_header']);
						
									$$('.highlight_item').each(function (e) { highlightItem(e, false); });	
								} else if (result['hash'] == previousEntry['hash']) {
									previousEntry['comments'] = result['comments'];
									previousEntry['comments_header'] = result['comments_header'];
								} else if (result['hash'] == nextEntry['hash']) {
									nextEntry['comments'] = result['comments'];
									nextEntry['comments_header'] = result['comments_header'];
								}
							}
							
							$('post_please_wait').hide();
							reloadPoints();
						},
						onFailure: function(transport) {
							sendingComment = false;
						}
					});
		}
	}
}

function replyToComment(oid) {
	if (loaded) {
		
	
		new Ajax.Request($('var_request_reply_to_comment').getValue(), {
						parameters: {
								hash: currentHash,
								oid: oid
						},
						onSuccess: function(transport) {
							$('comments_header').replace(transport.responseText);
							$('comment_text').focus();
							scrollToSmoothly($('comments_header'), 0.7);
							
							currentReplyOid = oid;
							
							if (oid == 0) {
								$('comment_clear_reply').hide();
							} else {
								$('comment_clear_reply').show();
							}
						}
					});
	}
}

function hidePointsTransfer() {
	$('transfer_points').fade({ duration: 0.5, from: 1 });
}

function showPointsTransfer(oid) {
	currentInsightfulOid = oid;
	$('transfer_points').appear({ duration: 0.5, to: 1 });
}

function deleteEntry() {
	if (loaded) {
		showConfirmation($('var_delete_href').getValue() + currentHash, $('var_delete_title').getValue(), $('var_delete_text').getValue(), $('var_delete_yes').getValue(), $('var_delete_no').getValue());
	}
}

function transferPoints() {
	if (loaded) {
		new Ajax.Request($('var_request_transfer_points').getValue(), {
						parameters: {
								oid: currentInsightfulOid,
								hash: currentHash
						},
						onSuccess: function(transport) {
							sendingComment = false;
							var result = transport.responseText.evalJSON();
							if (result['status'] == 1) {
								if (result['hash'] == currentHash) {
									$('comments').update(result['comments']);
									if ($('var_translate')) translate();
									$$('.highlight_item').each(function (e) { highlightItem(e, false); });	
								} else if (result['hash'] == previousEntry['hash'])
									previousEntry['comments'] = result['comments'];
								else if (result['hash'] == nextEntry['hash'])
									nextEntry['comments'] = result['comments'];
							}
							
							hidePointsTransfer();
							reloadPoints();
						},
						onFailure: function(transport) {
							setTimeout('transferPoints()', 10000);
						}
					});
	}
}

function refreshFavorite() {
		if (currentFavorite) {
			$$('.favorite_icon').invoke('writeAttribute', 'src', $('var_graphics_path').getValue() + 'heart.png?2')
							   .invoke('writeAttribute', 'title', $('var_favorite_remove').getValue());
		} else {
			$$('.favorite_icon').invoke('writeAttribute', 'src', $('var_graphics_path').getValue() + 'heart_inactive.png?2')
							   .invoke('writeAttribute', 'title', $('var_favorite_add').getValue());
		}
}

function favorite(event) {
	if (currentFavorite && !ongoingFavorite) {
		ongoingFavorite = true;
		new Ajax.Request($('var_request_remove_from_favorites').getValue(), {
			parameters: {
								hash: currentHash
						},
			onSuccess: function(transport) {
				currentFavorite = false;
				ongoingFavorite = false;
				refreshFavorite();
			},
			onFailure: function(transport) {
				ongoingFavorite = false;
			}
		});
	} else if (!ongoingFavorite) {
		ongoingFavorite = true;
		new Ajax.Request($('var_request_add_to_favorites').getValue(), {
			parameters: {
								hash: currentHash
						},
			onSuccess: function(transport) {
				currentFavorite = true;
				ongoingFavorite = false;
				refreshFavorite();
			},
			onFailure: function(transport) {
				ongoingFavorite = false;
			}
		});
	}
}

function purchase(event) {
	window.location = $('var_page_entry_order').getValue() + currentHash;
}

function facebook(event) {
	if (currentHash.substr(0, 4) == "eid=") {
		window.open('http://www.facebook.com/sharer.php?u=http://inspi.re/?e=' + currentHash.substr(4),'sharer','toolbar=0,status=0,width=626,height=436');
	} else if (currentHash.substr(0, 16) == "persistenttoken=") {
		new Ajax.Request($('var_request_persistent_token_to_eid').getValue(), {
			parameters: {
								persistenttoken: currentHash.substr(16)
						},
			onSuccess: function(transport) {
				window.open('http://www.facebook.com/sharer.php?u=http://inspi.re/?e=' + transport.responseText,'sharer','toolbar=0,status=0,width=626,height=436');
			},
			onFailure: function(transport) {
			}
		});
	}
}

function twitter(event) {
	if (currentHash.substr(0, 4) == "eid=") {
		window.location = 'http://www.twitter.com/home?status=http://inspi.re/?e=' + currentHash.substr(4);
	} else if (currentHash.substr(0, 16) == "persistenttoken=") {
		new Ajax.Request($('var_request_persistent_token_to_eid').getValue(), {
			parameters: {
								persistenttoken: currentHash.substr(16)
						},
			onSuccess: function(transport) {
				window.location = 'http://www.twitter.com/home?status=http://inspi.re/?e=' + transport.responseText;
			},
			onFailure: function(transport) {
			}
		});
	}
}

function initEntry() {
	$$('.purchase_icon').invoke('observe', 'click', purchase);

	viewerUid = $('var_viewer_uid').getValue();
}
