<?php

/* metadata display filters */

add_filter('mmww_filter_metadata', 'mmww_filter_all_metadata' ,10, 2);
function mmww_filter_all_metadata ($meta, $mime) {
	
	/* fix up the copyright statement to present in a compliant way */
	if ( ! empty ($meta['copyright']) ) {
		$meta['copyright'] = __( "Copyright &#169; ", 'mmww' ) . $meta['copyright'];
	}
	
	/* get a creation time string from the timestamp */
	if ( ! empty ($meta['created_timestamp']) ) {
		$meta['created_time'] = 
				date_i18n( get_option('date_format'), $meta['created_timestamp'] ) . ' ' .
		     	date_i18n( get_option('time_format'), $meta['created_timestamp'] );
	}

	/* eliminate redundant items from the metadata */
	$tozap = array('created_timestamp','aperture','shutter_speed', 'warning');
	foreach ($tozap as $zap) {
		unset ($meta[$zap]);
	}

	/* eliminate zero or empty items */
	foreach ($meta as $key => $val) {
		if ( is_string($val) && strlen($val) == 0 ) {
			unset ($meta[$key]);
		}
		if ( is_numeric($val) && $val == 0 ) {
			unset ($meta[$key]);
		}
	}
	return $meta;
}

//add_filter('mmww_filter_metadata', 'mmww_filter_image_metadata' ,8, 2);
//function mmww_filter_image_metadata ($meta, $mime) {
//	return $meta;
//}

//add_filter('mmww_filter_metadata', 'mmww_filter_audio_metadata' ,8, 2);
//function mmww_filter_audio_metadata ($meta, $mime) {
//	return $meta;
//}

//add_filter('mmww_filter_metadata', 'mmww_filter_application_metadata' ,8, 2);
//function mmww_filter_application_metadata ($meta, $mime) {
//	return $meta;
//}



add_filter('media_send_to_editor', 'mmww_audio_send_to_editor', 11, 3);

/**
 * adjust hyperlinks and other embed codes for audio shortcode upon sending them to the editor
 *
 * @since 2.5.0
 *
 * @param string $html  the string to be inserted in the editor, filtered
 * @param int $attachment_id attachment id
 * @param array $attachment
 * @return filtered html string
 */
function mmww_audio_send_to_editor($html, $attachment_id, $attachment) {
	$post = get_post($attachment_id);	
	/* double check we're in the right neighborhood before bashing the html */
	$mime = $post->post_mime_type;
	if ( $mime == 'audio/mpeg'  && stripos( $html,'[audio ' )) {
		/* correct mime type AND attempt to post an audio shortcode. */
		$meta = array();
		$jsonmeta = get_post_meta($post->ID,MMWW_POSTMETA_KEY,true);
		if (! empty ($jsonmeta)) {
			$meta = json_decode($jsonmeta, true);
		}
		/* filter the metadata for display according to the MIME type, extensibly */
		/* audio file; insert a player button matching the Jetpack [audio] shortcode syntax */
		$url = wp_get_attachment_url($post->ID);
		$files = esc_attr($url);
		/* the [audio[ titles and artists lists are comma separated in Jetpack shortcode, so scrub embedded commas */
		$titles = mmww_nocommas(mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 'album')));
		$artists = mmww_nocommas(mmww_getmetastring ('; ',$meta, array('creditlead', 'credit', 'creditconductor')));
		$html = "[audio $files|titles=$titles|artists=$artists]";
	}		
	return $html;
}

/**
 * turn audio/mpeg into audio, image/tiff into image, etc
 * @param string $f MIME type
 * @return string basic data type
 */
function mmww_getfiletype ($f) {
	$ff = explode( '/', $f );
	$filetype = $ff[0];
	$filetype = strtolower( $filetype );
	return $filetype;
	
}


add_filter('wp_read_image_metadata', 'mmww_wp_read_image_metadata',11,3);

/**
 * filter to extend the stuff in wp_admin/includes/image.php
 *        and store the metadata in the right place.
 *        This function handles xmp, iptc, exif, and id3v2
 *        and so copes pretty well with pdf, mp3, etc.
 * @param array $meta  associative array containing pre-loaded metadata
 * @param string $file file name
 * @param string $sourceImageType encoding of a few MIME types
 * @return bool|array False on failure. Image metadata array on success.
 */
function mmww_wp_read_image_metadata ($meta, $file, $sourceImageType) {
		
	if ( ! file_exists( $file ) ) {
		return $meta;
	}
	
	$ft = wp_check_filetype( $file );
	$filetype = $ft['type'];
	$filetype = mmww_getfiletype($filetype);
	
	/* try to get the metadata from the various sources */
	require_once 'xmp.php';
	
	/* merge up the metadata  -- later merges  overwrite earlier ones*/
	
	switch ($filetype) {
		case 'audio':
			require_once 'id3.php';
			$newmeta = mmww_get_id3_metadata ($file);
			$meta = array_merge($meta, $newmeta);
			$newmeta = mmww_get_xmp_audio_metadata ($file);
			$meta = array_merge($meta, $newmeta);
			break;

		case 'image':
			require_once 'exif.php';
			$newmeta = mmww_get_exif_metadata ($file);
			$meta = array_merge($meta, $newmeta);
			require_once 'iptc.php';
			$newmeta = mmww_get_iptc_metadata ($file);
			$meta = array_merge($meta, $newmeta);
			break;

		case 'application':
			break;
			
		default:
			$meta['warning'] = __('Unrecognized media type in file ','mmww') . "$file ($filetype)";
	}

	/* all kinds of files (including pdf) */
	$newmeta =  mmww_get_xmp_metadata ($file);
	$meta = array_merge($meta, $newmeta);

	return $meta;
}

