<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
extract($request, EXTR_SKIP);
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

$randomplay = file_exists("{$path_to_dir}/.random");

?>
<form enctype="multipart/form-data" name="upload" action="" method="POST" class="fpbx-submit" data-fpbx-delete="?display=music&amp;action=delete&amp;category=<?php echo $category?>">
<input type="hidden" name="display" value="<?php echo $display?>">
<input type="hidden" name="category" value="<?php echo $category?>">
<input type="hidden" name="action" value="updatecategory">
<!--Enable Random Play-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="erand"><?php echo _("Enable Random Play") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="erand"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" name="erand" id="erandyes" value="yes" <?php echo ($randomplay?"CHECKED":"") ?>>
						<label for="erandyes"><?php echo _("Yes");?></label>
						<input type="radio" name="erand" id="erandno" value="no" <?php echo ($randomplay?"":"CHECKED") ?>>
						<label for="erandno"><?php echo _("No");?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="erand-help" class="help-block fpbx-help-block"><?php echo _("Enable random playback of music for this category")?></span>
		</div>
	</div>
</div>
<!--END Enable Random Play-->
<!--Upload File-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="mohfile"><?php echo _("Upload File") ?></label>
					</div>
					<div class="col-md-9">
						<span class="btn btn-default btn-file">
						    <?php echo _("Browse")?>
						    <input type="file" class="form-control" name="mohfile" id="mohfile">
						</span>
						<span class="filename"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--END Upload File-->
<?php echo $mpg123html ?>
</form>
<br/>
<hr/>
<br/>
