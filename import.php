<?php
//ini_set("display_errors","1");
//ini_set("error_reporting", E_ALL);
set_time_limit(60 * 60 * 10);
include_once('/home/httpd/bbw-fat-tube.com/content/html/thaflix/includes/prepare.php');
include_once('/home/httpd/bbw-fat-tube.com/content/html/thaflix/includes/curl.php');
$sql->Query("SET NAMES utf8");
# WORK #########################################################################

# WORK #
if(is_numeric($argv[1])){
    $cl_pages = $argv[1];
}
if($argv[2] && strlen($argv[2])>2){
    $niches[]['name'] = trim($argv[2]);
}
//print_r($argv);exit;
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
    foreach($niches as $n){
        unset($html,$data,$pages,$limit);$isCategory = $isTag = false;
        //get pages
        if($n['name']=='gay' || $n['name']=='shemale'){
            $curl = new Curl();
            $curl->init('http://www.tnaflix.com/search.php?page=1&what='.$n['name'].'&category=&sb=rating&su=anytime&sd=all&dir=desc')->serverfriendly()->exec();
            if($n['name']=='gay'){
                $curl->addCookie('content_filter3', array('value'=>'type='.$n['name'].'&filter=cams','expires'=>'Thu, 31-Jan-2014 08:37:45 GMT','path'=>'/'));
            }else{
                $curl->addCookie('content_filter3', array('value'=>'type=tranny&filter=cams','expires'=>'Thu, 31-Jan-2014 08:37:45 GMT','path'=>'/'));
            }
            $curl->init('http://www.tnaflix.com/search.php?page=1&what='.$n['name'].'&category=&sb=rating&su=anytime&sd=all&dir=desc')->serverfriendly()->exec();
            $page = $curl->getContent();            
        }else{
            $page =  browse('http://www.tnaflix.com/search.php?page=1&what='.$n['name'].'&category=&sb=rating&su=anytime&sd=all&dir=desc');
        }
        if(stristr($page,"No results for")){continue;}
        preg_match_all("#search.php\?page=(.*?)&#is", $page , $data);
        if(sizeof($data[1]) && is_numeric(max($data[1]))){$limit = max($data[1]);}else{$limit=1;}
        if(!$cl_pages){
            $pages = $limit;
        }else{
            if($limit && $limit < $cl_pages){
                $pages = $limit;
            }else{
                $pages = $cl_pages;
            }
        }
        
        for($p=1;$p<=$pages;$p++){
            echo "Niche: " . $n['name'] . " Page: " . $p . "\n";
            unset($data);
            if($n['name']=='gay' || $n['name']=='shemale'){
                $curl->init('http://www.tnaflix.com/search.php?page='.$p.'&what='.$n['name'].'&category=&sb=rating&su=anytime&sd=all&dir=desc')->serverfriendly()->exec();
                $html = $curl->getContent();
            }else{
                $html = browse('http://www.tnaflix.com/search.php?page='.$p.'&what='.$n['name'].'&category=&sb=rating&su=anytime&sd=all&dir=desc');
            }
            preg_match_all("#<div class=\"video svideo\" id=\"video.*?\">.*?<a href=\"(.*?)\".*?<span class=\"duringTime\">(.*?)</span>.*?</div>#is", $html , $data);
            if(sizeof($data[1])){
                $report['pages']++;
                //echo "Is links from page ".$p."\n";exit;
                foreach($data[1] as $k=>$link){
                    $report['movies']++;
                    $d = explode(":",$data[2][$k],2);
                    $sql->Query("INSERT INTO galleries SET url = 'http://www.tnaflix.com".$link."', duration = ".(($d[0]*60)+$d[1]).",dateAdded = " . time());
                }
            }
        }
    }   
}
print_r($report);
//cd /home/httpd/bbw-fat-tube.com/content/html/thaflix; /usr/bin/php import.php 3 moms
function getNewLocation($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
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