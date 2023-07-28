<?php
# Copyright (c) 2011 Robert Munteanu (robert@lmn.ro)

# Zip export for MantisBT is free software: 
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation, 
# either version 2 of the License, or (at your option) any later version.
#
# Zip export plugin for MantisBT is distributed in the hope 
# that it will be useful, but WITHOUT ANY WARRANTY; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Zip export plugin for MantisBT.  
# If not, see <http://www.gnu.org/licenses/>.

class ZipExportPlugin extends MantisPlugin {
    
    public function register() {
        $this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");

        $this->version = "2.10";
        $this->requires = array(
			"MantisCore" => "2.0.0"
        );

        $this->author = "Cas Nuy";
        $this->contact = "cas-at-nuy.info";
        $this->url ="https://github.com/mantisbt-plugins/ZipExport";
		$this->page = "config";
    }
    
    public function hooks() {
    
        return array (
            'EVENT_MENU_FILTER' => 'add_zip_export_link',
        );
    }
    
    public function add_zip_export_link() {
        
        $t_required_level = plugin_config_get('export_access_level_threshold');
        
        if ( ! access_has_project_level( $t_required_level ) )
            return;

		return array( '<a class="btn btn-sm btn-primary btn-white btn-round" href="' .plugin_page( 'export.php' ) . '">' . plugin_lang_get( 'export_related_issues_link' ) . '</a>', );
    }
    
    function config() {
        return array(
            'export_access_level_threshold' => DEVELOPER
        );
    }
}
