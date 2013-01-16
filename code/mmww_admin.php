<?php    

add_action('admin_menu','mmww_admin_actions');
add_action('admin_init','mmww_register_setting');

function mmww_admin_actions() {
	load_plugin_textdomain( 'mmww', MMWW_PLUGIN_DIR, 'languages' );
	add_options_page( __( 'Media Metadata Workflow Wizard', 'mmww' ),
			__( 'Media Metadata', 'mmww' ), 
			'upload_files', 
			'mmww', 'mmww_admin_page' );
	
};

function mmww_register_setting(){
	register_setting( 'mmww', 'mmww_options', 'mmww_admin_validate_options' );
}

/**
 * emit the options heading for the audio section
 */
function mmww_admin_audio_text() {
	echo '<p>' . __('Options for audio files', 'mmww') . '</p>';
}

/**
 * emit the shortcode question
 */
function mmww_admin_audio_shortcode_text() {
	// get option 'audio' value from the database
	$options = get_option( 'mmww_options' );
	$choice = (empty( $options['audio_shortcode'] )) ? 'disabled' : $options['audio_shortcode'];

	$choices = array (
		'disabled' => __( '(Never)', 'mmww'),
		'custom' => __( 'Custom URL', 'mmww'),
		'attachment' => __( 'Attachment Page', 'mmww'),
		'media' => __( 'Media File', 'mmww'),
		'none' => __( 'None', 'mmww'),
		'always' => __( '(Always)', 'mmww'),
	);
	$pattern = '<input type="radio" id="mmww_admin_audio_shortcode" name="mmww_options[audio_shortcode]" value="%1$s" %2$s> %3$s';

	$f = array();
	foreach ($choices as $i => $k) {
		$checked =($choice == $i) ? 'checked' : '';
		$f[] = sprintf($pattern,$i,$checked,$k );
	}
	echo implode('&nbsp;&nbsp;&nbsp;&nbsp',$f);
	unset ($f);
}

/**
 * emit a text question
 */
function mmww_admin_text($item) {
	$options = get_option( 'mmww_options' );
	$value = (empty( $options[$item] )) ? ' ' : $options[$item];
	$pattern = '<input type="text" id="mmww_admin_%2$s" name="mmww_options[%2$s]" value="%1$s" size="80" />';
	$pattern = sprintf ($pattern, $value, $item);
	return $pattern;
}

function mmww_admin_audio_title_text() {
	echo mmww_admin_text('audio_title') . "\n";
}
function mmww_admin_audio_caption_text() {
	echo mmww_admin_text('audio_caption') . "\n";
}


/**
 * validate the options settings
 * @param array $input
 * @return validated array
 */
function mmww_admin_validate_options( $input ) {
	$valid = array();
	$valid['audio_shortcode'] = $input['audio_shortcode'];
	$valid['audio_title'] = $input['audio_title'];
	$valid['audio_title'] = $input['audio_title'];
	return $valid;
}

function mmww_admin_page() {
	load_plugin_textdomain( 'mmww', MMWW_PLUGIN_DIR, 'languages' );
	?>
	<div class="wrap">';
	<div id="icon-plugins" class="icon32"></div><div id="icon-upload" class="icon32"></div>
	<?php
	printf ('<div class="wrap"><h2>' . __( 'Media Metadata Workflow Wizard (Version %1s) Options', 'mmww' ) . '</h2></div>', MMWW_VERSION_NUM);
	_e( 'Test page' );
	
	
	add_settings_section(
			'mmww_admin_audio',
			__( 'Audio Settings', 'mmww' ),
			'mmww_admin_audio_text', 
		    'mmww' );
	
	add_settings_field(
			'mmww_admin_audio_shortcode',
			__( 'Insert audio player shortcode when author chooses this <em>Link To</em> style', 'mmww' ),
			'mmww_admin_audio_shortcode_text',
			'mmww',
			'mmww_admin_audio'
	);

	add_settings_field(
			'mmww_admin_audio_title',
			__( 'Audio title template', 'mmww' ),
			'mmww_admin_audio_title_text',
			'mmww',
			'mmww_admin_audio'
	);
	add_settings_field(
			'mmww_admin_audio_caption',
			__( 'Audio caption template', 'mmww' ),
			'mmww_admin_audio_caption_text',
			'mmww',
			'mmww_admin_audio'
	);
	
	
	?>
    <form action="options.php" method="post">

    <?php
    settings_fields('mmww');
    do_settings_sections('mmww');
    ?>

    <p class="submit">
    <input name="Submit" type="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','mmww');?>" />
    </p>
    </form></div>

    <?php
    
    
}