<?php


class MMWWXMPReader {

	private $xmp;

	function __construct($file) {
		$this->xmp = $this->get_xmp($file);
	}
	
	function __destruct() {
		unset ($this->xmp);
	}
	

	private $xmp_metadata_list = array (
		'rating' => '//xmp:Rating',
		'rating' => '//@xmp:Rating',
		'workflowlabel' => '//@xmp:Label',
		'creditorganization' => '//@photoshop:AuthorsPosition',
		'descriptionwriter' => '//@photoshop:CaptionWriter',
		'copyrighted' => '//@xmpRights:Marked',
		'copyrightwebstatement' => '//@xmpRights:WebStatement',
		'credit'=> '//dc:creator/rdf:Seq/rdf:li',
		'tags' => '//dc:subject/rdf:Bag/rdf:li',
		'copyright' => '//dc:rights/rdf:Alt/rdf:li',
		'title' => '//dc:title/rdf:Alt/rdf:li',
		'description'=> '//dc:description/rdf:Alt/rdf:li',
		'format' => '//@dc:format'
	);

	private $audio_metadata_list = array (
		'workflowlabel' => '//@xmp:Label',
		'rating' => '//@xmp:Rating',
		'rating' => '//xmp:Rating',
		'copyrighted' => '//@xmpRights:Marked',
		'copyrightwebstatement' => '//@xmpRights:WebStatement',
		'credit'=> '//@xmpDM:artist',
		'tags' => '//dc:subject/rdf:Bag/rdf:li',
		'copyright' => '//dc:rights/rdf:Alt/rdf:li',
		'title' => '//dc:title/rdf:Alt/rdf:li',
		'album' => '//@xmpDM:album',
		'engineer' => '//@xmpDM:engineer',
		'releasedate' => '//@xmpDM:releaseDate',
		'year' => '//@xmpDM:year',
		'description'=> '//dc:description/rdf:Alt/rdf:li',
		'format' => '//@dc:format'
	);

	/**
	 * retrieve XMP from a media file
	 * @param string $file file path name
	 * @return SimpleXMLElement or false if no XMP was found
	 */
	private function get_xmp ($file) {
		/* find a xmp metadata stanza in the file */
		$ts = '<x:xmpmeta';
		$xmp = false;
		$chunksize = 64*1024;
		$maxsize = $chunksize;
		$content = '';
		$s = False;
		$size = filesize($file);
		$start = 0;
		while ($start < $size) {
			/* read twice the chunksize */
			$content = file_get_contents ($file, false, NULL, $start, $chunksize+$chunksize);
			$s = strpos($content, $ts);
			if ($s === False) {
				/* move ahead by the chunksize */
				$start += $chunksize;
			} else  {
				/* found the start, stop reading */
				$start += $s;
				break;
			}
		}
		if ($start < $size ) {
			/* read the maxsize from the start point of the stanza */
			$content = file_get_contents ($file, false, NULL, $start, $maxsize);
			$s = strpos($content, $ts);
		}
		if (! ($s === False)) {

			/* find the end */
			$te = '</x:xmpmeta>';
			$e = strpos($content, $te, $s+strlen($ts));
			if (! ($e === False)) {
				$e += strlen($te);
				/* found the stanza, use it */
				$xmp = simplexml_load_string("<?xml version='1.0'?>\n" . substr($content, $s, $e - $s));

				/* deal with the plethora of namespaces in XMP */
				$ns = $xmp->getNamespaces(true);
				foreach ($ns as $key => $val) {
					$xmp->registerXPathNamespace($key, $val);
				}
				unset($ns);
			}
		}
		unset ($content);
		return $xmp;
	}

	/**
	 * get a metadata array from an xmp stanza based on a list of itesm
	 * @param string $file name containing xmp stanza
	 * @param metadata list $list
	 * @return multitype:string
	 */
	private function get_list($xmp, $list) {
		$result = array();
		if (is_object($xmp)) {
			foreach ($list as $tag => $xpath) {
				/* use @ here to avoid error messages when XML namespaces are unexpected */
				$it = @$xmp->xpath($xpath);
				if (!($it === False)) {
					$gather = array();
					foreach($it as $s) {
						$gather[] = $s;
					}
					if (!empty($gather)) {
						$out = implode(';',$gather);
						if (is_string($out) && strlen($out) > 0) {
							$result[$tag] = $out;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * fetch items of image metadata from the xmp in an image file
	 * @return string array of metadata, possibly empty if no metadata found.
	 */
	public function get_metadata () {
		return $this->get_list($this->xmp, $this->xmp_metadata_list);
	}
	/**
	 * fetch items of audio metadata from the xmp in an audio file
	 * @return string array of metadata, possibly empty if no metadata found.
	 */
	public function get_audio_metadata () {
		return $this->get_list($this->xmp, $this->audio_metadata_list);
	}

}