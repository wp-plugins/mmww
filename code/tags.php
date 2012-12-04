<?php

/** this function is never run.  It's to help xgettext create translation
 * files for the metadata tags we're handling. 
 * 
xgettext --help
cd C:\Users\Ollie\wordpress-workspace\i18n
dir
php makepot.php wp-plugin C:\web\htdocs\wp-content\plugins\mmww
dir
more mmww.pot
dir
 * 
 */

function mmww_tags() {
	/* translators: audio album or collection title tag name */
	_x('ATALB', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio album or collection title tag example */
	_x('ATALB', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio beats per minute tag name */
	_x('ATBPM', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio beats per minute tag example */
	_x('ATBPM', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio numerical genre or content type tag name */
	_x('ATCON', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio numerical genre or content type tag example */
	_x('ATCON', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio content group (often omitted) tag name */
	_x('ATIT1', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio content group tag example */
	_x('ATIT1', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio track or item title tag name */
	_x('ATIT2', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio track or item title tag example */
	_x('ATIT2', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio subtitle tag name */
	_x('ATIT3', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio subtitle (e.g. K.56, Opus 17) tag example */
	_x('ATIT3', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio key tag name */
	_x('ATKEY', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio key (e.g. Ebm for e-flat minor) tag example */
	_x('ATKEY', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio year of recording tag name */
	_x('ATYER', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio year of recording tag example */
	_x('ATYER', 'example', MMWW_PLUGIN_NAME);
	
	/* translators: audio DDMM day/month of recording tag name */
	_x('ATDAT', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio DDMM day/month of recording tag example */
	_x('ATDAT', 'example', MMWW_PLUGIN_NAME);
	
	/* translators: audio year of recording tag name */
	_x('ATDRC', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio year of recording tag example */
	_x('ATDRC', 'example', MMWW_PLUGIN_NAME);
	
	/* translators: audio artist or author tag name */
	_x('ATPE1', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio artist or author tag example */
	_x('ATPE1', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio encoder organization tag name */
	_x('ATENC', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio encoder organization tag example */
	_x('ATENC', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio writer or lyricist tag name */
	_x('ATEXT', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio writer or lyricist tag example */
	_x('ATEXT', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio time HHMM tag name */
	_x('ATIME', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio time HHMM tag example */
	_x('ATIME', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio duration in milliseconds tag name */
	_x('ATLEN', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio duration in milliseconds example */
	_x('ATLEN', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio media type tag name */
	_x('ATMED', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio media type example ex CD, DVD */
	_x('ATMED', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio lead artist tag name */
	_x('ATPE1', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio lead artist tag example */
	_x('ATPE1', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio artist tag name */
	_x('ATPE2', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio artist tag example */
	_x('ATPE2', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio conductor tag name */
	_x('ATPE3', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio conductor tag example */
	_x('ATPE3', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio engineer tag name */
	_x('ATPE4', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio engineer tag example */
	_x('ATPE4', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio original artist tag name */
	_x('ATOPE', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio original artist tag example */
	_x('ATOPE', 'example', MMWW_PLUGIN_NAME);

	/* translators: audio copyright tag name */
	_x('ATCOP', 'tag', MMWW_PLUGIN_NAME);
	/* translators: audio copyright tag example */
	_x('ATCOP', 'example', MMWW_PLUGIN_NAME);

}

?>