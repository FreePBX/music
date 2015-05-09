<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
?>
<form name="localcategory" id="localcategory" action="" method="post" onsubmit="return addcategory_onsubmit();" class="fpbx-submit">
<input type="hidden" name="display" value="music">
<input type="hidden" name="action" value="addednew">
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
						<input type="text" class="form-control" id="category" name="category" value="">
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
</form>
