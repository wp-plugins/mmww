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
	echo '<p>' . __('These settings control the insertion of ID3 and other metadata from uploaded MP3 audio files into WordPress attachment data.', 'mmww') . '</p>';
}

/**
 * emit the options heading for the image section
 */
function mmww_admin_image_text() {
	echo '<p>' . __('These settings control the insertion of EXIF and other metadata from uploaded image files (JPG, PNG, TIFF) into WordPress attachment data.', 'mmww') . '</p>';
}

/**
 * emit the options heading for the PDF section
 */
function mmww_admin_application_text() {
	echo '<p>' . __('These settings control the insertion of PDF file properties into WordPress attachment data.', 'mmww') . '</p>';
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
 * @param string $item contains the options item name ... e.g. audio_caption
 */
function mmww_admin_text($item) {
	$options = get_option( 'mmww_options' );
	$value = (empty( $options[$item] )) ? ' ' : $options[$item];
	$pattern = '<input type="text" id="mmww_admin_%2$s" name="mmww_options[%2$s]" value="%1$s" size="80" />';
	$pattern = sprintf ($pattern, $value, $item);
	echo $pattern;
	echo "\n";
}

/**
 * validate the options settings
 * @param array $input
 * @return validated array
 */
function mmww_admin_validate_options( $input ) {
	$codes = explode('|','audio_shortcode|audio_title|audio_caption|image_title|image_caption|image_displaycaption|image_alt|application_title|application_caption');
	$valid = array();
	foreach ($codes as $code) {
		$valid[$code] = $input[$code];
	}
	return $valid;
}

function mmww_admin_page() {
	load_plugin_textdomain( 'mmww', MMWW_PLUGIN_DIR, 'languages' );
	?>
	<div class="wrap">';
	<div id="icon-plugins" class="icon32"></div><div id="icon-upload" class="icon32"></div>
	<?php	
	printf ('<div class="wrap"><h2>' . __( 'Media Metadata Workflow Wizard (Version %1s) Settings', 'mmww' ) . '</h2></div>', MMWW_VERSION_NUM);
	
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
			'mmww_admin_text',
			'mmww',
			'mmww_admin_audio',
			'audio_title'
	);
	add_settings_field(
			'mmww_admin_audio_caption',
			__( 'Audio description template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_audio',
			'audio_caption'
	);
	
	
	add_settings_section(
			'mmww_admin_image',
			__( 'Image Settings', 'mmww' ),
			'mmww_admin_image_text',
			'mmww' );
		
	add_settings_field(
			'mmww_admin_image_title',
			__( 'Image title template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_image',
			'image_title'
	);
	add_settings_field(
			'mmww_admin_image_alt',
			__( 'Image alternate text template (accessibility)', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_image',
			'image_alt'  /*wp_postmeta ... meta_key = '_wp_attachment_image_alt', value in meta_value */
	);

	add_settings_field(
			'mmww_admin_image_displaycaption',
			__( 'Image caption template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_image',
			'image_displaycaption'  /* wp_posts.post_excerpt */
	);
	add_settings_field(
			'mmww_admin_image_caption',
			__( 'Image description template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_image',
			'image_caption'   /* wp_posts.post_description */
	);
	
	
	add_settings_section(
			'mmww_admin_application',
			__( 'PDF Settings', 'mmww' ),
			'mmww_admin_application_text',
			'mmww' );
	
	add_settings_field(
			'mmww_admin_application_title',
			__( 'PDF title template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_application',
			'application_title'
	);
	add_settings_field(
			'mmww_admin_application_caption',
			__( 'PDF description template', 'mmww' ),
			'mmww_admin_text',
			'mmww',
			'mmww_admin_application',
			'application_caption'
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