<table id="musicgrid" data-url="ajax.php?module=music&command=getJSON&jdata=musiclist&category=<?php echo $_REQUEST['category']?>" data-cache="false"  data-toggle="table" class="table table-striped">
	<thead>
		<tr>
			<th data-field="filename" class="col-md-8"><?php echo _("File")?></th>
			<th data-field="extension" class="col-md-2"><?php echo _("Type")?></th>
			<th data-field="link" data-formatter="musicFormat" class="col-md-2"><?php echo _("Action")?></th>
		</tr>
	</thead>
</table>
