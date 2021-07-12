#!/usr/bin/php
<?php
/*
 * timelapse_allsky.php
 * Generate a nightly timelapse video and delete old images
 *
 * Copyright (c) 2013-2020 - Dario Pilori <dario.pilori@astrogeo.va.it>
 * SPDX-License-Identifier: MIT
*/
// Directory with images
define("DIR", "/media/allsky");
// FTP settings
// define("FTPHOST", "ftp.example.com");
// define("FTPUSER", "user");
// define("FTPPASS", "passwords");
require_once('ftp_settings.php');

// Set timezone
date_default_timezone_set('Europe/Rome');

// Unix timestamp
$oggi = time();
$ieri = $oggi-(24*60*60);

// Yesterday's sunset
$inizio = date_sunset($ieri, SUNFUNCS_RET_TIMESTAMP, 45.86778, 8.77096);

// Today's sunrise
$fine = date_sunrise($oggi, SUNFUNCS_RET_TIMESTAMP, 45.86778, 8.77096);

// Get the date in standarf format
$tramonto = date('H:i',$inizio);
$alba = date('H:i',$fine);

// Generate a temporary text file with image names for mencoder
exec("ls ".DIR, $files);
$tlname = tempnam("/tmp", "timelapse");
$tl = fopen($tlname, "w");
if(!$tl) {
	die("Unable to save timelapse text file\n");
}

// Temporary directory with watermarked images
$tmpdir = tempnam("/tmp", "wmark");
unlink($tmpdir);
mkdir($tmpdir);

foreach ($files as $image) {
	// Consider only AllSky images
	if(!preg_match("/^AllSkyImage(\d)+\.JPG$/",$image)) {
		continue;
	}

	// Use `last edit' information from images to get the date/time of each image
	$lastedit = filemtime(DIR."/$image");
	// Only hour is relevant
	$oraedit = date('H:i',$lastedit);

	// Add watermark only to relevant images
        if( $lastedit > $inizio and $lastedit < $fine ) {
            exec("/usr/bin/composite -dissolve 50 -tile logo.png ".DIR."/$image $tmpdir/$image");
		fwrite($tl, "$tmpdir/$image\n");
	}

        // Delete files older than a week (+24h) and images during the day
	if( (time() - $lastedit) > (8*24*60*60) or (strtotime($oraedit) > (strtotime($alba)+(1*60*60)) and strtotime($oraedit) < (strtotime($tramonto)-(1*60*60))) ) {
        unlink(DIR."/$image");
	}	
}
fclose($tl);

// Generate movie with mencoder
$da = date('Ymd');
$cmd = "/usr/bin/mencoder -really-quiet -nosound -noskip -oac copy -ovc x264 -x264encopts preset=slow:bitrate=1200 -o ".DIR."/timelapse_videos/allsky_$da.avi -mf fps=10 'mf://@$tlname'";
$output = exec($cmd, $output, $retval);

if($retval) {
	fwrite(STDERR, "ERROR: Unable to create movie with mencoder!\n\n$output");
} else {
	unlink($tlname);
}

// Second step: compression in H.264 to be uploaded on the website
$tlupname = 'timelapsetest.mp4';
$cmd = "/usr/bin/ffmpeg -nostats -loglevel 0 -i ".DIR."/timelapse_videos/allsky_$da.avi -codec:v libx264 -profile:v high -preset slow -b:v 900k -maxrate 900k -bufsize 1800k -vf scale=640:480 -threads 2 -an $tlupname";
$output = exec($cmd, $output, $retval);

if($retval) {
	fwrite(STDERR, "ERRORE: Error compressing movie with ffmpeg!\n\n$output");
}

// FTP upload
$ftpconn = ftp_ssl_connect(FTPHOST) or die("Unable to connect to FTP server\n");
ftp_login($ftpconn, FTPUSER, FTPPASS) or die("Login error on FTP server\n");
ftp_pasv($ftpconn, true);
ftp_put($ftpconn, "allsky.mp4",$tlupname, FTP_BINARY) or die("Unable to upload file via FTP\n");
ftp_close($ftpconn);

// Remove temporary files
unlink($tlupname);
system("/bin/rm $tmpdir/*");
rmdir($tmpdir);
?>
