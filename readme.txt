=== MMWW ===
Contributors: olliejones
Tags: media, audio, video, images, metadata, exif, id3, xmp, png, iptc, workflow, caption, alt
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 0.9.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your media metadata workflow with WordPress's Media Library 

== To Do ==

Figure out how to put keywords into a taxonomy if that's what the user wants.

Get the Microsoft Windows Explorer-set Rating item to work properly.

Add support for the Yet Another Photo Blog (YAPB) [plugin]{http://wordpress.org/extend/plugins/yet-another-photoblog/}

Improve the syntax of the templates.

Add support for aac and video file metadata.

Ask for and receive lots of sample files from users, and use them to test.

Come up with a better way to handle commas in metadata when generating audio shortcodes.

Figure out a taxonomy to handle the media ratings in XMP.

Support TIFF files. (Please let the author know if you need TIFF support.)

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
order of the date and time they were taken.

You can specify templates defining what metadata items should be used to create
each WordPress attachment post's fields: title, caption, alt text, and description.

For audio files, MMWW can automatically create the [audio] shortcode provided by [Jetpack[(http://wordpress.org/extend/plugins/jetpack/). 
If you don't have Jetpack, you can find that shortcode also in the [Shortpack](http://wordpress.org/extend/plugins/shortpack/) plugin.
In WordPress 3.4.2 and earlier versions, there's an "Audio Player" button to do this on the media popup. 
In later versions, choose Link To Media File and the shortcode will be generated for you. (The Settings page
lets you turn this behavior off.)

== Installation ==

Install and activate this plugin in the usual way.

== Metadata templates ==

Once the plugin is installed and activated, it will populate the text fields in your site's attachment
posts using metadata from the media files you upload. 

= Text fields for attachments =

The attachment text files are Title, Description, Caption and Alternate Text.  The Title of the attachment is also used to create the slug.
The Description is free text describing the media item. 

The Caption is displayed underneath photos in posts. 

The Alternate Text is embedded in the IMG tag in the post. It serves two purposes: describing the image in textual form
for people who use screen readers because they cannot see the images, and for describing the image to search engines.

= Using metadata templates =

Many media editor programs, such as Photoshop, Paint Shop Pro, Acrobat and Audacity have ways of loading metadata into media.  These usually can
be found in a dialog box named "Properties," "Image Information," or something similar.

The settings page lets you specify the templates to use for populating the text fields. For example, you can set the 
Description template for an image file from a smartphone to 

     {description} {shutter}--{fstop} {latitude}/{longitude} {created_time}
     
and you'll see some details about how, where, and when the photo was taken in your Description.

= JPEG image files =

JPEG photo files have lots of possible metadata. 
Not every photograph has all these items of metadata, but most have some of them.

     {title}               Title of the file.
     {credit}              Author.
     {copyright}           Copyright notice if any is included.
     {description}         Narrative description.
     {tags}                One or more tags, separated by semicolons.
     {rating}              0 - 5, set by various image browsers
     {workflowlabel}       A text string like "Discard" or "Keep," set by various image browsers
     {camera}              Camera model
     {shutter}             Shutter speed, such as 1/60
     {fstop}               Aperture, like f/5.6
     {flash}               The flash setting, such as "No Flash" or "Fired, Red-eye reduction" 
     {lightsource}         The kind of light detected, such as "Daylight" or "Tungsten"
     {meteringmode}        The type of metering the camera used, such as "Spot," "Average," or "Unknown"
     {sensingmethod}       The type of image sensor, such as "One-chip color area sensor."
     {exposuremode}        The exposure mode, such as "Auto" or "Manual"
     {exposureprogram}     The exposure-setting program, such as "Aperture Priority" or "Normal Program."
     {brightness}          A number indicating how bright the scene is
     {latitude}            The GPS latitude reading, shown in degrees and decimals.
     {longitude}           The GPS longitude reading, showin in degrees and decimals.
     {created_time}        The timestamp describing the time the photograph was taken.

= PNG image files =

PNG image files have a few items of metadata, if the author bothered to set them. 

     {title}               Title of the file.
     {credit}              Author.
     {copyright}           Copyright notice if any is included.
     {description}         Narrative description.
     {created_time}        The timestamp describing the time the PNG was made.

= PDF =

PDF files, created by Adobe Acrobat and other programs, have a few items of metadata.  The most generally useful of
these are the title and credit.

     {title}               Title of the file.
     {credit}              Author.
     {copyright}           Copyright notice if any is included.
     {description}         Narrative description.
     {tags}                One or more tags, separated by semicolons.
     {rating}              0 - 5 
     {created_time}        The timestamp describing the time the PNG was made.

= Audio =

MP3 Audio files can have lots of metadata, defined by the ID3 standard.  The first few items are by far more common than the others.

     {title}               Title of the song.
     {album}               Title of the album.
     {credit}              Author or performer.
     {year}                Year of recording
     {copyright}           Copyright notice if any is included.
     {description}         Narrative description.
     {rating}              0 - 5 

These metadata items are in the ID3 standard, but most files don't have them.  MMWW handles them
in case your particular media workflow needs them.

	 {tempo}
	 {genre}
	 {grouptitle}
	 {keysignature}
	 {DDMM}              Day and month of recording
	 {HHMM}              Hour and minute of recording
	 {duration}
	 {creditlead}
	 {creditconductor}
     {creditproducer}
     {writer}
     {creditorganization}
     {mediatype}
     {creditoriginal}
     {copyright}

= Metadata Standards Reference =

[Adobe XMP](http://www.adobe.com/products/xmp/)

[ID3 for MP3 files](http://id3.org/)

[EXIF for JPEG files](http://www.exif.org/)

[IPTC Photo Metadata]{http://www.iptc.org/site/Photo_Metadata/)

== Frequently Asked Questions ==

= Do you have video file support? =

Not yet, but it is planned. Please let the author know if you're interested.

= Your plugin didn't read my media file correctly. What do I do now? =

Please send me the file at olliejones@gmail.com. By sending it to me you give me permission to add it to my test suite, and I'll do my best to get it working.

= If I upload a TIFF, my Insert Media dialog box stops working correctly.  Why?

That's true. It's a problem with WordPress, not with MMWW: WordPress doesn't handle TIFFs correctly.  
To fix your Insert Media dialog box, visit the Media Library from your dashboard, and delete all your TIFF attachments.

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
* Some basic stuff is working for pdf, jpg, mp3 files.  The Audio Player button works for WordPress 3.4.2 and earlier.

= 0.9.1 =

Audio Player button working for versions before Wordpress 3.5

= 1.0.0 =

 1. Metadata extraction working for jpg, png, mp3, pdf files. (There's no metadata in gif files, and tiff files aren't supported by WordPress).
 1. Integration with the V3.5 media manager is complete.
 1. Automatic [audio] shortcode insertion working both pre- and post- WordPress 3.5.
 1. The Settings page allows specification of templates for populating attachment-post fields.
 
== Upgrade Notice ==

= 1.0 =
Add support for the new Media Library popups in WordPress 3.5 and above. Add PNG support.

If you're putting audio files into posts and pages and you upgrade to WordPress 3.5 or later, you'll find that the Audio Player button has vanished along 
with the other Link URL buttons. This version of MMWW still allows you to insert a shortcode, 
by choosing Link To Media File on the attachment settings page. 

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


