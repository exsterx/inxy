<?php

function checkIpMask($mask, $ip) {
    $mask = explode("\r\n", $mask);
    $ip = explode('.', $ip);
    if (is_array($mask)) {
        foreach ($mask as $v) {
            $v = explode('.', $v);
            if (($v[0] == '*' || $v[0] == $ip[0]) && ($v[1] == '*' || $v[1] == $ip[1]) && ($v[2] == '*' || $v[2] == $ip[2]) && ($v[3] == '*' || $v[3] == $ip[3])) {
                return true;
            }
        }
    }
    return false;
}

function XMail($from, $to, $subj, $text) {
    $subject = '=?koi8-r?B?' . base64_encode(convert_cyr_string($subj, "w", "k")) . '?=';
    $headers = "Content-type: text/html; charset=koi8-r \r\n";
    $headers .= "From: " . SITE_NAME . " MailRobot <" . $from . ">\r\n";
    $headers .= "Reply-To: " . $from . "\n\n";
    $text = convert_cyr_string($text, "windows-1251", "koi8-r");
    return @mail("$to", "$subject", $text, $headers);
}

function check_email_addy($Email){
	if (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'.'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $Email)) {
		return 1;
	}else{
		return 0;
	}
}

function getMovieSuffix($title){
    $aReplaced['bad']=array('?','!',' ','&','*','$','#','@','/');$aReplaced['good']=array('','','-','','','','','','');
    return strtolower(str_replace($aReplaced['bad'],$aReplaced['good'],preg_replace("'[^a-zA-Z\d\s-\.,\?\!]'", "", $title))).'.html';
}

function encode_ps($str){return base64_encode(strrev(substr($str,0,3)).strrev(substr($str, 3)));}
function decode_ps($str){return strrev(substr(base64_decode($str),0,3)).strrev(substr(base64_decode($str),3));}

function sec2time($s) {
	   $d = intval($s/86400);
	   $s -= $d*86400;
	   $h = intval($s/3600);
	   $s -= $h*3600;
	   $m = intval($s/60);
	   $s -= $m*60;
	   if ($d) $str = $d . ':';
	   if ($h) $str .= $h . ':';
	   if ($m) { $str .= str_pad($m,2,'0',STR_PAD_LEFT) . ':'; } else { $str .= "00:"; }
	   $str .= str_pad($s,2,'0',STR_PAD_LEFT);
	   return $str;
}

function save_html($path,$page,$html){
    $file = fopen( $path.$page, "w");
	fwrite( $file, $html);
	fclose( $file );
	chmod ( $path.$page, 0777);
}

function buildGalleryHTML($template,$gallery){
    global $sql,$fullVarsList,$settings;
    $acceptedExtension = unserialize($settings->get('serializeThumbExtensions'));
    $acceptedVideoExtension = unserialize($settings->get('serializeVideoExtensions'));
    if(!$fullVarsList){
        $sql->Query("SELECT * FROM ".TPREFIX."vars WHERE active=1");
        foreach($sql->GetAssoc() as $v){
            $fullVarsList[$v['pattern']] = $v;
        }
    }
    if(!$template){return '';}
    preg_match_all("!<%(.*?)%>!is",$template,$out);
    foreach($out[0] as $k=>$p){
        if($fullVarsList[$p]){
            foreach($acceptedExtension as $e){
                if(stristr($gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))],$e) && !stristr($gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))],'http://')){
                    $items['replacements'][$p] = $settings->get('thumbsURL') . $gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))];
                    continue 2;
                }
            }
            foreach($acceptedVideoExtension as $e){
                if(stristr($gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))],$e) && !stristr($gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))],'http://')){
                    $items['replacements'][$p] = $settings->get('videosURL') . $gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))];
                    continue 2;
                }
            }
            $items['replacements'][$p] = $gallery[str_replace(" ","_",strtolower($fullVarsList[$p]['name']))];
            continue;
        }else{
            $part = explode("_",strrev($p),2);$limit = str_replace("%>","",strrev($part[0]));
            $p2 = strrev($part[1]) . '%>';
            if(is_numeric($limit) && $fullVarsList[$p2]){
                $items['replacements'][$p] = get_subphrase_as_is($gallery[str_replace(" ","_",strtolower($fullVarsList[$p2]['name']))],$limit) . '...';
                continue;
            }
        }
        if($gallery[strtolower($out[1][$k])]){
            if(strtolower($out[1][$k]) == 'local_path' && !stristr($gallery[strtolower($out[1][$k])],'http://')){
                $items['replacements'][$p] = $settings->get('mainURL') . $gallery[strtolower($out[1][$k])];
                if(stristr($items['replacements'][$p],'index.')){
                    $pp = explode("index.",$items['replacements'][$p]);
                    $items['replacements'][$p] = $pp[0];
                }
                continue;
            }
            if(strtolower($out[1][$k]) == 'data'){
                $items['replacements'][$p] = date($settings->get('dateFormat'),$gallery[strtolower($out[1][$k])]);
                continue;
            }
            $items['replacements'][$p] = $gallery[strtolower($out[1][$k])];
            continue;
        }
        $items['replacements'][$p] = '';
    }
    foreach($items['replacements'] as $p=>$r){$template = preg_replace("/".$p."/",$r,$template);}
    return $template;
}

