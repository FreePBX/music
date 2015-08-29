<div id="toolbar-all">
    <a class="btn" href="?display=music&amp;action=add&amp;type=files"><i class="fa fa-plus"></i> <?php echo _("Add Files Category")?></a>
		<a class="btn" href="?display=music&amp;action=add&amp;type=custom"><i class="fa fa-plus"></i> <?php echo _("Add Custom Application Category")?></a>
</div>
<table id="musicgrid" data-url="ajax.php?module=music&amp;command=getJSON&amp;jdata=categories" data-cache="false" data-cookie="true"
        data-cookie-id-table="moh-categories"
        data-toolbar="#toolbar-all"
        data-maintain-selected="true"
        data-show-columns="true"
        data-show-toggle="true"
        data-toggle="table"
        data-pagination="true"
        data-search="true" data-toggle="table" class="table table-striped">
	<thead>
		<tr>
			<th data-field="category" data-sortable="true"><?php echo _("Category")?></th>
			<th data-field="type" data-sortable="true"><?php echo _("Type")?></th>
			<th data-field="link" data-formatter="linkFormat"><?php echo _("Actions")?></th>
		</tr>
	</thead>
</table>