/**
 * remove all commas and apostrophes in a string
 * @param string $s
 * @return string with blanks where commas and apostrophes were.
 */
function mmww_nocommas($s) {
	$t = implode (' ', explode(', ',$s ));
	$t = implode (' ', explode(',',$t ));
	$t = implode ('',  explode("'",$t ));
	return $t;
}

/**
 * make a string from the non empty members of a metadata array
 * @param string $glue between members
 * @param array $meta metadata
 * @param array $tags like ('creator', 'producer' )
 * @return string 
 */
function mmww_getmetastring($glue, $meta, $tags) {
	$result = array();
	foreach ($tags as $tag) {
		if ( ! empty ($meta[$tag])) {
			$result[] = $meta[$tag];
		}
	}
	if (count($result) <= 0) {
		return '';
	}
	return implode($glue, $result);
}

/**
 * Retrieve the pathname for an attachment's file.
 *   (cribbed from post.php)
 * @since 2.1.0
 *
 * @param int $post_id Attachment ID.
 * @return string
 */
function mmww_get_attachment_path( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	if ( 'attachment' != $post->post_type )
		return false;

	$result = '';
	if ( $file = get_post_meta( $post->ID, '_wp_attached_file', true) ) { //Get attached file meta
		if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) { //Get upload directory
			if ( 0 === strpos($file, $uploads['basedir']) ) { //Check that the upload base exists in the file location
				$result = $file;
			}	
			elseif ( false !== strpos($file, 'wp-content/uploads') ) {
				$result = $uploads['basedir'] . substr( $file, strpos($file, 'wp-content/uploads') + 18 );
			}
			else {
				$result = $uploads['basedir'] . "/$file"; //Its a newly uploaded file, therefor $file is relative to the basedir.
			}
		}
	}

	if ( empty( $result ) )
		return false;

	return $result;
}

add_filter('attachment_fields_to_edit', 'mmww_attachment_fields_to_edit',20,2);
/**
 *  Edit fields in the media upload editor
 * @param associative array of fields $file
 * @param associative array describing $post
 * @return updated array of fields
 */
function mmww_attachment_fields_to_edit($fields,$post) {
	$metadata_refreshed = false;
	$mime = get_post_mime_type($post->ID);
	$file = mmww_get_attachment_path( $post->ID );
	
	/* do we have metadata already stored for this one? */
	$jsonmeta = get_post_meta($post->ID,MMWW_POSTMETA_KEY,true);
	if (! empty ($jsonmeta)) {
		$meta = json_decode($jsonmeta, true);
		$metadata_refreshed = false;
	}
	else {
		/* get the metadata from the media file. */
		$meta = array();
		if ($file) {
			$meta = wp_read_image_metadata( $file );
		}
		/* filter the metadata for display according to the MIME type, extensibly */
		$meta = apply_filters( 'mmww_filter_metadata', $meta, $mime );
		/* pack it up to store in the post meta table */
		$jsonmeta = json_encode ($meta);
		add_post_meta($post->ID,MMWW_POSTMETA_KEY,$jsonmeta);
		$metadata_refreshed = true;
	}
	
		/* this field is only needed when embedding an attachment */
	if (isset( $fields['url'] ) && isset( $fields['url']['html'] ) ) {
		if ($mime == 'audio/mpeg') {
			/* audio file; insert a player button matching the Jetpack [audio] shortcode syntax */
			$url = wp_get_attachment_url($post->ID);
			$files = esc_attr($url);
			/* the [audio[ titles and artists lists are comma separated, so entitize embedded commas */
			$titles = mmww_nocommas(mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 'album')));
			$artists = mmww_nocommas(mmww_getmetastring ('; ',$meta, array('creditlead', 'credit', 'creditconductor')));
			$playertag = "[audio $files|titles=$titles|artists=$artists]";
			/* translators: name of a UI button element in the insert media popup */
			$playerbuttonname = _x( 'Audio Player' , 'button_name' , 'mmww');
			$postid = $post->ID;
		
			$fields['url']['html'] .=
				"<button type='button' class='button urlaudioplayer audio-player-$postid' data-link-url='$playertag'>" .
				"$playerbuttonname</button>";
		}
	}
	/* do we have refreshed metadata? if so, update the fields */
	if ($metadata_refreshed) {
	
		$string = mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 
				'album','creditlead', 'credit', 'creditconductor','created_time', 'copyright'));
		$fields['post_excerpt']['value'] = $string;
		
		$string = '';
		if (mmww_getfiletype( $mime ) == 'image') {
			$fields['image_alt']['value'] = mmww_getmetastring ('; ',$meta, array('title','credit'));
		}		

		$string .= '<table><tr><td>tag</td><td>value</td></tr>' . "\n";
		foreach ($meta as $tag => $value) {
			$string .= '<tr><td>' . $tag . '</td><td>' . $value .'</td></tr>' . "\n";
		}
		$string .= '</table>' . "\n";
		
		$fields['post_content']['value'] = $string;
	}
	return $fields;
}

?>