<?php

require_once 'png.php';

//add_action( 'media_buttons', 'mmww_media_buttons' );

/* metadata display filters; internal to mmww */

add_filter('mmww_filter_metadata', 'mmww_filter_all_metadata' ,10, 1);

/**
 * hook function for filter internal to mmww 
 * @param array $meta of metadata key/val strings
 * @return array of metadata
 */
function mmww_filter_all_metadata ($meta) {
	
	/* fix up the copyright statement to present in a compliant way */
	if ( ! empty ($meta['copyright']) ) {
		$meta['copyright'] = __( "Copyright &#169; ", 'mmww' ) . $meta['copyright'];
	}
	
	/* get a creation time string from the timestamp */
	if ( ! empty ($meta['created_timestamp']) ) {
		/* do the timezone stuff right; png creation time is in local time */
		$previous = date_default_timezone_get();
		@date_default_timezone_set(get_option('timezone_string'));
		$meta['created_time'] = 
			date_i18n( get_option('date_format'), $meta['created_timestamp'] ) . ' ' .
	     	date_i18n( get_option('time_format'), $meta['created_timestamp'] );
		@date_default_timezone_set($previous);	
	}

	/* eliminate redundant items from the metadata */
	$tozap = array('aperture','shutter_speed', 'warning');
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

add_filter('wp_update_attachment_metadata', 'mmww_update_attachment_metadata',10,2);

/**
 * Filter to handle extra stuff in attachment metadata update
 * @param array $data attachment data array
 * @param int $id attachment id 
 * @return data, modified as needed
 */
function mmww_update_attachment_metadata ($data, $id) {

	$meta = $data['image_meta'];
	$updates = array();

	/* handle the caption for photos, which goes into wp_posts.post_excerpt. */
	if (!empty($meta['displaycaption'])) {
		$updates['post_excerpt'] = $meta['displaycaption'];
	}
	
	/* update the attachment post_date and post_date_gmt if that's what the user wants and the metadata has it */
	$options = get_option( 'mmww_options' );
	$choice = (empty( $options['use_creation_date'] )) ? 'no' : $options['use_creation_date'];
	if ($choice == 'yes' && !empty($meta['created_timestamp'])) {
		$previous = date_default_timezone_get();
		@date_default_timezone_set(get_option('timezone_string'));
		$ltime =  date( 'Y-m-d H:i:s', $meta['created_timestamp'] );
		$updates['post_date'] = $ltime;
		$ztime = gmdate( 'Y-m-d H:i:s', $meta['created_timestamp'] );
		$updates['post_date_gmt'] = $ztime;
		@date_default_timezone_set($previous);
	}
	
	/* make any updates needed to the posts table. */
	if (!empty ($updates)) {
		global $wpdb;
		$where = array( 'ID' => $id );	
		$wpdb->update( $wpdb->posts, $updates, $where );
	}

	/* handle the alt text (screenreader etc) which goes into a postmeta row */
	if (!empty($meta['alt'])) {
		update_post_meta ($id, '_wp_attachment_image_alt', $meta['alt']);
	} 
	
	/* stash tne metadata itself so we don't have to reread it from the file for site visitors */	
	update_post_meta ($id, MMWW_POSTMETA_KEY, json_encode($meta));
	
	return $data;
}

add_filter('wp_read_image_metadata', 'mmww_read_media_metadata',11,3);
add_filter('wp_read_image_metadata', 'mmww_apply_template_metadata',90,3);

/**
 * filter to extend the stuff in wp_admin/includes/image.php
 *        and store the metadata in the right place.
 *        This function handles xmp, iptc, exif, png, and id3v2
 *        and so copes pretty well with pdf, mp3, jpg, png etc.
 * @param array $meta  associative array containing pre-loaded metadata
 * @param string $file file name
 * @param string $sourceImageType encoding of a few MIME types
 * @return bool|array False on failure. Image metadata array on success.
 */
function mmww_read_media_metadata ($meta, $file, $sourceImageType) {

	if ( ! file_exists( $file ) ) {
		return $meta;
	}
	//TODO hang on to the file name to use as a title if nothing else works out.
	$meta_accum = array();

	$ft = wp_check_filetype( $file );
	$filetype = $ft['type'];
	$filetype = mmww_getfiletype($filetype);

	/* merge up the metadata  -- later merges  overwrite earlier ones*/
	switch ($filetype) {
		case 'audio':
			require_once 'xmp.php';
			require_once 'id3.php';
			$newmeta = mmww_get_id3_metadata ($file);
			$meta_accum = array_merge($meta_accum, $newmeta);
			$newmeta = mmww_get_xmp_audio_metadata ($file);
			$meta_accum = array_merge($meta_accum, $newmeta);
			$meta_accum['mmww_type'] = $filetype;
			break;

		case 'image':
			require_once 'exif.php';
			$newmeta = mmww_get_exif_metadata ($file);
			$meta_accum = array_merge($meta_accum, $newmeta);
			require_once 'png.php';
			$newmeta = mmww_get_png_metadata ($file);
			$meta_accum = array_merge($meta_accum, $newmeta);
			require_once 'iptc.php';
			$newmeta = mmww_get_iptc_metadata ($file);
			$meta_accum = array_merge($meta_accum, $newmeta);
			$meta_accum['mmww_type'] = $filetype;
			break;

		case 'application':
			$meta_accum['mmww_type'] = $filetype;
			/* this is for pdf. Processing below for that */
			break;
				
		default:
			$meta['warning'] = __('Unrecognized media type in file ','mmww') . "$file ($filetype)";
	}

	/* all kinds of files (including pdf), look for Adobe XMP publication metadata */
	require_once 'xmp.php';
	$newmeta =  mmww_get_xmp_metadata ($file);
	if (! empty ($newmeta)) {
		$meta_accum = array_merge($meta_accum, $newmeta);
		$meta_accum['mmww_type'] = $filetype;
	}
	
	$meta = array_merge($meta, $meta_accum);

	return $meta;
}


/**
 * filter to use the metadata to construct title and caption
 *        using appropriate templates
 * @param array $meta  associative array containing pre-loaded metadata
 * @param string $file file name
 * @param string $sourceImageType encoding of a few MIME types
 * @return bool|array False on failure. Image metadata array on success.
 */
function mmww_apply_template_metadata ($meta, $file, $sourceImageType) {

	if ( empty ($meta) && empty ($meta['mmww_type'])) {
		/* if there's no mmww metadata detected, don't do anything more */
		return $meta;
	}
	
	$cleanmeta = apply_filters( 'mmww_filter_metadata', $meta );
	
	/* $meta[caption] goes into wp_posts.post_content. This is shown as "description" in the UI.
	 * $meta[title] goes into wp_posts.post_title. This is shown as "title"
	 * we don't have a $meta item to go into wp_posts.post_excerpt. This is shown as "caption" in the UI.
	 */
	
	$codes = array ('title', 'caption', 'alt', 'displaycaption');
	$newmeta = array();	
	foreach ($codes as $code) {
		$codetype = $meta['mmww_type'].'_'.$code;
		$gen = mmww_make_string ($cleanmeta,$codetype);
		if(!empty($gen)) {
			$newmeta[$code] = $gen;
		}
	}
	
	$meta = array_merge($cleanmeta, $newmeta);
	return $meta;
}

/**
 * remove all commas and apostrophes from a string
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
 * Retrieve the pathname in the file system for an attachment's file.
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
				$result = $uploads['basedir'] . "/$file"; //Its a newly uploaded file, therefore $file is relative to the basedir.
			}
		}
	}

	if ( empty( $result ) )
		return false;

	return $result;
}

/**
 * make a desciption or caption string from the metadata and the template
 * @param array $meta metadata array
 * @param string $item which template (e.g.  audio_caption)
 *
 * @return description or caption string
 */
function mmww_make_string( $meta, $item ) {
	$r = ''; /* result accumulator */
	$options = get_option( 'mmww_options' );
	if (!array_key_exists($item, $options)) {
		return NULL;
	}
	$t = (empty( $options[$item] )) ? '' : $options[$item]; /* the template */
	/* this is a parse loop that handles text {token} text {token} style templates. */
	while ( ! empty ($t) > 0) {
		$p = strpos ($t, '{');
		if (!($p===False)) {   /* start position of next {token} */
			/* move the stuff before the token to the result string */
			$r .= substr($t,0,$p);  $t = substr($t,$p);

			$p = strpos($t,'}'); /* position of next } */	
			if (!($p === False)) {
				/* grab the token from the stream */
				$p += 1; /* include the ending } */
				$token = substr($t,0,$p);
				$t = substr($t,$p);
				/* look up the token in the metadata */
				$token = substr($token, 1, -1); /* take off first and last brace chars */
				if ( ! empty ($meta[$token])) {
					/* found it, use it. */
					$r .= $meta[$token];
				} else {
					/* special case: metadata name in template but not present in the item
					 * if the next template character is blank, skip it
					 */
					if (' ' == substr($t,0,1)) {
						$t = substr($t,1);
					}
				}
			} else { /* if there's no closing brace, use the rest of the template */
				$r .= $t;  $t = '';
			}
		} else {
			/* if there's no remaining opening brace, use the rest of the template */
			$r .= $t; $t = '';
		}
	} /* end while template is ! empty */
	return $r;
}

//TODO I don't think we  need this one. add_filter ('wp_prepare_attachment_for_js', 'mmww_wp_prepare_attachment_for_js', 20,3);

/**
 * update attachment details for display in media manager 
 * @since 3.5
 * @param array $response attachment details.
 * @param mixed $attachment Attachment ID or object.
 * @param mixed $meta array of metadata. meta['image_meta'] contains this plugin's stuff 
 * @return array Array of attachment details.
 */
function mmww_wp_prepare_attachment_for_js ($response, $attachment, $meta) {
	/* the $meta parameter isn't always present */
	if ( !empty ( $meta ) && !empty ( $meta['image_meta'] )  ) {
		$im = $meta['image_meta'];
		if ('audio' == $response['type']) {
			$response['caption'] = mmww_make_string($im,'audio_caption');
			$response['description'] = mmww_make_string($im,'audio_description');
			$response['title'] = mmww_make_string($im,'audio_title');
		}
	}
	
	return $response;
}


/**
 * get an html table made of an item's metadata
 * @param array $meta of metadata strings
 * @return string html
 */
function mmww_get_metadata_table ($meta) {
	/* filter the metadata for display according to the MIME type, extensibly */
	$meta = apply_filters( 'mmww_filter_metadata', $meta );
	$string .= '<table><tr><td>tag</td><td>value</td></tr>' . "\n";
	foreach ($meta as $tag => $value) {
		$string .= '<tr><td>' . $tag . '</td><td>' . $value .'</td></tr>' . "\n";
	}
	$string .= '</table>' . "\n";

	return $string;
}


if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
	add_filter('attachment_fields_to_edit', 'mmww_attachment_fields_to_edit',20,2);
}
/**
 *  Edit fields in the media upload editor
 * @version 3.4 and earlier
 * @param associative array of fields $file
 * @param associative array describing $post
 * @return updated array of fields
 */
