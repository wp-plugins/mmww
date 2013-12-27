<?php

class MMWWIPTCReader {

	private $iptc;

    /* table of iptc data items and indexes */

    /* the following data items show up in xmp, so see xmp.php
      {iptc:creator:address}      The creator's address.
      {iptc:creator:city}         The creator's city.
      {iptc:creator:state}        The creator's state or province.
      {iptc:creator:postcode}   The creator's post / zip code.
      {iptc:creator:country}      The creator's country.
      {iptc:creator:phone}        The creator's phone(s).
      {iptc:creator:email}        The creator's email(s).
      {iptc:creator:website}      The creator's web site(s).
      {iptc:iptcsubjectcode}      IPTC subject code.
      {iptc:genre}                Intellectual genre.
      {iptc:scenecode}            IPTC scene code.
      {iptc:copyrightstatus}      Copyright Status.
      {iptc:rightsusageterms}     Terms of usage.
        */


    /* 2#055 date, 2#056 hhmmss time with +xxxx */
    /* {iptc:datecreated}          Creation date. */

    private $taglist = array(
        array('2#080', 'iptc:creator'),          /* The creator's name. */
        array('2#085', 'iptc:creator:jobtitle'), /* The creator's job title. */
        array('2#105', 'iptc:headline'),         /* Headline. */
        array('2#120', 'iptc:description'),      /* Description. */
        array('2#112', 'iptc:descriptionwriter'),/* Author of the description. */
        array('2#025', 'iptc:keywords'),         /* Keywords, separated with comma or semicolon. */
        array('2#092', 'iptc:sublocation'),      /* Location within city. */
        array('2#090', 'iptc:city'),             /* City. */
        array('2#095', 'iptc:state'),            /* State/Province. */
        array('2#101', 'iptc:country'),          /* Country. */
        array('2#100', 'iptc:iscocountrycode'),  /* Country code per ISO 3166. */
        array('2#005', 'iptc:title'),            /* Title. */
        array('2#103', 'iptc:jobidentifier'),    /* Job Identifier. */
        array('2#040', 'iptc:instructions'),     /* Instructions. */
        array('2#110', 'iptc:creditline'),       /* Credit line. */
        array('2#115', 'iptc:source'),           /* Source. */
        array('2#116', 'iptc:copyright'),        /* Copyright Notice. */
    );


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
     * Returns true if $string is valid UTF-8 and false otherwise.
     *
     * @since        1.14
     * @param [mixed] $string     string to be tested
     * @subpackage
     */
    private function is_utf8($string) {

        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $string);

    }

    private function make_utf8( $string ) {
        if ( $this->is_utf8 ( $string ) ){
            return $string;
        }
        else {
            return utf8_encode ( $string );
        }
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

            /* do the list of IPTC items */
            foreach ($this->taglist as $pair) {
                $tag = $pair[1];
                $index = $pair[0];
                if ( ! empty( $iptc[$index][0] ) ) {
                    $tempString = $iptc[$index][0];
                    $meta[$tag] = $this->make_utf8($tempString);
                }
            }

            /* do the specific items */
			// headline, "A brief synopsis of the caption."
			if ( ! empty( $iptc['2#105'][0] ) ) {
                $tempString = trim( $iptc['2#105'][0] );
				$meta['title'] = $this->make_utf8($tempString );
            }
			// title, "Many use the Title field to store the filename of the image, though the field may be used in many ways."
			elseif ( ! empty( $iptc['2#005'][0] ) ) {
                $tempString = trim( $iptc['2#005'][0] );
			    $meta['title'] = $this->make_utf8( $tempString );
            }
			if ( ! empty( $iptc['2#120'][0] ) ) { // description / legacy caption
                $tempString = trim( $iptc['2#120'][0] );
				$caption = $this->make_utf8( $tempString );
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
				$meta['credit'] = $this->make_utf8(trim($iptc['2#110'][0]));
			elseif ( ! empty( $iptc['2#080'][0] ) ) // creator / legacy byline
			$meta['credit'] = $this->make_utf8(trim($iptc['2#080'][0]));

			if ( (! empty( $iptc['2#055'][0]) ) and (! empty( $iptc['2#060'][0] )) ) { // created date and time

				/* do the timezone stuff right; creation time is in local time */
				$previous = date_default_timezone_get();
				@date_default_timezone_set(get_option('timezone_string'));
				$meta['created_timestamp'] = strtotime( $iptc['2#055'][0] . ' ' . $iptc['2#060'][0] );
				@date_default_timezone_set($previous);
			}
			if ( ! empty( $iptc['2#116'][0] ) ) // copyright
				$meta['copyright'] = $this->make_utf8( trim( $iptc['2#116'][0] ) );
		}
		return $meta;
	}
}
