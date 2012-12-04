<?php    

add_action('admin_menu','mmww_admin_actions');

function mmww_admin_actions() {
	load_plugin_textdomain( MMWW_PLUGIN_NAME, MMWW_PLUGIN_DIR, 'languages' );
	add_options_page( __( 'Media Metadata Workflow Wizard', MMWW_PLUGIN_NAME ),
			__( 'Media Metadata', MMWW_PLUGIN_NAME ), 
			'upload_files', 
			MMWW_PLUGIN_NAME, 'mmww_admin' );
};

function mmww_admin() {
	load_plugin_textdomain( MMWW_PLUGIN_NAME, MMWW_PLUGIN_DIR, 'languages' );
	echo '<div id="icon-plugins" class="icon32"></div><div id="icon-upload" class="icon32"></div>';
	printf ('<div class="wrap"><h2>' . __( 'Media Metadata Workflow Wizard (Version %1s) Options', 'mmww' ) . '</h2></div>', MMWW_VERSION_NUM);
	_e( 'Stub page' );
};




