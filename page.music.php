<?php /* $Id$ */
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$mh = \FreePBX::create()->Music;

$heading = _("On Hold Music");
$request = $_REQUEST;
switch ($request['view']) {
	case 'form':
		switch($request="action"){
			case "edit":
				$content = load_view(__DIR__.'/views/updateform.php', array('request' => $request, 'mh' => $mh));
				$content .= load_view(__DIR__.'/views/musiclist.php', array('request' => $request, 'mh' => $mh));
			break;
			case "add":
				$content = load_view(__DIR__.'/views/addcatform.php', array('request' => $request, 'mh' => $mh));
			break;
			case "addstream":
			case "editstream":
				$content = load_view(__DIR__.'/views/addstreamform.php', array('request' => $request, 'mh' => $mh));
			break;
		}
	break;
	default:
		$content = load_view(__DIR__.'/views/grid.php', array('request' => $request, 'mh' => $mh));
	break;
}


?>
<div class="container-fluid">
	<h1><?php $heading?></h1>
	<div class = "display full-border">
		<div class="row">
			<div class="col-sm-9">
				<div class="fpbx-container">
					<div class="display full-border">
						<?php echo $content ?>
					</div>
				</div>
			</div>
			<div class="col-sm-3 hidden-xs bootnav">
				<div class="list-group">
					<?php echo load_view(__DIR__.'/views/bootnav.php', array('request' => $request ))?>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="/admin/modules/module/assets/js/pinsets.js"></script>
