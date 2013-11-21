<?php
//ini_set("display_errors","1");
//ini_set("error_reporting", E_ALL);
set_time_limit(60 * 60 * 10);
include_once('/home/httpd/18gaytube.net/content/html/youporn/includes/prepare.php');
define("LOG","/home/httpd/18gaytube.net/content/html/youporn/log.txt");
$sql->Query("SET NAMES utf8");
# WORK #########################################################################
if(is_numeric($argv[1])){
    $cl_pages = $argv[1];
}
if($argv[2] && strlen($argv[2])>2){
    $niches[]['name'] = trim($argv[2]);
}
# GET NICHES #
# NICHES #
if(!$niches){
    $sql->Query("SELECT * FROM niches ORDER BY name");
    if($sql->size_of_result){
        $niches = $sql->GetAssoc();
    }else{
        exit('Niches not found!');
    }    
}
if(sizeof($niches)){
    unlink(LOG);
    umask(0);file_put_contents(LOG , "NICHE | PAGES | ADDED | DUPLICATES" . "\n", FILE_APPEND);exec("chmod 777 ".LOG);
    foreach($niches as $n){
        unset($html,$data,$pages,$limit,$logString,$report); $logString = $report = array();$report['movies'] = $report['duplicates'] = 0;
        $isCategory = $isTag = false;
        if($n['name'] == 'gay'){
            $page =  browse('http://www.youporngay.com/search/views/?category_id=0&query='.$n['name'].'&type=gay');
        }else{
            $page =  browse('http://www.youporngay.com/search/views/?category_id=0&query='.$n['name'].'&type=straight');
        }
        if(stristr($page,"No videos found")){continue;}
        preg_match_all("#<a href=\".*?page=(.*?)\">#is", $page , $data);
        if(is_numeric(max($data[1]))){$limit = max($data[1]);}else{$limit=1;}
        if(!$cl_pages){
            $pages = $limit;
        }else{
            if($limit && $limit < $cl_pages){
                $pages = $limit;
            }else{
                $pages = $cl_pages;
            }
        }
//        $location = getNewLocation('http://www.extremetube.com/videos?search='.$n['name'].'&o=mv');
//        if(stristr($location,'/category/')){
//           $isCategory = true;
//           $logString[] = 'http://www.extremetube.com/category/'.$n['name'].'&o=mv';
//        }
//        if(stristr($location,'/keyword/')){
//           $isTag = true;
//           $logString[] = 'http://www.extremetube.com/videos/keyword/'.$n['name'].'&o=mv';
//        }
        if(!$isTag && !$isCategory){
            $logString[] = 'http://www.youporngay.com/search/views/?category_id=0&query='.$n['name'].'&type=straight';
        }
        for($p=1;$p<=$pages;$p++){
            echo "Niche: " . $n['name'] . " Page: " . $p . "\n";
            unset($data);
            if($n['name'] == 'gay'){
                preg_match_all("#<li class=\"videoBox grid_3\">.*?<a href=\"(.*?)\?.*?\">.*?<div class=\"duration\">(.*?)<span>.*?</li>#is",browse('http://www.youporngay.com/search/views/?category_id=0&query='.$n['name'].'&type=gay&page=' . $p) , $data);
            }else{
                preg_match_all("#<li class=\"videoBox grid_3\">.*?<a href=\"(.*?)\?.*?\">.*?<div class=\"duration\">(.*?)<span>.*?</li>#is",browse('http://www.youporngay.com/search/views/?category_id=0&query='.$n['name'].'&type=straight&page=' . $p) , $data);
            }
            if(sizeof($data[1])){
                $logString['pages'] = $p;
                //echo "Is links from page ".$p."\n";exit;
                foreach($data[1] as $k=>$linkMovie){
                    $d = redtubeDuration($data[2][$k]);
                    $sql->Query("SELECT * FROM galleries WHERE url = 'http://www.youporngay.com".$linkMovie."'");
                    if(!$sql->size_of_result){
                        $sql->Query("INSERT INTO galleries SET url = 'http://www.youporngay.com".$linkMovie."', duration = ".$d.",dateAdded = " . time());
                        $report['movies']++;
                    }else{
                        $report['duplicates']++;
                    }
                    
                }
            }
        }
        $logString[] = $report['movies'];
        $logString[] = $report['duplicates'];
        umask(0);file_put_contents(LOG , join(" | ", $logString) . "\n", FILE_APPEND);exec("chmod 777 ".LOG);
    }   
}
print_r($report);
// cd /home/httpd/bbw-porntube.com/content/html/xvideos; /usr/bin/php import.php 30
function redtubeDuration($str){
    $substring = explode(":",$str);
    if(sizeof($substring)==2){return ($substring[0] * 60) + ceil($substring[1]);}
    if(sizeof($substring)==3){return ($substring[0] * 60 * 60) + ($substring[1]*60) + ceil($substring[2]);}
    
}
function getNewLocation($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_NOBODY, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071615 Fedora/3.0.1-1.fc9 Firefox/3.0.1");
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  $headers = curl_exec($ch);
  foreach(explode("\n",$headers) as $h){
      if(stristr($h,'location')){return trim(str_replace("Location: ","",trim($h)));}
  }
  curl_close($ch);
  return '';
}
?>