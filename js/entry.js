
google.load("language", "1");

var loaded = false;
document.observe('dom:loaded', initEntry);
var currentHash = null;
var viewerUid = null;
var currentUid = null;
var currentPoints = null;
var currentOrd = null;
var movingNext = true;
var movingPrevious = true;
var loading = false;
var sendingComment = false;
var flickeringEffect = null;
var currentInsightfulOid = null;
var savedText = '';
var currentFavorite = false;
var ongoingFavorite = false;
var currentPurchaseable = false;
var currentBigCommentator = false;
var ishome = false;
var currentCompetitionStatus = -1;
var currentCid = 0;
var moderator = false;
var administrator = false;
var currentDisqualified = false;
var currentWidth = 900;
var loadTime = null;
var voting_blocked = false;

var delayedPreviousEntryLoad = false;
var delayedNextEntryLoad = false;

var tooShort = true;
var minimumWordCount = 0;

var hashCheckEnabled = true;
var coolirisloaded = false;
var commentHeaderInitialized = false;

var alreadyTranslated = new Array();

function checkHash() {
	if (hashCheckEnabled && currentHash != document.location.hash.substr(1)) {
		loaded = false;
		currentHash = document.location.hash.substr(1);

		getEntry();	
		getNextEntry(false);
		getPreviousEntry(false);
	
		loaded = true;
	}
}

function initCommentHeader() {
	if (!commentHeaderInitialized) {
		commentHeaderInitialized = true;
		$('comment_actions').show();
		$('comment_text').show();
	}
}

function starOut(event) {
	var element = Event.element(event);
	
	if (currentCompetitionStatus == 1) {		
		if (!element.hasClassName('star')) {
			 element = Event.findElement(event, '.star');
		}
		currentOrd = null;
		if (currentPoints != null)
			starsShowCurrentScore(currentPoints);
		else
			starsChange(5, $('var_graphics_path').getValue() + 'star-off.gif');	
	}
}

function starOver(event) {
	var element = Event.element(event);
	
	if (currentCompetitionStatus == 1) {		
		if (!element.hasClassName('star')) {
			 element = Event.findElement(event, '.star');
		}
		
		currentOrd = parseInt(element.identify().substring(5, 6));
		
		starsShowCurrentScore(currentOrd);
	}
}

function starClick(event) {
	var element = Event.element(event);
	
	if (currentCompetitionStatus == 1) {
		if (!element.hasClassName('star')) {
			 element = Event.findElement(event, '.star');
		}
		
		castVote(parseInt(element.identify().substring(5, 6)));
	}
}

function castVote(score) {
	var timeDifference = 0;
	var voteTime = new Date();
	if (loadTime != null)
		timeDifference = (voteTime.getTime() - loadTime.getTime()) / 1000;

	if (loaded) {
		new Ajax.Request($('var_request_cast_entry_vote').getValue(), {
						parameters: {
								hash: currentHash,
								points:  score,
								time: Math.max(0, timeDifference)
						},
						onSuccess: function(transport) {
							var result = transport.responseText.evalJSON();
							if (result['status'] == 1 || result['status'] == 2 || result['status'] == 3) {
								if (result['hash'] == currentHash) {
									var oldPoints = currentPoints;
									currentPoints = parseInt(result['points']);
									starsShowCurrentScore(currentPoints);
									if (oldPoints != currentPoints)
										flickeringEffect = new Effect.Pulsate('actual_stars', { pulses: 2, duration: 0.8 });
									setTimeout('$(\'actual_stars\').setOpacity(1.0);', 1000);
								} else if (result['eid'] == previousEntry['eid'])
									previousEntry['points'] = result['points'];
								else if (result['eid'] == nextEntry['eid'])
									nextEntry['points'] = result['points'];
							}
							reloadPoints();
							//sendingVote = false;
							
							if (result['status'] == 2) {
								showConfirmation('', $('var_too_fast_title').getValue(), $('var_too_fast_text').getValue(), null, $('var_voting_warning_ok').getValue());
								$('keyboard_hint').hide();
								$('actual_stars').hide();
								voting_blocked = true;
							} else if (result['status'] == 3) {
								showConfirmation('', $('var_same_vote_title').getValue(), $('var_same_vote_text').getValue(), null, $('var_voting_warning_ok').getValue());
								$('keyboard_hint').hide();
								$('actual_stars').hide();
								voting_blocked = true;
							}
						},
						onFailure: function(transport) {
							setTimeout('castVote(' + score + ')', 10000);
						}
					});
	}
}

