<?php

class MMWWMedia {

	/** a cache of metadata keyed on the name of the file */
	private $meta_cache_by_filename = array();
	
	function __construct() {
		/* set up the various filters etc. */

		/* metadata display filters; internal to mmww */
		add_filter( 'mmww_filter_metadata', array ($this, 'add_tidy_metadata') ,10, 1);
		add_filter( 'mmww_filter_metadata', array ($this, 'remove_meaningless_metadata') ,11, 1);
		/* attachment metadata specific */
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'refetch_metadata' ), 10, 2 );
		add_filter( 'wp_update_attachment_metadata', array( $this, 'update_metadata' ),10,2 );
		/* image metadata readers */
		add_filter( 'wp_read_image_metadata', array( $this, 'read_media_metadata' ),11,3 );
		add_filter( 'wp_read_image_metadata', array ($this, 'apply_template_metadata' ),90,3 );

	}
	
	/**
	 * hook function for filter internal to mmww 
	 * @param array $meta of metadata key/val strings
	 * @return array of metadata
	 */
	function add_tidy_metadata ($meta) {	
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
		return $meta;
	}
	
	/**
	 * hook function for filter internal to mmww 
	 * @param array $meta of metadata key/val strings
	 * @return array of metadata
	 */
	function remove_meaningless_metadata ($meta) {
			
		/* eliminate redundant items from the metadata */
		$tozap = array('aperture','shutter_speed', 'warning');
		foreach ($tozap as $zap) {
			unset ($meta[$zap]);
		}
	
		/* eliminate zero or empty items except title and caption */
		$keep = array ('title' => 'yes', 'caption' => 'yes');
		foreach ($meta as $key => $val) {
			if (!array_key_exists($key,$keep)) {
				if ( is_string($val) && strlen($val) == 0  ) {
					unset ($meta[$key]);
				}
				if ( is_numeric($val) && $val == 0 ) {
					unset ($meta[$key]);
				}
			}
		}
		return $meta;
	}

	/**
	 * turn audio/mpeg into audio, image/tiff into image, etc
	 * @param string $f MIME type
	 * @return string basic data type
	 */
	private function getfiletype ($f) {
		$ff = explode( '/', $f );
		$filetype = $ff[0];
		$filetype = strtolower( $filetype );
		return $filetype;	
	}

	/**
	 * refetch the attachment metadata for non-image file types (audio, PDF, etc)
	 * @param array $meta of metadata items including $meta['image_meta']
	 * @param int $id attachment id to process
	 * @return usable attachment metadata
	  */
	function refetch_metadata ($metadata, $id) {
		$image_meta = array();
		/*
		 * Note: sometimes WP asks us to reread the metadata.
		 * $this->$meta_cache_by_filename gets us out of doing that,
		 * to save file-slurping cpu and io time
		 */
		if (!array_key_exists('image_meta', $metadata)) {
			/* no image_meta, we need to get it. */
			$file = $this->get_attachment_path ($id);
			if (array_key_exists($file, $this->meta_cache_by_filename)) {
				$image_meta = $this->meta_cache_by_filename[$file];
			}  else {
				$image_meta = wp_read_image_metadata( $file );
			}
			
			if ( $image_meta ) {
				$metadata['image_meta'] = $image_meta;
			}		
		}
		return $metadata;
	}

	/**
	 * Filter to handle extra stuff in attachment metadata update
	 * @param array $data attachment data array. Can be an empty array
	 * @param int $id attachment id 
	 * @return data, modified as needed
	 */
	function update_metadata ($data, $id) {
	
		if ( !empty ( $data ) && array_key_exists('image_meta', $data) ) {
			$meta = $data['image_meta'];
			$updates = array();
		
			/* handle the caption for photos, which goes into wp_posts.post_excerpt. */
			if (!empty($meta['displaycaption'])) {
				$updates['post_excerpt'] = $meta['displaycaption'];
			}
			
			/* update the attachment post_date and post_date_gmt if that's what the admin wants and the metadata has it */
			$options = get_option( 'mmww_options' );
			$choice = (empty( $options['use_creation_date'] )) ? 'no' : $options['use_creation_date'];
			if ($choice == 'yes' && !empty($meta['created_timestamp'])) {
				/* a note on timezones: WP mostly keeps the PHP default timezone set to
				 * UTC and computes an offset between UTC and local time when it's needed.
				 * That works fine for time = now.  But for incoming timestamps
				 * (embedded in media), it's necessary to set the PHP timezone to the
				 * user's timezone.  This is because the current timezone offset may not be
				 * the same as the offset at the time in the incoming timestamp.
				 * That is, for example, the incoming timestamp may have been when daylight
				 * savings time was in force, but that may not be true at time=now.
				 * Hence this monkey business with saving and restoring the
				 * default timezone.
				 */
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
				clean_post_cache ($id);
			}
		
			/* handle the image alt text (screenreader etc) which goes into a postmeta row */
			if (!empty($meta['alt'])) {
				update_post_meta ($id, '_wp_attachment_image_alt', $meta['alt']);
			} 
			
			/* stash tne metadata itself so we don't have to reread it from the file for site visitors */	
			// need this? update_post_meta ($id, MMWW_POSTMETA_KEY, json_encode($meta));
		}
		
		return $data;
	}

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
	function read_media_metadata ($meta, $file, $sourceImageType) {
	
		if ( ! file_exists( $file ) ) {
			return $meta;
		}
		
		/* if the metadata is cached, return it right away */
		if (array_key_exists($file, $this->meta_cache_by_filename)) {
			return $this->meta_cache_by_filename[$file];
		}
		//TODO hang on to the file name to use as a title if nothing else works out.
		$meta_accum = array();
	
		$ft = wp_check_filetype( $file );
		$filetype = $ft['type'];
		$filetype = $this->getfiletype($filetype);
	
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
	function apply_template_metadata ($meta, $file, $sourceImageType) {
	
		if ( empty ($meta) && empty ($meta['mmww_type'])) {
			/* if there's no mmww metadata detected, don't do anything more */
			return $meta;
		}
	
		/* if the metadata is cached, return it right away */
		if (array_key_exists($file, $this->meta_cache_by_filename)) {
			return $this->meta_cache_by_filename[$file];
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
			$gen = $this->make_string ($cleanmeta,$codetype);
			if(!empty($gen)) {
				$newmeta[$code] = $gen;
			}
		}
		
		$meta = array_merge($cleanmeta, $newmeta);
		/* cache the resulting metadata */
		$this->meta_cache_by_filename[$file] = $meta;
		return $meta;
	}

	/**
	 * Retrieve the pathname in the file system for an attachment's file.
	 *   (cribbed from post.php)
	 * @since 2.1.0
	 *
	 * @param int $post_id Attachment ID.
	 * @return string
	 */
	private function get_attachment_path( $post_id = 0 ) {
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
	private function make_string( $meta, $item ) {
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

	/**
	 * get an html table made of an item's metadata
	 * @param array $meta of metadata strings
	 * @return string html
	 */
	private function get_metadata_table ($meta) {
		/* filter the metadata for display according to the MIME type, extensibly */
		$meta = apply_filters( 'mmww_filter_metadata', $meta );
		$string .= '<table><tr><td>tag</td><td>value</td></tr>' . "\n";
		foreach ($meta as $tag => $value) {
			$string .= '<tr><td>' . $tag . '</td><td>' . $value .'</td></tr>' . "\n";
		}
		$string .= '</table>' . "\n";
	
		return $string;
	}
} 

new MMWWMedia();
