<?php

require_once ( 'Zend/Media/Id3v2.php' );
require_once ( 'Zend/Media/Id3/TextFrame.php' );

global $mmww_id3_metadata_list;
$mmww_id3_metadata_list = array(
	'TALB' => 'album',
	'TBPM' => 'tempo',
	'TCON' => 'genre',
	'TIT1' => 'grouptitle',
	'TIT2' => 'title',
	'TIT3' => 'caption',
	'TKEY' => 'keysignature',
	'TYER' => 'year',
	'TDAT' => 'DDMM',
	'TIME' => 'HHMM',
	'TLEN' => 'duration',
	'TPE1' => 'credit',
	'TPE2' => 'creditlead',
	'TPE3' => 'creditconductor',
	'TPE4' => 'creditproducer',
	'TEXT' => 'writer',
	'TENC' => 'creditorganization',
	'TMED' => 'mediatype',
	'TOPE' => 'creditoriginal',
	'TCOP' => 'copyright' );


/**
 * turn a utf 16 string into utf 8
 * (some id3v2 tags, esp. from Adobe tools, are coded in utf 16)
 * Many thanks to Andrew Walker.
 * @link http://www.craiglotter.co.za/2010/03/05/php-convert-a-utf-16-string-to-a-utf-8-string/
 * @param input string $str
 * @return string result
 */
function mmww_utf16_to_utf8($str) {
	$c0 = ord($str[0]);
	$c1 = ord($str[1]);

	if ($c0 == 0xFE && $c1 == 0xFF) {
		$be = true;
	} else if ($c0 == 0xFF && $c1 == 0xFE) {
		$be = false;
	} else {
		return $str;
	}

	$str = substr($str, 2);
	$len = strlen($str);
	$dec = '';
	for ($i = 0; $i < $len; $i += 2) {
		$c = ($be) ? ord($str[$i]) << 8 | ord($str[$i + 1]) :
		ord($str[$i + 1]) << 8 | ord($str[$i]);
		if ($c >= 0x0001 && $c <= 0x007F) {
			$dec .= chr($c);
		} else if ($c > 0x07FF) {
			$dec .= chr(0xE0 | (($c >> 12) & 0x0F));
			$dec .= chr(0x80 | (($c >>  6) & 0x3F));
			$dec .= chr(0x80 | (($c >>  0) & 0x3F));
		} else {
			$dec .= chr(0xC0 | (($c >>  6) & 0x1F));
			$dec .= chr(0x80 | (($c >>  0) & 0x3F));
		}
	}
	return $dec;
}

function mmww_get_id3_metadata ($file) {
	global $mmww_id3_metadata_list;
	$meta = array();
	try {
		$id3 = new Zend_Media_Id3v2($file);
		$found = false;
		foreach ($id3->frames as $frames) {
			foreach ($frames as $frame) {
				if ($frame instanceof Zend_Media_Id3_TextFrame) {
					$tag = $frame->identifier;
					if (! empty ($mmww_id3_metadata_list[$tag])) {
						$val = $frame->text;
						$val = mmww_utf16_to_utf8 ($val);
						if ((!empty ($val)) && strlen($val) > 0 ) {
							$found = true;
							$meta[$mmww_id3_metadata_list[$tag]] = $val;
						}
					}
				}
			}
		}
	}
	catch (Zend_Media_Id3_Exception $e) {
		$meta['error'] = $e->getMessage() . " ($file)";
	}
	if ($found) {
		$meta['format'] = 'audio/mpeg';
	}
	return $meta;
}

?>