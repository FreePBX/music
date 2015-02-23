<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
extract($request);
if (isset($category)){
	if ($category == "default") {
		$path_to_dir = $mh->mohpath; //path to directory u want to read.
	} else {
		$path_to_dir = $mh->mohpath."/$category"; //path to directory u want to read.
	}
	if (file_exists("{$path_to_dir}/.custom")) {
		$application = file_get_contents("{$path_to_dir}/.custom");
		$application = explode("\n",$application);
		if (isset($application[1])) {
			$format = explode('=',$application[1],2);
			$format = $format[1];
		} else {
			$format = "";
		}
	} else {
		$application = false;
	}
	$formaction = "editednewstream";
	$onsubmit = 'editstream_onsubmit();';
	$readonly = 'readonly';
}else{
	$formaction = "addednewstream";
	$onsubmit = 'addstream_onsubmit();';
}
?>
<form name="streamform" action="" method="post" onsubmit="<?php echo $onsubmit?>">
<input type="hidden" name="display" value="music">
<input type="hidden" name="action" value="<?php echo $formaction?>">
<input type="hidden" name="view" value="form">

<!--Category Name-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="category"><?php echo _("Category Name") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="category"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="category" name="category" value="<?php echo $category?>" <?php echo $readonly?>>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="category-help" class="help-block fpbx-help-block"><?php echo _("Allows you to Set up Different Categories for music on hold.  This is useful if you would like to specify different Hold Music or Commercials for various ACD Queues.")?></span>
		</div>
	</div>
</div>
<!--END Category Name-->
<!--Application-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="stream"><?php echo _("Application") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="stream"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="stream" name="stream" value="<?php echo $application[0]?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="stream-help" class="help-block fpbx-help-block"><?php echo _("This is the \"application=\" line used to provide the streaming details to Asterisk. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?></span>
		</div>
	</div>
</div>
<!--END Application-->
<!--Optional Format-->
<!--This should probably be a generated select-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="format"><?php echo _("Optional Format") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="format"></i>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control" id="format" name="format" value="<?php echo htmlentities($format)?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="format-help" class="help-block fpbx-help-block"><?php echo _("Optional value for \"format=\" line used to provide the format to Asterisk. This should be a format understood by Asterisk such as ulaw, and is specific to the streaming application you are using. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?></span>
		</div>
	</div>
</div>
<!--END Optional Format-->
</form>
