<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.


    // Code based on the excel_xml_export.php from MantisBT
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'string_api.php' );
	require_once( 'columns_api.php' );
	require_once( 'excel_api.php' );

	require( 'print_all_bug_options_inc.php' );
	
	const COLUMN_INDEX_LAST_NOTE_1 = 4;
	const COLUMN_INDEX_LAST_NOTE_2 = 10;

	auth_ensure_user_authenticated();

	$t_required_level = plugin_config_get('export_access_level_threshold');
	access_ensure_project_level( $t_required_level );
	
	$f_export = gpc_get_string( 'export', '' );

	helper_begin_long_process();
	
	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = 100;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	if ( $result === false ) {
		print_header_redirect( 'view_all_set.php?type=0&print=1' );
	}
	
	$t_export_title = excel_get_default_filename();
	
	header( 'Content-Type: application/zip; charset=UTF-8' );
	header( 'Pragma: public' );
	header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $t_export_title ) ) . '.zip"' ) ;

	$f_bug_arr = explode( ',', $f_export );
	
	$file = '/tmp/file.zip';
	$zip = new ZipArchive();
	$zip->open($file, ZIPARCHIVE::CREATE);

	do
	{
		$t_more = true;
		$t_row_count = count( $result );
		
		$row_number = 0;

		for( $i = 0; $i < $t_row_count; $i++ ) {
			$t_row = $result[$i];
			$t_bug = null;

			if ( is_blank( $f_export ) || in_array( $t_row->id, $f_bug_arr ) ) {
			    $zip->addFromString($t_row->id .'.txt', 'Here is a bug file');

			} #in_array
		} #for loop

		// If got a full page, then attempt for the next one.
		// @@@ Note that since we are not using a transaction, there is a risk that we get a duplicate record or we miss
		// one due to a submit or update that happens in parallel.
		if ( $t_row_count == $t_per_page ) {
			$t_page_number++;
			$t_bug_count = null;
			$t_page_count = null;

			$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
			if ( $result === false ) {
				$t_more = false;
			}
		} else {
			$t_more = false;
		}
	} while ( $t_more );
	
	$zip->close();
	
	echo file_get_contents($file);

	unlink ( $file );