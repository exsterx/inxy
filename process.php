<?php
error_reporting(2047);
set_time_limit(60 * 60 * 2);
ignore_user_abort();
include_once(dirname(__file__) . '/includes/prepare.php');
include_once(dirname(__file__) . '/includes/SQLmanager.php');
$id = $argv[1];
if($_GET['id']){$id = $_GET['id'];}
//
try {
    $sql->Query("UPDATE galleries SET status='process',dateProcess = '".(time() + (60*60*1))."' WHERE galleryID=" . $id);
    $sql->Query("SELECT g.* FROM galleries g WHERE g.galleryID = " . $id);
    if($sql->size_of_result){$g = $sql->GetAssocFirst();}else{exit('without ID');}
    //text info
    unset($bd,$isOrientation,$niches,$tags,$file);$isOrientation = false;
    $bd['duration'] = $g['duration'];
    $html = browse($g['url']);
    unset($data); preg_match("#<h1.*?>(.*?)</h1>#is", $html, $data);
    if(trim($data[1])){
        $bd['title'] = $g['title'] = trim($data[1]);
    }else{
        throw new Exception('Title not found.');
    }
//    unset($data); preg_match("#<span class=\".*?\">Categories:</span>(.*?)<div class=\"reset\"></div>#is", $html, $data);
//    if($data[1]){
//        unset($out); preg_match_all("#<a href=\".*?\">(.*?)</a>#is", $data[1], $out);
//        if(sizeof($out[1])){
//            sort($out[1]);$niches  = array_unique($out[1]); $niches = strtolower(join(",",$niches));
//            $bd['niches'] = $niches;
//        }
//    }
    unset($data); preg_match("#<label>Tags:</label>(.*?)</ul>#is", $html, $data);
    if($data[1]){
        unset($out); preg_match_all("#<a href=\".*?\">(.*?)</a>#is", $data[1], $out);
        if(sizeof($out[1])){
            sort($out[1]);$tags  = array_unique($out[1]); $tags = strtolower(join(",",$tags));
            $bd['tags'] = $tags;
        }
    }
    if(stristr($bd['title'],'shemale') || stristr($bd['title'],'ladyboy') || stristr($bd['niches'],'shemale') || stristr($bd['niches'],'ladyboy') || stristr($bd['tags'],'shemale') || stristr($bd['tags'],'ladyboy')){
        $bd['orientation'] = 'shemale';$isOrientation = true;
    }
    if(!$isOrientation && (stristr($bd['title'],'gay') || stristr($bd['title'],'twink') || stristr($bd['niches'],'gay') || stristr($bd['niches'],'twink') || stristr($bd['tags'],'gay') || stristr($bd['tags'],'twink'))){
        $bd['orientation'] = 'gay';
    }
    $bd['error'] = "";
    $bd['search'] = $bd['title'];
    //if($bd['niches']){$bd['search'] .= ' ' . $bd['niches'];}
    if($bd['tags']){$bd['search'] .= ' ' . $bd['tags'];}
    unset($data); preg_match("#clip_text'\)\.val\(\"(.*?)<a#is", $html, $data);
    if($data[1]){
        $bd['embed'] = stripslashes(trim($data[1])).'</iframe>';
        $bd['embed'] = str_replace("&lt;","<",$bd['embed']);
        $bd['embed'] = str_replace("&gt;",">",$bd['embed']);
        $bd['embed'] = str_replace("&gt;",">",$bd['embed']);
        $bd['embed'] = str_replace("&quot;","'",$bd['embed']);
        $bd['embed'] = str_replace("&amp;","&",$bd['embed']);
    }else{
        throw new Exception('Embed not found.');
    }
    //print_r($bd);
    //link
    unset($data); 
    if(preg_match("#video.src = '(.*?)'#is", $html, $data)){
        if(stristr($data[1],'http')){$file = urldecode($data[1]);}
    }
    //exit($file);
    if(!$file){        throw new Exception('Link to FLV not found.');    }
    //get shots
    $relativePath = setDownloadPath($g);
    $path = WORK . $relativePath; 
    $thumbPath = TH_WORK . $relativePath; 
    $movie = $path . 'movie.flv';
    exec(WGET . ' -U Opera/9.51 --referer=\''.$g['url'].'\' "' . $file . '" -O ' . $movie);
    if(!filesize($movie)){
          exec('rm -rf '.$path);
          throw new Exception('Error download movie');
    }
    //check duration
    exec(MPLAYER_PATH . " -vo null -ao null -frames 0 -identify '" . $movie . "'", $res);
    foreach ($res as $v) {
        $v = strtolower($v);
        if (strstr($v, 'id_length=')) {
            $bd['duration'] = ceil(str_replace("id_length=", "", $v));
        }
    }
    
    $numberThumbs = $settings->get('limitThumbs');
    $periodGetThumb = round($numberThumbs / ($bd['duration'] - (5 + 5)), 2);
    if ($periodGetThumb < 0.05) {
        $periodGetThumb = 0.05;
    }
    exec(FFMPEG_PATH . " -i " . $movie . " -an -ss 5 -r " . $periodGetThumb . " -vframes " . $numberThumbs . " -y " . $thumbPath . "%db.jpg");
    
    if(is_file($movie)){unlink($movie);}
    if( count(glob($thumbPath.'/*.jpg')) != $settings->get('limitThumbs') ){
        exec('rm -rf '.$thumbPath);
        throw new Exception('Thumbs not was created');
    }
    foreach(glob($thumbPath.'/*.jpg') as $thumb){
        if(!filesize($thumb)){
        exec('rm -rf '.$thumbPath);
        throw new Exception('Found thumb with zero file size');
        break;
        }
    }
    $jpegs = glob($thumbPath.'/*.jpg');$i=1;natcasesort($jpegs);
    foreach ($jpegs as $thumb) {
        umask(0);
        rename($thumb, $thumbPath . $i . '.jpg');
        exec("chmod 777 " . $thumbPath . $i . '.jpg');
        $i++;
    }
    //save to DB
    unset($sfields);
    foreach($bd as $k=>$v){
        $sfields[] = $k . "=\"".  mysql_escape_string(trim($v))."\"";
    }
    if($sfields){$sql->Query("UPDATE galleries SET " . implode(",",$sfields) . " WHERE galleryID=".$g['galleryID']);}
    //
    unset($replacements);
    //generate HTML
    unset($html);
    $html = "<?php\n";
    $html .= '$desc'." = '".addslashes(stripslashes($bd['title']))."';\n";
    $html .= '$len'." = '".getDuration($bd['duration'])." ';\n";
    $html .= '$embede'." = '" .str_replace("'",'"',$bd['embed']). "';\n";
    $html .= '$thumbs' . " = '";
    for($i=1;$i<=10;$i++){$html .= '<img src="'.HTH_WORK . $relativePath .$i.'.jpg">';}
    $html .= "';\n";
    $html .= "?>";
    umask(0);file_put_contents($path . 'index.html',$html);exec("chmod 777 " . $path . 'index.html');
    //to DB
    $sql->Query("UPDATE galleries SET status='active', path='".$relativePath."' WHERE galleryID = " . $g['galleryID']);
} catch (Exception $e) {
    $error = $e->getMessage();
    $sql->Query("UPDATE galleries SET status='error', error='" . mysql_escape_string($error) . "' WHERE galleryID=" . $g['galleryID']);
    if(is_dir($path)){
        rmdir_recursive($path);
    }
    if(is_dir($thumbPath)){
        rmdir_recursive($thumbPath);
    }
    exit($error);
}

