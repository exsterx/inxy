<?php
//ini_set("display_errors","1");
//ini_set("error_reporting", E_ALL);
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
        unset($item);
        $item[] = $g['embed'];
        $item[] = HTH_WORK . $g['path'] . '2.jpg';
        $item[] = clearTitle($g['title']);
        $item[] = $g['niches'];
        $item[] = getDuration($g['duration']);
        $item[] = date("Y-m-d",$g['dateAdded']);
        $strings[] = join("|",$item);
    }
}
echo join("\n",$strings);
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