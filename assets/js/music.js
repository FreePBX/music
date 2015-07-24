function addstream_onsubmit() {
	var theForm = document.formstream;
	var msgInvalidCategoryName = "<?php echo _('Please enter a valid Category Name'); ?>";
	var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";
	var msgReservedCategoryName = "<?php echo _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name'); ?>";

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value))
		return warnInvalid(theForm.category, msgInvalidCategoryName);
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved")
		return warnInvalid(theForm.category, msgReservedCategoryName);
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, msgInvalidStreamName);

	return true;
}


function editstream_onsubmit() {
	var theForm = document.formstream;
	var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";

	defaultEmptyOK = false;
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, msgInvalidStreamName);

	return true;
}

function addcategory_onsubmit() {
	var theForm = document.localcategory;
	var msgInvalidCategoryName = _('Please enter a valid Category Name');
	var msgReservedCategoryName = _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name');

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value))
		return warnInvalid(theForm.category, msgInvalidCategoryName);
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved")
		return warnInvalid(theForm.category, msgReservedCategoryName);

	return true;
}

function linkFormat(value){
	var action = 'edit';
	if(value['type'] == 'streaming'){
		action = 'editstream'
	}
	var html = '<a href="?display=music&view=form&category='+value['category']+'&action='+action+'"><i class="fa fa-pencil"></i></a>';
	if(value['category'] !== 'default'){
		html += '&nbsp;<a href="?display=music&action=delete&category='+value['category']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	}
	return html;
}

function musicFormat(value){
	html = '<a href="?display=music&action=deletefile&filename='+value['filename']+'&category='+value['category']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	return html;
}
