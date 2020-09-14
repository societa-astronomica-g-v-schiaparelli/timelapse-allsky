#!/usr/bin/php
<?php
/*
 * timelapse_allsky.php
 * Genera il filmato in timelapse della notte e cancella le immagini vecchie
 * Nel filmato e` aggiunto un watermark.
 *
 * 2013-2020 - Dario Pilori <dario.pilori@astrogeo.va.it>
 * SPDX-License-Identifier: MIT
*/
// Directory contenente le immagini
define("DIR", "/media/allsky");
// Impostazioni FTP
// define("FTPHOST", "ftp.example.com");
// define("FTPUSER", "user");
// define("FTPPASS", "passwords");
require_once('ftp_settings.php');

// Imposta timezone
date_default_timezone_set('Europe/Rome');

// Timestamp UNIX (secondi)
$oggi = time();
$ieri = $oggi-(24*60*60);

// Tramonto di IERI
$inizio = date_sunset($ieri, SUNFUNCS_RET_TIMESTAMP, 45.86778, 8.77096);

// Alba di OGGI
$fine = date_sunrise($oggi, SUNFUNCS_RET_TIMESTAMP, 45.86778, 8.77096);

// Scrivo ore alba e tramonto in formato standard
$tramonto = date('H:i',$inizio);
$alba = date('H:i',$fine);

// File di testo temporaneo dove mettere l'elenco dei file per il filmato in timelapse da dare in pasto a mencoder
exec("ls ".DIR, $files);
$tlname = tempnam("/tmp", "timelapse");
$tl = fopen($tlname, "w");
if(!$tl) {
	die("Impossibile salvare il file del timelapse\n");
}

// Crea directory temporanea dove mettere le immagini con watermark
$tmpdir = tempnam("/tmp", "wmark");
unlink($tmpdir);
mkdir($tmpdir);

foreach ($files as $image) {
	// Considero solo le immagini di archivio della AllSky
	if(!preg_match("/^AllSkyImage(\d)+\.JPG$/",$image)) {
		continue;
	}

	// Per prendere la data, uso la data di ultima modifica del file, tanto solo la AllSky gli modifica
	// (..poi qualche genio fa uno script per modificarle per chissà quale motivo, il programma non funziona e partono le bestemmie...)
	$lastedit = filemtime(DIR."/$image");
	// Prendo solo l'ora
	$oraedit = date('H:i',$lastedit);

	// Salvo le immagini per il timelapse
    if( $lastedit > $inizio and $lastedit < $fine ) {
        exec("/usr/bin/composite -dissolve 50 -tile logo.png ".DIR."/$image $tmpdir/$image");
		fwrite($tl, "$tmpdir/$image\n");
	}

	// Cancello i files più vecchi di una settimana (+ 24 ore di margine per sicurezza), e le immagini del giorno
	if( (time() - $lastedit) > (8*24*60*60) or (strtotime($oraedit) > (strtotime($alba)+(1*60*60)) and strtotime($oraedit) < (strtotime($tramonto)-(1*60*60))) ) {
        unlink(DIR."/$image");
	}	
}
fclose($tl);

// Genero il filmato usando mencoder
$da = date('Ymd');
$cmd = "/usr/bin/mencoder -really-quiet -nosound -noskip -oac copy -ovc x264 -x264encopts preset=slow:bitrate=1200 -o ".DIR."/timelapse_videos/allsky_$da.avi -mf fps=10 'mf://@$tlname'";
$output = exec($cmd, $output, $retval);

if($retval) {
	fwrite(STDERR, "ERRORE: Errore nella creazione del filmato!\n\n$output");
} else {
	unlink($tlname);
}

// Comprimo il filmato ulteriormente per l'upload sul sito
$tlupname = 'timelapsetest.mp4';
$cmd = "/usr/bin/ffmpeg -nostats -loglevel 0 -i ".DIR."/timelapse_videos/allsky_$da.avi -codec:v libx264 -profile:v high -preset slow -b:v 900k -maxrate 900k -bufsize 1800k -vf scale=640:480 -threads 2 -an $tlupname";
$output = exec($cmd, $output, $retval);

if($retval) {
	fwrite(STDERR, "ERRORE: Errore nella compressione del filmato!\n\n$output");
}

// Invio via FTP del filmato
$ftpconn = ftp_connect(FTPHOST) or die("Impossibile collegarsi al server FTP\n");
ftp_login($ftpconn, FTPUSER, FTPPASS) or die("Impossibile effettuare il login al server FTP\n");
ftp_pasv($ftpconn, true);
ftp_put($ftpconn, "allsky.mp4",$tlupname, FTP_BINARY) or die("Errore nell'invio via FTP del file\n");
ftp_close($ftpconn);

// Cancello files temporanei
unlink($tlupname);
system("/bin/rm $tmpdir/*");
rmdir($tmpdir);
?>
