var progressid= '';
document.observe('dom:loaded', initEditablePicture);

function UUID() {
	uuid = "";
    for (i = 0; i < 32; i++) {
      uuid += Math.floor(Math.random() * 16).toString(16);
    }
    return uuid;
}

function resetPicture() {
	progressid = UUID();
	$('picture_upload_form').action = $('originalaction').getValue() + '&X-Progress-ID=' + progressid;
	$('reset_picture').blur();
	new Ajax.Request($('var_reset_url').getValue(), {
                onSuccess: function(transport) {
                 	$('picture_big').writeAttribute('src', transport.responseText);
                 	$('edit_cropping_holder').hide();
                 	$('reset_picture_holder').hide();
                 	
                 	if ($('picture_huge')) {
                 		$('picture_huge').writeAttribute('src', '');
    					$('picture_huge').hide();
    				}
    				
    				reloadPoints();
                }
        });
}

function checkFinished() {
	var result = window.frames['uploadframe'].document.body.innerHTML;
	
	if (result == '') {
		setTimeout("checkFinished()", 1000);
	} else {
		window.frames['uploadframe'].document.body.innerHTML = '';
		progressid = UUID();
		$('picture_upload_form').action = $('originalaction').getValue() + '&X-Progress-ID=' + progressid;
		
		$('picture_loader').hide();
		var picture_sizes = result.evalJSON();
		
		if (picture_sizes['status'] == 2) {				
			$('picture_big').writeAttribute('src', picture_sizes['big']);
			$('edit_cropping_holder').show();
			$('reset_picture_holder').show();
			$('profile_picture_controls').show();
			if ($('picture_huge')) {
				$('picture_huge').writeAttribute('src', picture_sizes['huge']);
				$('picture_huge').show();
			}
			$('progress_bar_container').hide();
			reloadPoints(); 
		} else {
			$('picture_loader').hide();
			alert($('var_upload_error').getValue());
		}
		$('picture_upload_browse').enable();
	}
}

function checkProgress() {
	new Ajax.Request($('var_request_upload_progress').getValue(), {
			method: 'get',
			requestHeaders: new Array('X-Progress-ID', progressid),
			onSuccess: function(transport) {
				var upload = eval(transport.responseText);
				
				var percent = 0;
				if (upload.state == 'done')
			 		percent = 100;
				else if (upload.state == 'starting')
					percent = 0;
				else if (upload.state == 'error') {
					percent = 0;
					$('progress_bar_container').hide();
					$('picture_loader').hide();
					progressid = UUID();
					$('picture_upload_form').action = $('originalaction').getValue() + '&X-Progress-ID=' + progressid;
					$('picture_upload_browse').enable();
					alert($('var_upload_size_error').getValue());
				}
				else
					percent =  Math.ceil(100.0 * upload.received / upload.size);
				
				if (percent < 100 && upload.state != 'error') {
					if (!$('progress_bar_container').visible()) {
						$('progress_bar_container').show();
					}
					$('progress_bar').setStyle({width: percent + '%'});
					$('progress_bar_text').update(percent + '% ' + $('var_uploaded_text').getValue());
				} else if (upload.state != 'error') {
					$('progress_bar_container').hide();
					$('picture_loader').show();
				}
				
				if (upload.state == 'uploading' || upload.state == 'starting')
					setTimeout("checkProgress()", 1000);
				else if (upload.state == 'done')
					checkFinished();
			}
	});
}

function startUpload(event) {
	setTimeout("checkProgress()", 1000);
	return true;
}

function fileChosen(event) {
	setTimeout("checkProgress()", 1000);
	$('picture_upload_form').submit();
	$('picture_upload_browse').clear();
	$('picture_upload_browse').disable();
}

function initEditablePicture() {
	if ($('originalaction')) {
		progressid = UUID();
		$('picture_upload_form').action = $('originalaction').getValue() + '&X-Progress-ID=' + progressid;
		$('picture_upload_form').observe('submit', startUpload);
		$('picture_upload_browse').observe('change', fileChosen);
	}
 }