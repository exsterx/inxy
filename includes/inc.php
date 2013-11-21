<?php
  function geturl($url,$ref = false, $proxy = false){
      $ch   =   curl_init($url);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_COOKIE, 'cookAV=1&age_check=1');
      curl_setopt($ch, CURLOPT_COOKIEFILE, CDIR.'/1.txt');
      curl_setopt($ch, CURLOPT_COOKIEJAR, CDIR.'/1.txt');
      if($proxy){
          curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
          curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
          curl_setopt($ch, CURLOPT_PROXY, $proxy);
      }
      curl_setopt($ch, CURLOPT_REFERER, $ref);
      $get  =   curl_exec($ch);
      curl_close($ch);
      return $get;
  }
  
  function my_write($ch, $str){
      global $per_movie, $buff;
      $buff .= $str;
      if( (strlen($buff)/1024) > $per_movie ){
          return false;
      }else{
          return strlen($str);
      }
  }
  
  function geturl_save($url, $proxy = false){
      global $per_movie, $buff;
      $ch   =   curl_init($url);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_COOKIE, 'cookAV=1&age_check=1');
      curl_setopt($ch, CURLOPT_COOKIEFILE, CDIR.'/1.txt');
      curl_setopt($ch, CURLOPT_COOKIEJAR, CDIR.'/1.txt');
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

  function elog($str){
      fwrite(fopen("elog.txt","w"),"[".date('Y-m-d H:i:s')."] {$str} \n");
  }
  
  function crop($src, $dst){
//      $iminfo = @getimagesize($src);
//      $z = min ($iminfo[0] / TH_W, $iminfo[1] / TH_H);
//      $crop_str =  TH_W . ('' . 'x' .  TH_H . '+') . '0+0';
//      $s = exec ('convert' . ' -quality 85  -gravity center  -resize ' . round ( $iminfo[0] / $z) . 'x' . round ( $iminfo[1] / $z) . ('' . '! -crop ' . $crop_str. ' ' . $src . '  -filter Blackman -modulate 110,102,100 -sharpen 1x1 -enhance '.$dst));
      $s = exec ('convert' . ' -quality 85 '. $src .'  '.$dst);
  }
  
  function get_duration_splitted($duration){
      $res=array();
      $res['minutes']=ceil($duration/60);
      $res['seconds']=ceil($duration-($res['minutes']*60));
      if ($res['seconds']<0){
            $res['minutes']=$res['minutes']-1;
          $res['seconds']=$res['seconds']+60;
      }
      if ($res['minutes']<1) {$res['minutes']=0;}
      if ($res['seconds']<1) {$res['seconds']=0;}
      if ($res['seconds']<10) {$res['seconds']="0".$res['seconds'];}
      return $res['minutes'].':'.$res['seconds'];
  }
  
  function save_tags($gid, $tags){
      global $sql;
      echo("Saving tags\n");
      foreach($tags as $tag){
          $tag = str_replace('\'','',$tag);
          $tag = mysql_real_escape_string($tag);
          $insert[] = "({$gid},'{$tag}')";
      }
      $sql->Query("INSERT INTO tags_stream (`gid`,`tags`) VALUES ".implode(',',$insert).";");
  }
  
  function make_shots($tube, $url, $id, $proxy = false){
      global $sql, $c, $buff, $per_movie, $file;
      echo("Making shots from {$url}\n");
      switch ($tube){          
          case 'shufuni':
          {
              $pattern = $url.'?fs=%s';
              break;
          }
          case 'pornhub':
          {
              $pattern = $url.'&start=%s';
              break;
          }
          case 'deviantclip':
          {
              if(strpos($url,'?') !== FALSE) $pattern = $url.'&fs=%s';
              else $pattern = $url.'?fs=%s';
              break;              
          }
          default:
          {
              $pattern = $url.'?start=%s';
              break;
          }
      }
      
      $flv = new FLV();
      echo("Getting first info\n");
      $buff = ''; $per_movie = 200; $c = 0;
      geturl_save($url, $proxy); 
      
      $tmp_dir = 'tmp_'.substr( md5( microtime() ), 0, 16);   
      if(!is_dir($tmp_dir)) exec('mkdir -m 0777 '.$tmp_dir);
//      if(!is_dir($tmp_dir)) mkdir($tmp_dir);

      if(substr($buff, 0, 3) != 'FLV'){
          echo("Video has not FLV format\n");
          $sql->Query("DELETE  FROM galls_stream WHERE `id` = {$id}");
          $sql->Query("DELETE  FROM tags_stream WHERE `gid` = {$id}");
          exec('rm -rf '.$tmp_dir);  
          return false;          
      }
      
      $f = fopen($tmp_dir.'/meta.flv', "w");
      fwrite($f, $buff);
      fclose($f);
      
      
      if(!file_exists($tmp_dir.'/meta.flv') || filesize($tmp_dir.'/meta.flv') == 0){
          echo("Video not saving\n");
          $sql->Query("DELETE  FROM galls_stream WHERE `id` = {$id}");
          $sql->Query("DELETE  FROM tags_stream WHERE `gid` = {$id}");
          exec('rm -rf '.$tmp_dir);  
          return false;          
      }
      
      $flv->open($tmp_dir.'/meta.flv');
      $tag = $flv->getTag(array(''));
      $frames = $tag->value['keyframes']['filepositions'];
      if( isset($tag->value['filesize']) && isset($tag->value['duration']) ){
          $per_movie = ceil($tag->value['filesize'] / $tag->value['duration'] / 1024);
      }else{
          $per_movie = 96;
      }
     
      unset($flv, $tag);
      
      $frames_count = ceil(count($frames) / TH_COUNT);
      $raw_frames = array_chunk($frames, $frames_count);
      
      $frames = array();
      foreach($raw_frames as $val){
          $our = floor(count($val) / 2);
          $frames[] = $val[$our];
      }
      
      $save_dir = rand(0,100);
      $sql->Query("UPDATE galls_stream SET `th` = {$save_dir} WHERE `id` = {$id};");
      if(!is_dir($save_dir)) exec('mkdir -m 0777 '.$save_dir);   
      $save_dir = $save_dir.'/'.$id;
      if(!is_dir($save_dir)) exec('mkdir -m 0777 '.$save_dir);
       
      foreach($frames as $k=> $offset){
          echo("Getting frame {$k}\n");
          $get_str = sprintf($pattern, $offset);
          $buff = '';
          geturl_save($get_str, $proxy);         
         
          fwrite(fopen($tmp_dir.'/'.$k.'.flv',"w"),$buff);
          
          exec("ffmpeg -i {$tmp_dir}/{$k}.flv -an -ss 0 -r 1 -vframes 1 -y -f mjpeg ".$tmp_dir."/{$k}.jpg");
          crop($tmp_dir.'/'.$k.'.jpg', $save_dir.'/'.$k.'.jpg');
      }
      
      
      if( count(glob($save_dir.'/*.jpg')) == 0 ){
          echo("Thumbs not found\n");
          $sql->Query("DELETE  FROM galls_stream WHERE `id` = {$id}");
          $sql->Query("DELETE  FROM tags_stream WHERE `gid` = {$id}");
          exec('rm -rf '.$tmp_dir);  
          exec('rm -rf '.$save_dir);  
          return false;
      }
      
      exec('rm -rf '.$tmp_dir);
  }  
?>
