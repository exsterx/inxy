<?php
//ini_set("display_errors","1");
//ini_set("error_reporting", E_ALL);
header ("Cache-Control: no-cache, must-revalidate, max-age=0");
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Pragma: no-cache");
$xml = '<?xml version="1.0" encoding="windows-1251"?>'.
'<rss version="2.0">'.
'<channel>'.
'<generator>Phil Feed Gen</generator>'.
'<pubDate>'.gmstrftime("%b %d %Y %H:%M:%S",time()).'</pubDate>'.
'<title>Insect FHG Gen</title>'.
'<description>Gen Feed</description>'.
'<link>http://oururl.com</link>'.
'<language>en</language>';
include_once('/home/httpd/18gaytube.net/content/html/youporn/includes/prepare.php');
$sql->Query("SET NAMES utf8");
# WORK #########################################################################
if(!$_GET['count'] || !is_numeric($_GET['count'])){$_GET['count'] = 100;} if($_GET['count'] > 10000){$_GET['count'] = 10000;}
if(!is_numeric($_GET['offset'])){unset($_GET['offset']);}
if(!is_array($_GET['tag'])){unset($_GET['tag']);}
if(!$_GET['orientation']){unset($_GET['orientation']);}
if(!$_GET['domain']){unset($_GET['domain']);}
if($_GET['domain']){
    $path = str_replace(DOMAIN,$_GET['domain'],HWORK);
}else{
    $path = HWORK;
}
# WFIELDS #
$left_join = '';$group_by = '';
$wfields[] = "status='active'";
if(is_array($_GET['tag'])){
    foreach($_GET['tag'] as $tag){
        if(!trim($tag) || strlen(trim($tag)) < 3){continue;}
        $orfields[] = "search LIKE '%".trim($tag)."%'";
        //$orfields[] = "keywords LIKE '%".trim($tag)."%'";
    }
    if(sizeof($orfields)){$wfields[] = "(".implode(" OR ",$orfields).")";}
}
if($_GET['orientation'] && strlen($_GET['orientation'])>=3){
    $wfields[] = "orientation='".$_GET['orientation']."'";
}

if(!isset($_GET['offset'])){
    $sql->Query("SELECT * FROM galleries ".$left_join." WHERE " . implode(" AND ", $wfields) . $group_by ." ORDER BY RAND() LIMIT " . $_GET['count']);
}else{
    $sql->Query("SELECT * FROM galleries ".$left_join." WHERE " . implode(" AND ", $wfields) . $group_by ." ORDER BY dateAdded DESC LIMIT " . $_GET['offset'] . "," . $_GET['count']);
}
if($sql->size_of_result){
    foreach($sql->GetAssoc() as $g){
        if(!$g['niches']){$g['niches'] = $_GET['tag'];}
        if($g['tags']){$g['niches'] .= ','. $g['niches'];}
        $xml .= "<item>\n".
	"<title>".clearTitle($g['title'])."</title>\n".
        "<description><![CDATA[<a href=\"".$path . $g['path'] . "index.html\">";
        for($i=1;$i<=$settings->get('limitThumbs');$i++){
            $xml .= '<img src="' . HTH_WORK . $g['path'] . $i . '.jpg">';
        }
        $xml .= "</a>]]></description>\n".
        "<pubDate>".gmstrftime("%b %d %Y %H:%M:%S",$g['dateAdded'])."</pubDate>\n".
	"<link>".$path . $g['path'] . "index.html</link>\n".
	"<StreamRotatorDuration>".getDuration(rand(60,180))."</StreamRotatorDuration>\n".
        "<StreamRotatorInfo>".clearTitle($g['niches'])."</StreamRotatorInfo>\n".
        "</item>\n";
    }
}


$xml .='</channel></rss>';
header ("Content-type: text/xml");
echo $xml;
# FUNCTION ####################################################################
function getDuration($second){
    $min = floor($second/60);
    $sec = $second - ($min * 60);
    return $min . ' min ' . $sec . ' sec';
}
function clearTitle($txt){
 for($i=0;$i<strlen($txt);$i++){if(preg_match("/^([a-z;!,. ]*)$/i",$txt{$i})){$new.=$txt{$i};}else{$new.=' ';}}
 return $new;
}
?>