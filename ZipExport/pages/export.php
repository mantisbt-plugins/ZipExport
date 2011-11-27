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
	
	$file = tempnam("tmp", "zip");
	$zip = new ZipArchive();
	$zip->open($file, ZIPARCHIVE::OVERWRITE);

	$t_user_id = auth_get_current_user_id();
	
	$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );
	
	do
	{
		$t_more = true;
		$t_row_count = count( $result );
		
		$row_number = 0;

		for( $i = 0; $i < $t_row_count; $i++ ) {
			$t_row = $result[$i];
			$t_bug = null;

			if ( is_blank( $f_export ) || in_array( $t_row->id, $f_bug_arr ) ) {
			    
			    $zip->addEmptyDir($t_row->id);
			    $zip->addFromString($t_row->id .'/bug.txt', 'Here is a bug file');
			    
			    if ( file_can_download_bug_attachments( $t_row->id, $t_user_id ) ) {
    			    $t_attachments = bug_get_attachments( $t_row->id );
    			    if ( sizeof ( $t_attachments ) > 0 ) {
    			        foreach ( $t_attachments as $t_attachment ) {
    			            
    			            $t_file_contents;
    			            
    			            switch ( config_get( 'file_upload_method' ) ) {
    			                
    			                case DISK:
    			                    $t_local_disk_file = file_normalize_attachment_path( $t_attachment['diskfile'], $t_project_id );
    			                    $t_file_contents = file_get_contents($t_local_disk_file);
    			                    break;
    			                    
    			                case FTP:
    			                    $t_local_disk_file = file_normalize_attachment_path( $t_attachment['diskfile'], $t_project_id );
    			                    
    			                    if ( !file_exists( $t_local_disk_file ) ) {
    			                        $ftp = file_ftp_connect();
    			                        file_ftp_get ( $ftp, $t_local_disk_file, $t_attachment['diskfile'] );
    			                        file_ftp_disconnect( $ftp );
    			                    }
    			                    $t_file_contents = file_get_contents($t_local_disk_file);
    			                    break;
    			                    
    			                case DATABASE:
    			                    
    			                    $t_content_query =  "SELECT content FROM $t_bug_file_table WHERE id=" . db_param();
    			                    $t_content_result = db_query_bound( $t_content_query, Array( $t_attachment['id'] ) );
    			                    $t_content_row = db_fetch_array( $t_content_result );
    			                    $t_file_contents = $t_content_row['content'];
    			                    break;
    			                  
    			            }
    			            
    			            $zip->addFromString($t_row->id.'/attachments/' . $t_attachment['filename'], $t_file_contents);
    			        }
    			    }
			    }
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
	
	readfile( $file );

	unlink ( $file );