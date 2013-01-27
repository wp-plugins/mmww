=== Plugin Name ===
Contributors: olliejones
Donate link: http://joebackward.wordpress.com/2012/11/25/mmww-media-metadata-workfilow-wizard-plugin-for-wordpress-3-4/
Tags: media, audio, video, images, metadata, exif, id3, xmp, png, iptc, workflow
Requires at least: 3.0.1
Tested up to: 3.5
Stable tag: 0.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates your media metadata workflow with WordPress's Media Library. 

== To Do ==

Add support for aac and video file metadata

Ask for and receive lots of sample files from users, and use them to test.

Switch to using object syntax.

Come up with a better way to handle commas in metadata when generating audio tags.

== Description ==

If you create lots of images, graphics, audio clips, or video clips 
you probably go to some trouble to put metadata (titles, 
copyrights, track names, dates, and all that) into them.

JPEG image files have EXIF metadata. MP3 audio clips have ID3 metadata.
PNG files have their own kind of metadata. Adobe is pushing an interoperable 
standard called XMP to hold metadata as well.  Video files also have metadata.  

If you use a production tool like Acrobat, Adobe Bridge or Audacity, you probably
put this kind of metadata into your files.  And then you probably rekey it when 
you put the files into your WordPress site.  

This plugin will get you out of doing that. Now you can have that 
metadata transferred into the Media Library automatically when you 
upload your media.

You can choose to have the creation date in your media file used as the "Uploaded" date
in WordPress.  So, for example, your photos can be ordered in the media library in
order of the date they were taken.

You can specify templates defining what metadata items should be used to create
each WordPress attachment post's fields: title, caption, alt text, and descripion.

== Installation ==

Install and activate this plugin in the usual way.

For audio files, this uses the [audio] shortcode provided by Jetpack. 
If you don't have Jetpack, you can find that shortcode also
in the Shortpack plugin.

== Frequently Asked Questions ==

= Do you have video file support? =

Not yet, but it is planned.

= Your plugin didn't read my media file correctly. What do I do now? =

Please send me the file at olliejones@gmail.com. By sending it to me you give me permission to add it to my test suite, and I'll do my best to get it working.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 0.0.1 =
* Brandy new

= 0.0.2 =
* Some basic stuff is working for pdf, jpg, mp3 files.

= 0.9.1 =

Audio Player button working for versions before Wordpress 3.5

= 1.0.0 =

 1. Metadata extraction working for jpg, png, mp3, pdf files. (There's no metadata in gif files).
 1. Integration with the V3.5 media manager is complete.
 1. The Setting pages allows specification of templates for populating attachment-post fields.
 
== Upgrade Notice ==

= 1.0 =
Add support for WordPress 3.5 and above. Add PNG support.

= 0.9 =
First publicly visible version

= 0.5 =

== Credits ==

This plugin incorporates the Zend Media Framework by Sven Vollbehr and Ryan Butterfield
which they generously made available under the BSD license. It comes in handy for retrieving
and decoding the ID3 tags from audio files. 
See the LICENSE.txt file in this distribution.

Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
Thanks, Sven and Ryan!


== Metadata Standards Reference ==

List of references to metadata standards tk.


