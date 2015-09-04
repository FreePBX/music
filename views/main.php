<div class="container-fluid">
	<h1><?php echo $heading?></h1>
		<div class="row">
			<div class="col-sm-<?php echo (!empty($request["action"]) && !in_array($request["action"], array("addnew","editold"))) ? "9" : "12"?>">
				<div class="fpbx-container">
					<div class="display <?php echo (!empty($request["action"]) && !in_array($request["action"], array("addnew","editold"))) ? "full" : "no"?>-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<?php if (!empty($request["action"]) && !in_array($request["action"], array("addnew","editold"))) {?>
				<div class="col-sm-3 hidden-xs bootnav">
					<div class="list-group">
						<?php echo load_view(__DIR__.'/bootnav.php', array('request' => $request ))?>
					</div>
				</div>
			<?php } ?>
		</div>
</div>
