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
define('PATH_DIR','/home/httpd/18gaytube.net/content/html/youporn/');
define('WORK','/home/httpd/funporntube.com/content/html/vids/a11/12/');
define('TH_WORK','/home/httpd/18gaytube.net/content/html/youporn/thumbs/');

define('DOMAIN','funporntube.com');
define('HWORK','http://funporntube.com/vids/a11/12/');
define('HTH_WORK','http://18gaytube.net/youporn/thumbs/');

//
define('WGET_PATH','/usr/bin/wget');
define('IM','/usr/bin/convert');
define('MPLAYER_PATH','/usr/bin/mplayer');
define('FFMPEG_PATH','/usr/bin/ffmpeg');
define('PHP','/usr/bin/php');
define('WGET','/usr/bin/wget');
?>