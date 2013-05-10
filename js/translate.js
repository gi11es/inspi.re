var template = new Template(Url.decode($('var_template').getValue()));

$$('.translation_save').invoke('observe', 'click', SaveTranslation);
$('search').observe('click', SearchTranslations);

function SaveTranslation(event) {
	var element = Event.findElement(event, '.translation_save');
	var translation = element.up('div').down('.translation_current_input');
	var name = element.up('div').down('.translation_name');
	var parent_div = element.up('.translation_block');
	
	new Ajax.Request($('var_request_translate').getValue(), {
					parameters: {
							name: name.getValue(),
							translation: translation.getValue()
					},
					onSuccess: function(transport) {
						var result = transport.responseText.evalJSON();
						if (result['result'] == 'success') {
							parent_div.fade();
							$('percent_left').replace(result['div']);
							new Effect.Pulsate($('percent_left'), { delay: 0.7, pulses: 2, duration: 0.8 });
							reloadPoints();
						} else parent_div.shake();
					}
                });
}

function SearchTranslations(event) {
	new Ajax.Request($('var_request_search_translation').getValue(), {
					parameters: {
							text: $('search_text').getValue()
					},
					onSuccess: function(transport) {
						$$('.translation_save').invoke('stopObserving', 'click', SaveTranslation);
						var json = transport.responseText.evalJSON();
						
						var result = '';

						if (json.results != undefined) {
							for ( var name in json.results ) {
								var english = json.results[name]['english'].split("\"").join("&quot;");
								var translation = json.results[name]['translation'].split("\"").join("&quot;");
								var english_height = 14 * ((english.length / 100) + 1);
								var translation_height = 14 * ((translation.length / 100) + 3);
								result += template.evaluate({name: name, translation: translation, english: english, translation_height: translation_height, english_height: english_height});
							} 
						} else result = $('var_no_results').getValue();
						
						$('results').innerHTML = result;
						$$('.translation_save').invoke('observe', 'click', SaveTranslation);
					}
                });
}