function starsShowCurrentScore(score) {
	for (var i = 1; i <= score; i++)
		$('star_' + i).writeAttribute('src', $('var_graphics_path').getValue() + 'star-on.gif');
	for (var j = score + 1; j <= 5; j++)
		$('star_' + j).writeAttribute('src', $('var_graphics_path').getValue() + 'star-off.gif');
}

function starsChange(ord, changeTo) {
	for (var i = 1; i <= ord; i++)
		$('star_' + i).writeAttribute('src', changeTo);
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

function showEXIF() {
	$('entry_exif_link').hide();
	$('entry_exif_link_hide').show();
	$('exif').show();
}

function hideEXIF() {
	$('entry_exif_link_hide').hide();
	$('entry_exif_link').show();
	$('exif').hide();
}

function getEntry() {
	new Ajax.Request($('var_request_entry').getValue(), {
				parameters: {
							hash: currentHash
					},
					onSuccess: function(transport) {
						var entry = transport.responseText.evalJSON();
						
						if (entry['redirect']) {
							window.location = entry['redirect'];
						} else {		
							$('entry_mold').setStyle({width: entry['width'] + 'px'});
							currentWidth = entry['width'];
							
							$('entry').hide();
							$('entry_loader').show();
							loading = true;
							$('entry').writeAttribute('src', entry['src']);
							$('exif').update(entry['exif']);
							$('entry_exif_link').show();
							
							$('comments').update(entry['comments']);
							if ($('var_translate')) translate();
							
							$('comments_header').replace(entry['comments_header']);
							
							$('comments').show();
							$('comments_header').show();
							
							initCommentHeader();
							
							$('competition_description').replace(entry['competition_description']);
							$('vote_repartition').update(entry['vote_repartition']);

							if (entry['author']) $('entry_author').update(entry['author']);
							
							$$('.favorite_icon').invoke('observe', 'click', favorite);
							$$('.purchase_icon').invoke('observe', 'click', purchase);
							$$('.facebook_icon').invoke('observe', 'click', facebook);
							$$('.twitter_icon').invoke('observe', 'click', twitter);
							$$('.listing_item').each(function (e) { e.setOpacity(1.0); });	
							
							currentFavorite = entry['favorite'];
							refreshFavorite();
							
							currentPurchaseable = eval(entry['purchaseable']);
							if (currentPurchaseable) {
								$$('.purchase').invoke('show');
							} else {
								$$('.purchase').invoke('hide');
							}
							
							currentBigCommentator = eval(entry['big_commentator']);
							if (currentBigCommentator) {
								$('big_commentator').show();
							} else {
								$('big_commentator').hide();
							}
							
							currentUid = entry['uid'];
							currentCompetitionStatus = parseInt(entry['competition_status']);
							
							currentDisqualified = eval(entry['disqualified']);
							voting_blocked = eval(entry['voting_blocked']);
							
							if (ishome && currentDisqualified) {
								if (currentCompetitionStatus == 0)
									$('disqualified_entry').show();
								else
									$('disqualified_entry').hide();
									
								$('entry_disqualified_overlay').show();
							} else {	
								$('disqualified_entry').hide();
								$('entry_disqualified_overlay').hide();
							}
						
							if (currentCompetitionStatus == 0) {
								if (ishome) $('entry_edit_link').show();
								else $('entry_edit_link').hide();
								$('actual_stars').hide();
								$('vote_header').hide();
								$('keyboard_hint').hide();
								$('entry_author').hide();
								$('entry_delete_link').hide();
							} else if (currentCompetitionStatus == 1) {
								if (!voting_blocked) {
									$('actual_stars').show();
									$('keyboard_hint').show();
								} else {
									$('actual_stars').hide();
									$('keyboard_hint').hide();
								}
								
								$('vote_header').show();
								$('entry_author').hide();
								$('entry_delete_link').hide();
								$('entry_edit_link').hide();
							} else if (currentCompetitionStatus == 2) {
								$('entry_author').show();
								$('vote_header').hide();
								$('actual_stars').show();
								
								
								
								$('keyboard_hint').hide();
								$('entry_edit_link').hide();
								
								if (entry['uid'] == viewerUid) $('entry_delete_link').show();
								else $('entry_delete_link').hide();
							}
							
							currentPoints = parseInt(entry['points']);
							starsShowCurrentScore(currentPoints);
							if (entry['uid'] == viewerUid) {
								$$('.star').invoke('hide');
								$('receive_alerts').hide();
							} else if (!$('var_hide_stars')) {
								$$('.star').invoke('show');
								$('receive_alerts').show();
							}
								
							switch (currentCompetitionStatus) {
								case 0:
									if ($('submenu_compete'))
										$('submenu_compete').removeClassName('submenu_element').addClassName('submenu_element_selected');
									break;
								case 1:
									if ($('submenu_vote'))
										$('submenu_vote').removeClassName('submenu_element').addClassName('submenu_element_selected');
									break;
								case 2:
									if ($('submenu_hall_of_fame'))
										$('submenu_hall_of_fame').removeClassName('submenu_element').addClassName('submenu_element_selected');
									break;
							}
								
							if (ishome) {
								$('stars').hide();
								$('keyboard_hint').hide();
								document.title = entry['home_title'];
							} else {
								$('stars').show();
								document.title = entry['title'];
							}
							
							moderator = eval(entry['moderator']);
							administrator = eval(entry['administrator']);
							
							if (administrator && !moderator) $('entry_ability_link').show();
							
							if (moderator) {
								if (currentDisqualified) {
									if (currentCompetitionStatus == 1) $('actual_stars').hide();
									$('entry_requalify_link').show();
									$('entry_disqualify_link').hide();
									if (!ishome) $('disqualified_entry_moderator').show();
									$('entry_disqualified_overlay').show();
								} else {
									if (currentCompetitionStatus == 1 && !voting_blocked) $('actual_stars').show();
									$('entry_disqualify_link').show();
									$('entry_requalify_link').hide();
									$('disqualified_entry_moderator').hide();
									$('entry_disqualified_overlay').hide();
								}
							} else {
								$('entry_disqualify_link').hide();
								$('entry_requalify_link').hide();
								$('disqualified_entry_moderator').hide();
							}
							
							$$('.favorite').invoke('show');
							
							currentCid = entry['cid'];
							
							if ($('var_highlight')) highlightItem($($('var_highlight').getValue()), true);
							
							$('toggle_receive_alerts').checked = entry['entry_comment_notification'];
						}
					},
					onFailure: function(transport) {
						setTimeout('getEntry()', 10000);
					}
                });
	
}

function showEmptyCommentConfirmation(func) {
	showConfirmation('', $('var_comment_discard_title').getValue(), $('var_comment_discard_text').getValue(), $('var_delete_yes').getValue(), $('var_delete_no').getValue(), func);
}

function gotoNextEntry(event) {
	if ($F('comment_text') != '') {
		showEmptyCommentConfirmation(function () { $('comment_text').setValue(''); gotoNextEntry(event); });
		return;
	}
	
	if (loaded && !movingNext && !movingPrevious && !loading) {
		loadTime = null;
		movingNext = true;
		if (flickeringEffect != null) {
			flickeringEffect.cancel();
			if (voting_blocked) {
				$('actual_stars').hide();
			} else {
				$('actual_stars').show();
			}
			
			$('actual_stars').setOpacity(1.0);
		}
			
		if (nextEntry != null) {
			alreadyTranslated = new Array();
			
			previousEntry['width'] = currentWidth;
			previousEntry['src'] = $('entry').readAttribute('src');
			previousEntry['hash'] = currentHash;
			previousEntry['exif'] = $('exif').innerHTML;
			previousEntry['comments'] = $('comments').innerHTML;
			previousEntry['comments_header'] = '<div id="comments_header" class="comment_thread">' + $('comments_header').innerHTML + '</div>';
			previousEntry['points'] = currentPoints;
			previousEntry['uid'] = currentUid;
			previousEntry['favorite'] = currentFavorite;
			previousEntry['purchaseable'] = currentPurchaseable;
			previousEntry['big_commentator'] = currentBigCommentator;
			previousEntry['title'] = document.title;
			previousEntry['competition_status'] = currentCompetitionStatus;
			previousEntry['disqualified'] = currentDisqualified;
			previousEntry['vote_repartition'] = $('vote_repartition').innerHTML;
			previousEntry['entry_comment_notification'] = $('toggle_receive_alerts').checked;
			if ($('entry_author')) previousEntry['author'] = $('entry_author').innerHTML;
			
			$('entry_mold').setStyle({width: nextEntry['width'] + 'px'});
			currentWidth = nextEntry['width'];
			
			$('entry').hide();
			$('entry_loader').show();
			loading = true;
			if (!coolirisloaded) $('entry').writeAttribute('src', nextEntry['src']);
			$('exif').update(nextEntry['exif']);
			$('comments').update(nextEntry['comments']);
			if ($('var_translate')) translate();
			
			$('comment_text').setValue('');
			
			$('comments_header').replace(nextEntry['comments_header']);

			if (nextEntry['author']) $('entry_author').update(nextEntry['author']);
			
			$$('.favorite_icon').invoke('observe', 'click', favorite);
			$$('.purchase_icon').invoke('observe', 'click', purchase);
			$$('.facebook_icon').invoke('observe', 'click', facebook);
			$$('.twitter_icon').invoke('observe', 'click', twitter);
			$$('.listing_item').each(function (e) { e.setOpacity(1.0); });	
			
			currentFavorite = nextEntry['favorite'];
			refreshFavorite();
			
			currentPurchaseable = eval(nextEntry['purchaseable']);
			if (currentPurchaseable) {
				$$('.purchase').invoke('show');
			} else {
				$$('.purchase').invoke('hide');
			}
			
			currentBigCommentator = eval(nextEntry['big_commentator']);
			if (currentBigCommentator) {
				$('big_commentator').show();
			} else {
				$('big_commentator').hide();
			}
			
			hashCheckEnabled = false;
			currentHash = nextEntry['hash'];
			document.location.hash = currentHash;
			hashCheckEnabled = true;
			
			currentUid = nextEntry['uid'];
			currentPoints = parseInt(nextEntry['points']);
			starsShowCurrentScore(currentPoints);
			if (nextEntry['uid'] == viewerUid) {
				$$('.star').invoke('hide');
				$('receive_alerts').hide();
			} else if (!$('var_hide_stars')) {
				$$('.star').invoke('show');
				$('receive_alerts').show();
			}
				
			if (!ishome) document.title = nextEntry['title'];
			
			currentCompetitionStatus = parseInt(nextEntry['competition_status']);
			if (currentCompetitionStatus == 2 && currentUid == viewerUid)
				$('entry_delete_link').show();
			else
				$('entry_delete_link').hide();
				
			currentDisqualified = eval(nextEntry['disqualified']);
			
			if (moderator) {
				if (currentDisqualified) {
					if (currentCompetitionStatus == 1) $('actual_stars').hide();
					$('entry_requalify_link').show();
					$('entry_disqualify_link').hide();
					if (!ishome) $('disqualified_entry_moderator').show();
					$('entry_disqualified_overlay').show();
				} else {
					if (currentCompetitionStatus == 1 && !voting_blocked) $('actual_stars').show();
					$('entry_disqualify_link').show();
					$('entry_requalify_link').hide();
					$('disqualified_entry_moderator').hide();
					$('entry_disqualified_overlay').hide();
				}
			}
			
			$('vote_repartition').update(nextEntry['vote_repartition']);
			$('toggle_receive_alerts').checked = nextEntry['entry_comment_notification'];
							
			nextEntry = new Array();
			
			getNextEntry(false);
		} else {
			getNextEntry(true);
		}
	}
}

function getNextEntry(recall) {
	new Ajax.Request($('var_request_next_entry').getValue(), {
					parameters: {
							hash: currentHash
					},
					onSuccess: function(transport) {
						nextEntry = transport.responseText.evalJSON();
						if (recall) {
							nextEntry();
						} else {
							if (!loading) {
								if (!coolirisloaded) {
									var preload_image = new Image();
									preload_image.src = nextEntry['src'];
								}
							} else delayedNextEntryLoad = true;
						}
						movingNext = false;
					},
					onFailure: function(transport) {
						setTimeout('getNextEntry(' + recall + ')', 10000);
					}
                });
}

function gotoPreviousEntry(event) {
	if ($F('comment_text') != '') {
		showEmptyCommentConfirmation(function () { $('comment_text').setValue(''); gotoPreviousEntry(event); });
		return;
	}
	
	if (loaded && !movingPrevious && !movingNext && !loading) {
		loadTime = null;
		movingPrevious = true;
		if (flickeringEffect != null) {
			flickeringEffect.cancel();
			flickeringEffect.cancel();
			if (voting_blocked) {
				$('actual_stars').hide();
			} else {
				$('actual_stars').show();
			}
			
			$('actual_stars').setOpacity(1.0);
		}
		
		if (previousEntry != null) {
			alreadyTranslated = new Array(); 
			
			nextEntry['width'] = currentWidth;
			nextEntry['src'] = $('entry').readAttribute('src');
			nextEntry['hash'] = currentHash;
			nextEntry['exif'] = $('exif').innerHTML;
			nextEntry['points'] = currentPoints;
			nextEntry['comments'] = $('comments').innerHTML;
			nextEntry['comments_header'] = '<div id="comments_header" class="comment_thread">' + $('comments_header').innerHTML + '</div>';
			nextEntry['uid'] = currentUid;
			nextEntry['favorite'] = currentFavorite;
			nextEntry['purchaseable'] = currentPurchaseable;
			nextEntry['big_commentator'] = currentBigCommentator;
			nextEntry['competition_status'] = currentCompetitionStatus;
			nextEntry['title'] = document.title;
			nextEntry['disqualified'] = currentDisqualified;
			nextEntry['vote_repartition'] = $('vote_repartition').innerHTML;
			nextEntry['entry_comment_notification'] = $('toggle_receive_alerts').checked;
			if ($('entry_author')) nextEntry['author'] = $('entry_author').innerHTML;
			
			$('entry_mold').setStyle({width: previousEntry['width'] + 'px'});
			currentWidth = previousEntry['width'];
			
			$('entry').hide();
			$('entry_loader').show();
			loading = true;
			$('entry').writeAttribute('src', previousEntry['src']);
			$('exif').update(previousEntry['exif']);
			
			$('comments').update(previousEntry['comments']);
			if ($('var_translate')) translate();
			
			$('comment_text').setValue('');
			
			$('comments_header').replace(previousEntry['comments_header']);

			if (previousEntry['author']) $('entry_author').update(previousEntry['author']);
			
			$$('.favorite_icon').invoke('observe', 'click', favorite);
			$$('.purchase_icon').invoke('observe', 'click', purchase);
			$$('.facebook_icon').invoke('observe', 'click', facebook);
			$$('.twitter_icon').invoke('observe', 'click', twitter);
			$$('.listing_item').each(function (e) { e.setOpacity(1.0); });	
			
			currentFavorite = previousEntry['favorite'];
			refreshFavorite();
			
			currentPurchaseable = eval(previousEntry['purchaseable']);
			if (currentPurchaseable) {
				$$('.purchase').invoke('show');
			} else {
				$$('.purchase').invoke('hide');
			}
			
			currentBigCommentator = eval(previousEntry['big_commentator']);
			if (currentBigCommentator) {
				$('big_commentator').show();
			} else {
				$('big_commentator').hide();
			}
			
			hashCheckEnabled = false;
			currentHash = previousEntry['hash'];
			document.location.hash = currentHash;
			hashCheckEnabled = true;
			
			currentUid = previousEntry['uid'];
			currentPoints = parseInt(previousEntry['points']);
			starsShowCurrentScore(currentPoints);
			if (previousEntry['uid'] == viewerUid) {
				$$('.star').invoke('hide');
				$('receive_alerts').hide();
			} else if (!$('var_hide_stars')) {
				$$('.star').invoke('show');
				$('receive_alerts').show();
			}
				
			if (!ishome) document.title = previousEntry['title'];
			
			currentCompetitionStatus = parseInt(previousEntry['competition_status']);
			if (currentCompetitionStatus == 2 && currentUid == viewerUid)
				$('entry_delete_link').show();
			else
				$('entry_delete_link').hide();
				
			currentDisqualified = eval(previousEntry['disqualified']);
			
			if (moderator) {
				if (currentDisqualified) {
					if (currentCompetitionStatus == 1) $('actual_stars').hide();
					$('entry_requalify_link').show();
					$('entry_disqualify_link').hide();
					if (!ishome) $('disqualified_entry_moderator').show();
					$('entry_disqualified_overlay').show();
				} else {
					if (currentCompetitionStatus == 1 && !voting_blocked) $('actual_stars').show();
					$('entry_disqualify_link').show();
					$('entry_requalify_link').hide();
					$('disqualified_entry_moderator').hide();
					$('entry_disqualified_overlay').hide();
				}
			}
			
			$('vote_repartition').update(previousEntry['vote_repartition']);
			$('toggle_receive_alerts').checked = previousEntry['entry_comment_notification'];
			
			previousEntry = new Array();
			
			getPreviousEntry(false);
		} else {
			getPreviousEntry(true);
		}
	}
}

function getPreviousEntry(recall) {
	new Ajax.Request($('var_request_previous_entry').getValue(), {
					parameters: {
							hash: currentHash
					},
					onSuccess: function(transport) {
						previousEntry = transport.responseText.evalJSON();
						if (recall) {
							previousEntry();
						} else {
							if (!loading) {
								if (!coolirisloaded) {
									var preload_image = new Image();
									preload_image.src = previousEntry['src'];
								}
							} else delayedPreviousEntryLoad = true;
						}
						movingPrevious = false;
					},
					onFailure: function(transport) {
						setTimeout('getPreviousEntry(' + recall + ')', 10000);
					}
                });
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
								text: text
						},
						onSuccess: function(transport) {
							$('comment_text').setValue('');
							$('toggle_receive_alerts').checked = true;
			
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

function keyPress(e) {
	var code;
	if (!e) var e = window.event;
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	
	//console.log();
	
	if (Event.element(e).type != 'textarea' && Event.element(e).type != 'text' && $('stars').visible() && !e.altKey && !e.shiftKey && !e.ctrlKey && !e.metaKey) switch (code) {
		case 37:
		case 63234:
			gotoPreviousEntry(e);
			if (e.altKey) {
				e.cancelBubble = true;
				e.returnValue = false;
				if (e.stopPropagation) e.stopPropagation();
				return false;
			}
			return;
		case 39:
		case 63235:
			gotoNextEntry(e);
			if (e.altKey) {
				e.cancelBubble = true;
				e.returnValue = false;
				if (e.stopPropagation) e.stopPropagation();
				return false;
			}
			return;
		case 48:
		case 96:
		case 88:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(0);
			return;
		case 49:
		case 97:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(1);
			return;
		case 50:
		case 98:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(2);
			return;
		case 51:
		case 99:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(3);
			return;
		case 52:
		case 100:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(4);
			return;
		case 53:
		case 101:
			if ($('actual_stars').visible() && currentCompetitionStatus == 1) castVote(5);
			return;
		case 70:
			favorite();
			return;
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

function editEntry() {
	if (loaded) {
		window.location = $('var_edit_href').getValue() + currentCid;
	}
}

function disqualifyEntry() {
	if (loaded) {
		showConfirmation($('var_disqualify_href').getValue() + currentHash, $('var_disqualify_title').getValue(), $('var_disqualify_text').getValue(), $('var_disqualify_yes').getValue(), $('var_disqualify_no').getValue());
	}
}

function requalifyEntry() {
	if (loaded) {
		window.location =  $('var_requalify_href').getValue() + currentHash;
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

function entryLoaded(event) {
	$('entry_loader').hide();
	$('entry').show();
	loading = false;
	
	resizeFloaters();
	loadTime = new Date();
	
	if (delayedNextEntryLoad) {
		var preload_image = new Image();
		preload_image.src = nextEntry['src'];
		delayedNextEntryLoad = false;
	}
	
	if (delayedPreviousEntryLoad) {
		var preload_image = new Image();
		preload_image.src = previousEntry['src'];
		delayedPreviousEntryLoad = false;
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

function toggleReceiveAlerts() {
	var value = $('toggle_receive_alerts').checked;

	new Ajax.Request($('var_request_toggle_entry_comment_notification').getValue(), {
			parameters: {
								hash: currentHash,
								value: value
						},
			onSuccess: function(transport) {
			},
			onFailure: function(transport) {
				$('toggle_receive_alerts').checked = !value;
			}
		});
}

function initEntry() {
	$$('.star').invoke('observe', 'mouseout', starOut).invoke('observe', 'mouseover', starOver).invoke('observe', 'click', starClick);
	if ($('next')) $('next').observe('click', gotoNextEntry);
	if ($('previous')) $('previous').observe('click', gotoPreviousEntry);
	$('toggle_receive_alerts').observe('change', toggleReceiveAlerts);
	
	$$('.purchase_icon').invoke('observe', 'click', purchase);

	viewerUid = $('var_viewer_uid').getValue();
	ishome = eval($('var_ishome').getValue());
	
	$('entry').observe('load', entryLoaded);
	
	nextEntry = new Array();
	previousEntry = new Array();
	
	hashCheckEnabled = false
	currentHash = document.location.hash.substr(1);
	hashCheckEnabled = true

	getEntry();	
	getNextEntry(false);
	getPreviousEntry(false);
	
	document.onkeydown = keyPress;
	
	loaded = true;
	
	setInterval("checkHash()", 500);
}
