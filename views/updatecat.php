<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
extract($request);
if ($mh->mpg123) {
	$mpg123html = '
		<!--Volume Adjustment-->
		<div class="element-container">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="form-group">
							<div class="col-md-3">
								<label class="control-label" for="volume">'. _("Volume Adjustment") .'</label>
								<i class="fa fa-question-circle fpbx-help-icon" data-for="volume"></i>
							</div>
							<div class="col-md-9">
								<select name="volume" id="volume" class="form-control">
									<option value="1.50">'. _("Volume 150%").'</option>
									<option value="1.25">'. _("Volume 125%").'</option>
									<option value="" selected>'. _("Volume 100%").'</option>
									<option value=".75"><'. _("Volume 75%").'</option>
									<option value=".5">'. _("Volume 50%").'</option>
									<option value=".25">'. _("Volume 25%").'</option>
									<option value=".1">'. _("Volume 10%").'</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<span id="volume-help" class="help-block fpbx-help-block">'. _("The volume adjustment is a linear value. Since loudness is logarithmic, the linear level will be less of an adjustment. You should test out the installed music to assure it is at the correct volume. This feature will convert MP3 files to WAV files. If you do not have mpg123 installed, you can set the parameter: <strong>Convert Music Files to WAV</strong> to false in Advanced Settings").'</span>
				</div>
			</div>
		</div>
		<!--END Volume Adjustment-->
	';
}else{
	 $mpg123html = '
	<!--Encode wav to mp3-->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="onlywav">'. _("Encode wav to mp3") .'</label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="onlywav"></i>
						</div>
						<div class="col-md-9">
							<span class="radioset">
							<input type="radio" name="onlywav" id="onlywavyes" value="1">
							<label for="onlywavyes">'._("Yes").'</label>
							<input type="radio" name="onlywav" id="onlywavno" value="0" CHECKED>
							<label for="onlywavno">'._("No").'</label>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="onlywav-help" class="help-block fpbx-help-block">'. _("Should wav encode to mp3").'</span>
			</div>
		</div>
	</div>
	<!--END Encode wav to mp3-->
	';
}
if ($category == "default") {
	$path_to_dir = $mh->mohpath; //path to directory u want to read.
} else {
	$path_to_dir = $mh->mohpath."/$category"; //path to directory u want to read.
}
if (file_exists("{$path_to_dir}/.random")) {
			?> <input type="submit" name="randoff" value="<?php echo _("Disable Random Play");?>"> <?php
		} else {
			?> <input type="submit" name="randon" value="<?php echo _("Enable Random Play");?>"> <?php
		}
?>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" data-name="music" class="active">
		<a href="#music" aria-controls="music" role="tab" data-toggle="tab">
			<?php echo _("music")?>
		</a>
	</li>
	<li role="presentation" data-name="settings" class="change-tab">
		<a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">
			<?php echo _("Settings")?>
		</a>
	</li>
</ul>
<div class="tab-content display">
	<div role="tabpanel" id="tab1" class="tab-pane active">
		<form enctype="multipart/form-data" name="upload" action="" method="POST">
		<input type="hidden" name="display" value="<?php echo $display?>">
		<input type="hidden" name="category" value="<?php echo "$category" ?>">
		<input type="hidden" name="action" value="addedfile">
		<!--Upload File-->
		<div class="element-container">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="form-group">
							<div class="col-md-3">
								<label class="control-label" for="mohfile"><?php echo _("Upload File") ?></label>
								<i class="fa fa-question-circle fpbx-help-icon" data-for="mohfile"></i>
							</div>
							<div class="col-md-9">
								<input type="file" name="mohfile" id="mohfile"/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<span id="mohfile-help" class="help-block fpbx-help-block"><?php echo _("")?></span>
				</div>
			</div>
		</div>
		<!--END Upload File-->
		<input type="button" class="form-control" value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);alert('<?php echo addslashes(_("Please wait until the page loads. Your file is being processed."))?>');" tabindex="<?php echo ++$tabindex;?>"/>
	</div>
	<div role="tabpanel" id="casettings" class="tab-pane">
		Tab2 Content		
	</div>
</div>

<input type="button" value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);alert('<?php echo addslashes(_("Please wait until the page loads. Your file is being processed."))?>');" tabindex="<?php echo ++$tabindex;?>"/>
<?php echo $mpg123html ?>
</form>