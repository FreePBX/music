<table id="musicgrid" data-url="ajax.php?module=music&amp;command=getJSON&amp;jdata=musiclist&amp;category=<?php echo $_REQUEST['category']?>" data-cache="false"  data-toggle="table" class="table table-striped">
	<thead>
		<tr>
			<th data-field="filename" class="col-md-6"><?php echo _("File")?></th>
			<th data-field="extension" class="col-md-1"><?php echo _("Type")?></th>
			<th data-field="file" data-formatter="playFormatter" class="col-sm-4"><?php echo _("Play") ?></th>
			<th data-field="link" data-formatter="musicFormat" class="col-md-1"><?php echo _("Action")?></th>
		</tr>
	</thead>
</table>
