<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header(  'ZipExport'  );
layout_page_begin( 'config.php' );

print_manage_menu();
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" > 
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-text-width"></i>
		<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 
<tr  >
<td class="category" colspan="3">

</td>
</tr>
<tr>
<td class="form-title" colspan="3">
<?php echo plugin_lang_get( 'title' ) . ': ' . lang_get( 'plugin_tasks_config' ) ?>
</td>
</tr>

<tr  >
<td class="category">
<?php echo plugin_lang_get( 'export_access_level_threshold' ) ?>
</td>
<td class="center">
<select name="export_threshold">
<?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'export_access_level_threshold'  ) ) ?>;
</select> 
</td>
</tr>

<tr>
<td class="center" colspan="3">
<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' ) ?>" />
</td>
</tr>

</table>
</div>
</div>
</div>
</div>
</form>
</div>
</div>
<?php
layout_page_end();