<?php
session_start();
error_reporting(0);
include_once(dirname(__file__).'/config.php');
include_once(dirname(__file__).'/sqlme.php');
include_once(dirname(__file__).'/lib.php');
include_once(dirname(__file__).'/Settings.php');
//
import_request_variables('GP', 'request_');
if (!isset($request_action)) {
    $request_action = 'main';
}
$settings = new Settings();
$sql = new sqlme();
//
define('PATH_DIR','/home/httpd/bbw-fat-tube.com/content/html/thaflix/');
define('WORK','/home/httpd/funporntube.com/content/html/vids/a11/04/');
define('TH_WORK','/home/httpd/bbw-fat-tube.com/content/html/thaflix/thumbs/');

define('DOMAIN','funporntube.com');
define('HWORK','http://funporntube.com/vids/a11/04/');
define('HTH_WORK','http://bbw-fat-tube.com/thaflix/thumbs/');

//
define('WGET_PATH','/usr/bin/wget');
define('IM','/usr/bin/convert');
define('MPLAYER_PATH','/usr/bin/mplayer');
define('FFMPEG_PATH','/usr/bin/ffmpeg');
define('PHP','/usr/bin/php');
define('WGET','/usr/bin/wget');
?>