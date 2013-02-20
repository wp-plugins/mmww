<?php

class MMWWIPTCReader {

	private $iptc;

	function __construct($file) {
		$this->iptc = false;
		// fetch additional info from iptc if available
		if ( is_callable( 'iptcparse' ) ) {
			getimagesize( $file, $info );
			if (array_key_exists( 'APP13', $info )) {
				$this->iptc = iptcparse( $info['APP13'] );
			}
		}
	}

	function __destruct() {
		$this->iptc = false;
	}


	/**
	 * fetch metadata from an APP13 segment in an iptc data item in a file.
	 * @return array of metadata strings, or an empty array if no app13 data was found.
	 */
	public function get_metadata () {
		$meta = array();
		// read iptc, since it might contain data not available in exif such
		// as caption, description etc

		if (!(False === $this->iptc)) {
			$iptc = $this->iptc;
			// headline, "A brief synopsis of the caption."
			if ( ! empty( $iptc['2#105'][0] ) )
				$meta['title'] = utf8_encode( trim( $iptc['2#105'][0] ) );
			// title, "Many use the Title field to store the filename of the image, though the field may be used in many ways."
			elseif ( ! empty( $iptc['2#005'][0] ) )
			$meta['title'] = utf8_encode( trim( $iptc['2#005'][0] ) );

			if ( ! empty( $iptc['2#120'][0] ) ) { // description / legacy caption
				$caption = utf8_encode( trim( $iptc['2#120'][0] ) );
				if ( empty( $meta['title'] ) ) {
					// Assume the title is stored in 2:120 if it's short.
					if ( strlen( $caption ) < 80 )
						$meta['title'] = $caption;
					else
						$meta['description'] = $caption;
				} elseif ( $caption != $meta['title'] ) {
					$meta['description'] = $caption;
				}
			}

			if ( ! empty( $iptc['2#110'][0] ) ) // credit
				$meta['credit'] = utf8_encode(trim($iptc['2#110'][0]));
			elseif ( ! empty( $iptc['2#080'][0] ) ) // creator / legacy byline
			$meta['credit'] = utf8_encode(trim($iptc['2#080'][0]));

			if ( (! empty( $iptc['2#055'][0]) ) and (! empty( $iptc['2#060'][0] )) ) { // created date and time

				/* do the timezone stuff right; png creation time is in local time */
				$previous = date_default_timezone_get();
				@date_default_timezone_set(get_option('timezone_string'));
				$meta['created_timestamp'] = strtotime( $iptc['2#055'][0] . ' ' . $iptc['2#060'][0] );
				@date_default_timezone_set($previous);
			}
			if ( ! empty( $iptc['2#116'][0] ) ) // copyright
				$meta['copyright'] = utf8_encode( trim( $iptc['2#116'][0] ) );
		}
		return $meta;
	}
}
?>