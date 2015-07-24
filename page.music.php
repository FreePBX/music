<?php /* $Id$ */
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
//
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$mh = \FreePBX::create()->Music;
$message='';
if(isset($mh->message)){
	$message = '<div class="well well-info">'.$mh->message.'</div>';
}
$heading = _("On Hold Music");
$request = $_REQUEST;
$request['view'] = isset($request['view'])?$request['view']:'';
$request['action'] = isset($request['action'])?$request['action']:'';
switch ($request['view']) {
	case 'form':
		switch($request["action"]){
			case "edit":
			case "updatecategory":
			case "deletefile":
				$heading .= ' - '.$request['category'];
				$content = load_view(__DIR__.'/views/updatecat.php', array('request' => $request, 'mh' => $mh));
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

$request["action"] = ($request["action"] == "delete") ? "" : $request["action"];
?>
<div class="container-fluid">
	<h1><?php echo $heading?></h1>
	<?php echo isset($message)?$message:''?>
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
						<?php echo load_view(__DIR__.'/views/bootnav.php', array('request' => $request ))?>
					</div>
				</div>
			<?php } ?>
		</div>
</div>