function get_subphrase_words_clear($txt,$limit){
 for($i=0;$i<strlen($txt);$i++){if(preg_match("/^([a-z ]*)$/i",$txt{$i})){$new.=$txt{$i};}else{$new.=' ';}}
 foreach(explode(" ",trim($new)) as $k=>$t)
  {
  if(!$res){$res=$t;continue;}
  if(($k+1) > $limit){break;}
  $res.=" ".$t;
  }
  return $res;
}

function get_subphrase($src,$limit)
{
for($i=0;$i<strlen($src);$i++){if(preg_match("/^([a-z ]*)$/i",$src{$i})){$new.=$src{$i};}else{$new.=' ';}}
foreach(explode(" ",trim($new)) as $t)
 {
 if(!$res){$res=$t;continue;}
 if(strlen($res) < $limit && strlen($res." ".$t) < $limit){$res.=" ".$t;}
 if(strlen($res) > $limit || strlen($res." ".$t) > $limit){break;}
 }
 return $res;
}

function get_subphrase_as_is($src,$limit)
{
foreach(explode(" ",trim($src)) as $t)
 {
 if(!$res){$res=$t;continue;}
 if(strlen($res) < $limit && strlen($res." ".$t) < $limit){$res.=" ".$t;}
 if(strlen($res) > $limit || strlen($res." ".$t) > $limit){break;}
 }
 return $res;
}

function clearString($txt){
 for($i=0;$i<strlen($txt);$i++){if(preg_match("/^([a-z0-9 ]*)$/i",$txt{$i})){$new.=$txt{$i};}}
 return $new;
}

function get_random_str($length = "5") {
	$key = "";
	$charset = "abcdefghijklmnopqrstuvwxyz";
	$charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$charset .= "0123456789";
	for ($i=0; $i<$length; $i++)
		$key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
	return $key;
}