# FUNCTIONS ####################################################################
function getDuration($second){
$min = floor($second/60);
    $sec = $second - ($min * 60);
    return $min . ' min ' . $sec . ' sec';
}
function setDownloadPath($g){
    global $sql,$settings;
    //
    if(!$settings->get('lastFolder')){
        $settings->set('lastFolder',1);
    }
    //
    $relativePath = $settings->get('lastFolder') . '/';
    if(is_dir(WORK . $relativePath)){
        $dirs = scandir(WORK . $path);array_shift($dirs);array_shift($dirs);
        if(sizeof($dirs) >= 500){
            $nextNum = $settings->get('lastFolder')+1;
            $settings->set('lastFolder',$nextNum);
            $relativePath = $nextNum . '/';
        }
    }
    if(!is_dir(WORK . $relativePath)){
        umask(0);
        mkdir(WORK . $relativePath, 0777);
        exec("chmod 777 " . WORK . $relativePath);
    }
    if(!is_dir(TH_WORK . $relativePath)){
        umask(0);
        mkdir(TH_WORK . $relativePath, 0777);
        exec("chmod 777 " . TH_WORK . $relativePath);
    }
    //
    $str = strtolower($g['title']);
    $limit = $settings->get('limitTitleCharasters');$addedSym = '';
    do{
        $folder =  str_replace(" ","_",get_subphrase_custom($str.$addedSym,$limit));
        if(!$addedSym){$addedSym = 0;}
        $addedSym++;
        //$limit --;
    }while (is_dir($path . $folder . '/'));
    $relativePath .= $folder . '/';

    if(!is_dir(WORK . $relativePath)){
        umask(0);
        mkdir(WORK . $relativePath, 0777);
        exec("chmod 777 " . WORK . $relativePath);
    }
    if(!is_dir(TH_WORK . $relativePath)){
        umask(0);
        mkdir(TH_WORK . $relativePath, 0777);
        exec("chmod 777 " . TH_WORK . $relativePath);
    }    
    return $relativePath;
}
function get_subphrase_custom($src,$limit)
{
for($i=0;$i<strlen($src);$i++){if(preg_match("/^([a-z0-9 ]*)$/i",$src{$i})){$new.=$src{$i};}}
$prepare = explode(" ", trim($new));foreach($prepare as $word){if(trim($word)){$words[] = trim($word);}} $new = implode(" ",$words);
foreach(explode(" ",trim($new)) as $t)
 {
 if(!$res){$res=$t;continue;}
 if(strlen($res) < $limit && strlen($res." ".$t) < $limit){$res.=" ".$t;}
 if(strlen($res) > $limit || strlen($res." ".$t) > $limit){break;}
 }
 return $res;
}
  
  function geturl_save($url, $proxy = false){
      global $per_movie, $buff;
      $ch   =   curl_init($url);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
 //     curl_setopt($ch, CURLOPT_COOKIE, 'cookAV=1&age_check=1');
//      curl_setopt($ch, CURLOPT_COOKIEFILE, CDIR.'/1.txt');
//      curl_setopt($ch, CURLOPT_COOKIEJAR, CDIR.'/1.txt');
      curl_setopt($ch, CURLOPT_WRITEFUNCTION,'my_write');      
      if($proxy){
          curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
          curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
          curl_setopt($ch, CURLOPT_PROXY, $proxy);
      }
      $get  =   curl_exec($ch);
      curl_close($ch);
      
      return $get;
  }
  
?>