function mmww_attachment_fields_to_edit($fields,$post) {
	$metadata_refreshed = false;
	$mime = get_post_mime_type($post->ID);
	$file = mmww_get_attachment_path( $post->ID );

	/* get the metadata from the media file. */
	$meta = array();
	if ($file) {
		$meta = wp_read_image_metadata( $file );
	}	
		
	/* this field is only needed when embedding an attachment */
	/* this $fields['url'] array element is not present in WP 3.5 and beyond */
	if (isset( $fields['url'] ) && isset( $fields['url']['html'] ) ) {
		if ($mime == 'audio/mpeg') {
			/* 3.4.x and before: audio file; insert a player button matching the Jetpack [audio] shortcode syntax */
			$url = wp_get_attachment_url($post->ID);
			$files = esc_attr($url);
			/* the [audio[ titles and artists lists are comma separated, so entitize embedded commas */
			//TODO use the title field unmodified
			$titles = mmww_nocommas(mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 'album')));
			//TODO drive this from a setting template
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
	
	$string = mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 
			'album','creditlead', 'credit', 'creditconductor','created_time', 'copyright'));
	$fields['post_excerpt']['value'] = $string;
	
	$string = '';
	if (mmww_getfiletype( $mime ) == 'image') {
		$fields['image_alt']['value'] = mmww_getmetastring ('; ',$meta, array('title','credit'));
	}		

	$fields['post_content']['value'] = mmww_get_metadata_table ($meta);

	return $fields;
}


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
	if ( $mime == 'audio/mpeg' ) {
		$meta = array();
		$result = '';
		$jsonmeta = get_post_meta($post->ID,MMWW_POSTMETA_KEY,true);
		if (! empty ($jsonmeta)) {
			$meta = json_decode($jsonmeta, true);
			$url = wp_get_attachment_url($post->ID);
			$files = esc_attr($url);
			/* the [audio[ titles and artists lists are comma separated in Jetpack shortcode, so scrub embedded commas */
			$titles = mmww_nocommas(mmww_getmetastring ('; ', $meta, array('grouptitle', 'title', 'album')));
			$artists = mmww_nocommas(mmww_getmetastring ('; ',$meta, array('creditlead', 'credit', 'creditconductor')));
			$result = "[audio $files|titles=$titles|artists=$artists]";
		}
		
		if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
			/* pre-3.5 function ... fix up already-created [audio] shortcode */
			if ( stripos( $html,'[audio ' )) {
				/* correct mime type AND attempt to post an audio shortcode. */
				/* filter the metadata for display according to the MIME type, extensibly */
				/* audio file; insert a player button matching the Jetpack [audio] shortcode syntax */
				$html = $result;
			}
		}
		else {
			//TODO this probably is not the right criterion
			if (! empty ($result)) {
				$html = $result . '###'. $html;
			}
		}
	}
	return $html;
}


?>