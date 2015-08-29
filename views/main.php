<div class="container-fluid">
	<h1><?php echo $heading?></h1>
		<div class="row">
			<div class="col-sm-<?php echo ($request["action"] == "") ? "12" : "9"?>">
				<div class="fpbx-container">
					<div class="display <?php echo ($request["action"] == "") ? "no" : "full"?>-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<?php if ($request["action"] != "") {?>
				<div class="col-sm-3 hidden-xs bootnav">
					<div class="list-group">
						<?php echo load_view(__DIR__.'/bootnav.php', array('request' => $request ))?>
					</div>
				</div>
			<?php } ?>
		</div>
</div>
