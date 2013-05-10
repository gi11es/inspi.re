google.load("language", "1");

document.observe('dom:loaded', initDiscuss);

function translate() {
	var language = $('var_language').getValue();
	
	$$('.recent_post').each(
		function (e) { 
			google.language.translate(e.innerHTML, "", language,  
				function(result) {  
					if (result.translation) {						
						e.update(result.translation);
					}
				}
			); 
		}
	);		
}

function changeRecentPostsAmount() {
	$('recent_posts_change_amount').hide();
	$('recent_posts_current_amount').hide();
	$('recent_posts_change_input').show();
}

function cancelRecentPostsAmount() {
	$('recent_posts_change_input').hide();
	$('recent_posts_change_amount').show();
	$('recent_posts_current_amount').show();
}

function saveRecentPostsAmount() {
	new Ajax.Request($('var_request_update_paging').getValue(), {
					parameters: {
							amount: $F('recent_posts_per_page'),
							paging: 'DISCUSS_RECENT_POSTS'
					},
					onSuccess: function(transport) {
						window.location = $('var_reload_url').getValue();
					}});
}

function initDiscuss(event) {
	if ($('var_translate')) translate();
}