function getRelativeGalleryPath($gallery){
    global $settings,$sql;
    if($settings->get('useSubFolders')){
        $folder = $settings->get('lastFileFolder');
        if($folder){
            if(is_dir(BUILDPATH . $folder . '/')){
                $files = scandir(BUILDPATH . $folder . '/');
                array_shift($files);
                array_shift($files);
                if(sizeof($files) >=  $settings->get('maxFilesInFolder')){
                    unset($folder);
                }
            }else{
                unset($folder);
            }
        }
        //
        if(!$folder){
            if($settings->get('folderType') == 'random'){
                do{
                    $folder = get_random_str($settings->get('maxCharastersFolder'));
                }while (is_dir(BUILDPATH . $folder . '/'));
            }
            if($settings->get('folderType') == 'counter'){
                if(is_numeric($settings->get('lastFileFolder'))){
                    $folder = $settings->get('lastFileFolder') + 1;
                }else{
                    $folder = 1;
                }
                if(is_dir(BUILDPATH . $folder . '/')){
                    do{
                        $folder++;
                    }while (is_dir(BUILDPATH . $folder . '/'));                    
                }
            }
            if($settings->get('folderType') == 'keywords'){
                $keywords = unserialize($settings->get('serializeKeywords'));
                foreach($keywords as $key){
                    $folder = $key;
                    if(!is_dir(BUILDPATH . $folder . '/')){break;}
                }
                if(!$folder){exit('Please, add more kewords for generate folders!');}
            }
            umask(0);
            mkdir(BUILDPATH . $folder . '/', 0777);
            exec("chmod 777 " . BUILDPATH . $folder . '/');
            $settings->set('lastFileFolder',$folder);
        }
        $path = $folder . '/';
    }else{
        $path = '';
    }
    //
    if(!$settings->get('galleryAsFolder')){
        $fileNameType = $settings->get('filenameType');
        if($fileNameType == 'title'){
            if(!$gallery['title']){$fileNameType = 'random';}
            $str = strtolower($gallery['title']);
            $maxCharasters = $settings->get('maxCharastersFilename');
            $filenameDelimiter = $settings->get('filenameDelimiter');
            do{
                if(!$maxCharasters){$fileNameType = 'random';break;}
                $filename =  str_replace(" ",$filenameDelimiter,get_subphrase($str,$maxCharasters)) . '.' . $settings->get('filenameExtension');
                $maxCharasters--;
            }while (is_file(BUILDPATH . $path . $filename));
        }
        if($fileNameType == 'description'){
            if(!$gallery['description']){$fileNameType = 'random';}
            $str = strtolower($gallery['description']);
            $maxCharasters = $settings->get('maxCharastersFilename');
            $filenameDelimiter = $settings->get('filenameDelimiter');
            do{
                if(!$maxCharasters){$fileNameType = 'random';break;}
                $filename =  str_replace(" ",$filenameDelimiter,get_subphrase($str,$maxCharasters)) . '.' . $settings->get('filenameExtension');
                $maxCharasters--;
            }while (is_file(BUILDPATH . $path . $filename));
        }
        if($fileNameType == 'keywords'){
            $keywords = unserialize($settings->get('serializeKeywords'));$maxIteration = 0;
            do{
                if($maxIteration > 100){$fileNameType = 'random';break;}
                shuffle($keywords); unset($words);
                foreach($keywords as $i=>$word){
                    $words[] = $word;
                    if($i == $settings->get('maxCharastersFilename')){break;}
                }
                $filename = implode($settings->get('filenameDelimiter'),$words) . '.' . $settings->get('filenameExtension');
                $maxIteration++;
            }while (is_file(BUILDPATH . $path . $filename));
        }
        if($fileNameType == 'random'){
            do{
                $filename = get_random_str($settings->get('maxCharastersFilename')) . '.' . $settings->get('filenameExtension');
            }while (is_file(BUILDPATH . $path . $filename));
        }
    }else{
        $folderNameType = $settings->get('filenameType');
        if($folderNameType == 'title'){
            if(!$gallery['title']){$folderNameType = 'random';}
            $str = strtolower($gallery['title']);
            $maxCharasters = $settings->get('maxCharastersFilename');
            $filenameDelimiter = $settings->get('filenameDelimiter');
            do{
                if(!$maxCharasters){$folderNameType = 'random';break;}
                $filename =  str_replace(" ",$filenameDelimiter,get_subphrase($str,$maxCharasters));
                $maxCharasters--;
            }while (is_dir(BUILDPATH . $path . $filename . '/'));
        }
        if($folderNameType == 'description'){
            if(!$gallery['description']){$folderNameType = 'random';}
            $str = strtolower($gallery['description']);
            $maxCharasters = $settings->get('maxCharastersFilename');
            $filenameDelimiter = $settings->get('filenameDelimiter');
            do{
                if(!$maxCharasters){$folderNameType = 'random';break;}
                $filename =  str_replace(" ",$filenameDelimiter,get_subphrase($str,$maxCharasters));
                $maxCharasters--;
            }while (is_dir(BUILDPATH . $path . $filename . '/'));
        }
        if($folderNameType == 'keywords'){
            $keywords = unserialize($settings->get('serializeKeywords'));$maxIteration = 0;
            do{
                if($maxIteration > 100){$folderNameType = 'random';break;}
                shuffle($keywords); unset($words);
                foreach($keywords as $i=>$word){
                    $words[] = $word;
                    if($i == $settings->get('maxCharastersFilename')){break;}
                }
                $filename = implode($settings->get('filenameDelimiter'),$words);
                $maxIteration++;
            }while (is_dir(BUILDPATH . $path . $filename . '/'));
        }
        if($folderNameType == 'random'){
            do{
                $filename = get_random_str($settings->get('maxCharastersFilename'));
            }while (is_dir(BUILDPATH . $path . $filename . '/'));
        }
        //
        umask(0);
        mkdir(BUILDPATH . $path . $filename . '/', 0777);
        exec("chmod 777 " . BUILDPATH . $path . $filename . '/');
        $filename .= '/index.' . $settings->get('filenameExtension');
    }
    return $path . $filename;
}

function print_log($message)
{
global $log,$_GET,$ct_line,$buf;
if($_GET['info']=="yes")
  {
  if($message!='|')
    {
	echo "<font style=\"font:normal 12px Arial;\">".$message."</font>";
	}
  else
    {
	echo $message;$ct_line++;
	if($ct_line==50){echo '<br>';$ct_line=0;}
	}
  echo $buf.$buf.$buf;
  }
if($message!='|'){$log.=$message;}
return $log;
}

function rmdir_recursive($dir) {
	$files = scandir($dir);
	array_shift($files); // remove '.' from array
	array_shift($files); // remove '..' from array

	foreach ($files as $file) {
		$file = $dir . '/' . $file;
		if (is_dir($file)) {
			rmdir_recursive($file);
			if (is_dir($file))
				rmdir($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dir);
}

function browse($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_REFERER, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.1) Gecko/2008071615 Fedora/3.0.1-1.fc9 Firefox/3.0.1");
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  //curl_setopt($ch, CURLOPT_COOKIE, 'age_check=1');
  $content = curl_exec($ch);
  return $content;
}
?>