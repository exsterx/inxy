<?
	class gallery
	{
		function gallery($link)
		{
			$this->gallery = $link;

			$this->link = str_replace(" ", "%20", $link);

			$this->page = "";
			$this->code = 0;
		}

		function open()
		{
			$this->page = "";

			$link = $this->link;
			$link_info = parse_url($link);
			$host = $link_info["host"];
			@$fp = fsockopen($host, $link_info["port"] ? $link_info["port"] : 80, &$errno, &$errstr);
                        
			$header = array ();

			$path = substr($link, strlen("http://" . $host));

			if ($fp)
			{
                
				$request = "GET " . str_replace(" ", "%20", $path) . " HTTP/1.0\r\n";
				$request .= "Host: " . $host . "\r\n";
				if(!strstr($host,"wearehairy.com")){$request .= "Referer: http://" . $host . "/\r\n";}
				$request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322)\r\n";
				$request .= "\r\n";

				fwrite($fp, $request);

				while (!feof($fp))
				{
					$string = fgets($fp, 1024);
					if (!trim($string)) break;
					$header[] = $string;
				}

				$header = join("", $header);

				if (preg_match("/http\/\d\.\d\s+200\s/i", $header))
				{
					while (!feof($fp)) $this->page .= fgets($fp, 1024);
					fclose($fp);
					$result = true;
					$this->code = 200;
				}
				else
				{
					fclose($fp);
					if (preg_match("/http\/\d\.\d\s+(30\d)\s/i", $header, $code))
					{
						$pos = strpos($link, "?");
						if ($pos !== false) $base_link = substr($this->link, 0, $pos);

						if (substr($base_link, -1) == "/") $base_link = substr($base_link, 0, -1);
						else $base_link = dirname($base_link);

						preg_match("/Location: ([\s\S]+)\r*\n/iU", $header, $array);
						$link = $this->_get_full_url($base_link, trim($array[1]));

						$new_host = parse_url($link);
						//echo "NEW!!!";
						//echo "<br>" . $new_host["host"] . "<br>";
						$new_host = $new_host["host"];

						if (strtolower($new_host) == strtolower($host))
						{
							$this->link = trim($array[1]);
							$result = $this->open();
						}
						else
						{
							$this->link = $link;
							$result = $this->open();
//							$this->code = intval($code[1]);
//							$result = false;
						}
					}
					else
					{
						preg_match("/http\/\d\.\d\s+(\d+)\s/i", $header, $array);
						$this->code = intval($array[1]);
						$result = false;
					}
				}
				return $result;
			}
			else
			{
                            $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $this->gallery);
			    curl_setopt($ch, CURLOPT_REFERER, $this->gallery);
			    curl_setopt($ch, CURLOPT_HEADER,0);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			    $this->page = curl_exec($ch);
	                    $out['info'] = curl_getinfo($ch);
	                    curl_close($ch);
  		            $this->code = $out['info']['http_code'];
                            $this->link = $out['info']['url'];
		            if($this->code != 200){return false;}
			}
		}

		function _get_base_link ($link, $page)
		{
			$base_link = "";
			$pos = strpos($link, "?");
			if ($pos !== FALSE) $link = substr($link, 0, $pos);

			if (substr($link, -1) == "/") $base_link = substr($link, 0, -1);
			else $base_link = dirname($link);

			if ($count = preg_match_all("/(<base[^>]+>)/i", $page, $array))
			{
				for ($j = 0; $j < $count; $j++)
				{
					$base_link = $array[1][$j];

					if (preg_match("/href\s*=\s*[\'\"]?([^>\'\"]+)/i", $base_link, $_array))
					{
						$base_link = $_array[1];
						if (substr($base_link, -1) == "/") $base_link = substr($base_link, 0, -1);
						break;
					}
				}
			}
			//
                        if(!strstr($base_link,"http://") && (strstr(strtolower($page),'<base target="_self"') || strstr(strtolower($page),'<base target="_blank">') || strstr(strtolower($page),'<base target=_blank>')))
			  {
                            echo 'bvdbsdbvbsjdvjhsdbvjhb!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!';
			  $lParts=explode("/",strrev($link));unset($lParts[0]);
			  $base_link=strrev(implode("/",$lParts))."/";
			  }
                        if(stristr($base_link,'hustler'))
                          {
                            if(stristr($base_link,'.php'))
                            {
                            $url = explode("/",strrev($base_link),2);
                            $base_link = str_replace(strrev($url[0]),"",$base_link);
                            }
                          }
                        return $base_link;
		}

		function get_content($scan_links = false)
		{
			$image_files = preg_split("/\s+/", "jpeg jpg");
			$movie_files = preg_split("/\s+/", "flv mp4");
			$html_files = preg_split("/\s+/", "html htm php");

			$content_array = array ();
			$thumbs_array = array ();

			$page = $this->page;
			$link = $this->link;

			$base_link = $this->_get_base_link ($link, $page);

			$page = preg_replace("/\s+/", " ", $page);
			$page = preg_replace("/<!--[\s\S]*-->/iU", " ", $page);
			$page = preg_replace("/<a/i", "\n<a", $page);
			$type = "";
			//print_r ($page);
			if ($count = preg_match_all("/(<a[^>]+>)([\s\S]+)(?=<\/a|\$|<a)/iU", $page, $data))
			{
				//echo '<pre>';print_r ($data[1]);
				for ($j = 0; $j < $count; $j++)
				{
					$thumb_url = "";
					$content = 0;

					$url = $data[1][$j];
					//echo "in url: {$url}<br>";
					preg_match("/href\s*=\s*[\'\"]?([^\s\>\'\"]+)/i", $url, $_url);
                    if(strstr($url,"channel69cash.com")){preg_match("!\"(.*?)\"!is", $url, $_url);}
					$url = $_url[1];

					$c_url = parse_url($url);
                                        $c_url = $c_url["path"];
                                        //Porn XN Cash
                                        if(stristr($url,'thumbnail.php')){$c_url = $url;}
					$item = $data[2][$j];
                                        
					if (preg_match("/<img/i", $item))
					{
						if (preg_match("/\.(" . join("|", $image_files) . ")\$/i", $c_url) || stristr($c_url,'thumbnail.php'))
						{
							$content = 1;
							$type = "pictures";
						}
						elseif (preg_match("/\.(" . join("|", $movie_files) . ")\$/i", $c_url))
						{
							$content = 1;
							$type = "movies";
						}
						elseif ($scan_links && preg_match("/\.(" . join("|", $html_files) . ")\$/i", $c_url) && !preg_match("/\.gif/i", $item))
						{
								//echo $item . '<br />';
								$url_of_image = "";

								$this->page_inner = "";
						
								$link = $this->_get_full_url($base_link, $url);
								$link_info = parse_url($link);
								$host = $link_info["host"];
						
								@$fp = fsockopen($host, $link_info["port"] ? $link_info["port"] : 80, &$errno, &$errstr);
						
								$header = array ();
						
								$path = substr($link, strlen("http://" . $host));
						
								//print_r ($link);echo '<br />';
						
								if ($fp)
								{
									$request = "GET " . str_replace(" ", "%20", $path) . " HTTP/1.0\r\n";
									$request .= "Host: " . $host . "\r\n";
									$request .= "Referer: http://" . $host . "/\r\n";
									$request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322)\r\n";
									$request .= "\r\n";
									//print_r ($request);echo '<br />';
						
									fwrite($fp, $request);
						
									while (!feof($fp))
									{
										$string = fgets($fp, 1024);
										if (!trim($string)) break;
										$header[] = $string;
									}
									
									$header = join("", $header);

									//print_r ($header);echo '<br />';echo '<br />';
					
									if (preg_match("/http\/\d\.\d\s+200\s/i", $header))
									{
										while (!feof($fp)) $this->page_inner .= fgets($fp, 1024);
										fclose($fp);
										$result = true;
										$this->code_inner = 200;
									}

									$page_inner_base_link = $this->_get_base_link ($link, $page_inner); 
									
									
									$page_inner = $this->page_inner;
									$page_inner = preg_replace("/\s+/", " ", $page_inner);
									$page_inner = preg_replace("/<!--[\s\S]*-->/iU", " ", $page_inner);

									$square = 0;
									$max_index = 0;
									$cur = 0;

									$page_inner = preg_replace("/<a/i", "\n<a", $page_inner);
									preg_match_all("/<img\s*src\s*=\s*[\'\"]?([^\s>\'\"]+)/i", $page_inner, $img_urls);
									for ($k = 0; $k < count($img_urls[1]); $k++)
									{
										$images_array[$cur+$k]["url"] = $this->_get_full_url($page_inner_base_link, $img_urls[1][$cur+$k]);
										$images_array[$cur+$k]["size"] = getimagesize($this->_get_full_url($page_inner_base_link, $img_urls[1][$cur+$k]));
										$images_array[$cur+$k]["type"] = "pictures";
										if ($images_array[$cur+$k]["size"][0] * $images_array[$cur+$k]["size"][1] > $square)
										{
											$square = $images_array[$cur+$k]["size"][0] * $images_array[$cur+$k]["size"][1];
											$max_index = $cur+$k;
										}
									}
									//print_r ($img_urls);
									$url_of_image = $images_array[$max_index]["url"]; 	
									$type = $images_array[$max_index]["type"];
									if ($type == "movies")
										$thumb_url = $url_of_image;
																		
									fclose($fp);
								}

								$content = 1;
								//$type = "pictures";
								//$thumb_url = "";
							//}
						}

						if (!$thumb_url)
						{
							preg_match("/src\s*=\s*[\'\"]?([^\s>\'\"]+)/i", $item, $thumb_url);
							$thumb_url = $thumb_url[1];
						}

					}
					else
					{
						if (preg_match("/\.(" . join("|", $image_files) . ")\$/i", $url))
						{
							$_item = preg_quote(trim($item));
							$_item = preg_replace("/[\\\.]+&\w+;\$/", "([\s\S]*)", $_item);

							if (preg_match("/" . str_replace("/", "\\/", $_item) . "\$/", $url) || preg_match("/" . str_replace("/", "\\/", $_item) . "\$/", rawurldecode($url)))
							{
								$content = 1;
								$type = "pictures";
								$thumb_url = "";
							}
						}
						
					}

					if ($content)
					{
						if ($url_of_image)
							$content_url = $this->_get_full_url($base_link, $url_of_image);
						else
							$content_url = $this->_get_full_url($base_link, $url);						

						$content_array[] = $content_url;
						$types_array[] = $type;

						$thumbs_array[] = $this->_get_full_url($base_link, $thumb_url);
					}
				}
			}
			

			/**

				Scanning gallery for thumb in clip

			*/
                        $page_orig = preg_replace("/\s+/", " ", $this->page);
                        if(!preg_match("/wearehairy/i", $this->page)){$page_orig = preg_replace("/<a/i", "\n<a", $page_orig);}
                        if ($count = preg_match_all("/<script[^>]*?>(.*?)<\/script>/i", $page_orig, $data))
			{
				for ($i = 0; $i < $count; $i++)
				{
					$innerHTML = $data[1][$i];
                                        if (preg_match("/wearehairy/i", $innerHTML))
                                        {
                                            preg_match_all("/plr\((.*?)\)/i", $innerHTML, $matches);
                                            if($matches[1][0]){
                                                foreach(explode("'",$matches[1][0]) as $str){
                                                    if(stristr($str,'.jpg')){
                                                        $type = "movies";
							$content_url = $this->_get_full_url($base_link, urldecode($str));
							$content_array[] = $content_url;
							$types_array[] = $type;
							$thumbs_array[] = $content_url;
                                                    }
                                                }
                                            }
                                        }
					if (preg_match("/SWFObject/i", $innerHTML))
					{
						//echo 'SWFObject is found<br />';
						if ($count_matches = preg_match_all("/addVariable\(([\"\'])(.+?)\\1\,([\"\'])(.+?)\\3\)/i", $innerHTML, $data_matches))
						{
							//echo 'Variables are found<br />';
							for ($k = 0; $k < $count_matches; $k++)
							{
								$var_value = $data_matches[4][$k];
									$types_array[] = "movies";
                                                                if (preg_match("/\.(" . join("|", $image_files) . ")\$/i", $var_value))
								{
									$content_url = $this->_get_full_url($base_link, urldecode($var_value));						
									$thumbs_array[] = $content_url;
								}
								elseif (preg_match("/\.(" . join("|", $movie_files) . ")\$/i", $var_value))
								{
									$content_url = $this->_get_full_url($base_link, urldecode($var_value));
									$content_array[] = $content_url;
								}
								elseif (preg_match("/\.(" . join("|", $html_files) . ")/i", $var_value))
								{
									//echo 'found link!!! - ' . urldecode($var_value) . '<br />';									
								}
							}
						}
					}
				}
				// print_r ($data[1]);
			}

			if ($count_embed = preg_match_all("/<embed([^>]+)>/iU", $page_orig, $data_embed))
			{
				//print_r ($data);
				for ($i = 0; $i < $count_embed; $i++)
				{
						$embed_content = $data_embed[1][$i];
						if (preg_match("/flashvars=([\"\'])(.*?)\\1/i", $embed_content, $flashvars))
						{
							$flashvars_array = explode("&", $flashvars[2]);
							//print_r ($flashvars_array);
							foreach ($flashvars_array as $flashvar)
							{
								$flashvar_value = substr($flashvar, strpos($flashvar, '=') + 1);
								//echo $flashvar_value . '<br />';
								if (preg_match("/\.(" . join("|", $image_files) . ")\$/i", $flashvar_value))
								{
									//echo 'found thumb!!! - ' . urldecode($var_value) . '<br />';
									$type = "movies";
									$content_url = $this->_get_full_url($base_link, urldecode($flashvar_value));						
									$content_array[] = $content_url;
									$thumbs_array[] = $content_url;
								}
							}
						}
					
				}
				// print_r ($data[1]);
			}
			

			$content = array (
				"images" => array (),
				"clips" => array ()
			);
                        //echo '<pre>';
			//print_r ($content_array);exit;

			foreach ($content_array as $key => $entry)
			{
			    if(strstr($entry,' ')){$entry=str_replace(" ","%20",$entry);}
				if ($head = get_headers($entry)) {
					if($head[0] == "HTTP/1.1 404 Not Found")
						continue;
					if ($types_array[$key] == "pictures") $content["images"][] = $entry;
					else $content["clips"][] = $entry;
					$content["thumbs"][] = $thumbs_array[$key];
				} 
			}
			return $content;
		}

		function _get_full_url($base_url, $url)
		{
			if (!$url) return "";

			if (substr($url, 0, 7) == "http://") return $url;

			if (substr($url, 0, 1) == "/")
			{
				$_url = parse_url($base_url);
				return "http://" . $_url["host"] . $url;
			}

			if (substr($url, 0, 3) == "../")
			{
				while (substr($url, 0, 3) == "../")
				{
					$base_url = dirname($base_url);
					$url = substr($url, 3);
				}
				return $base_url . "/" . $url;
			}
			elseif (substr($url, 0, 2) == "./") return $base_url . "/" . substr($url, 2);
			else return $base_url . "/" . $url;
		}
		function download($link, $filename,$fhg)
		{
                $s['useragent']="Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.11) Gecko/2009060215 AdCentriaIM/1.7 Firefox/3.0.11";
		$s['referer']=$fhg['url'];
                //check removable content
		if($fhg['scanstatus'])
		  {
		  for($i=1;$i<=5;$i++)
		   {
		   //echo '<br><br>i-'.$i.'<br><br>';
		   //echo "link: " . $link . "<br>";
		   if(!$file_contents)
		     {
			  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, $link);
			  curl_setopt($ch, CURLOPT_USERAGENT, $s['useragent']);
			  curl_setopt($ch, CURLOPT_REFERER, $s['referer']);
			  curl_setopt($ch, CURLOPT_HEADER,1);
			  //curl_setopt($ch, CURLOPT_NOBODY,1);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			  $out['headers']=curl_exec($ch);
	          $out['info']=curl_getinfo($ch);
	          curl_close($ch);
  		      //echo "Code: " . $out['info']['http_code'] . "<br>";
		      if($out['info']['http_code']==302)
		        {
			    foreach(explode("\n",$out['headers']) as $v){if($v && strstr($v,'Location:') && strstr($v,'http://')){$link=trim(str_replace("Location:","",trim($v)));}}
			    }
			  if($out['info']['http_code']==200)
			    {
				//$file_contents=array_pop(explode("\r\n",$out['headers']));
				foreach(explode("\r\n",$out['headers']) as $v){if($v && strstr($v,'Content-Length:')){$lenght=trim(str_replace("Content-Length:","",trim($v)));}}
				$file_contents=substr($out['headers'], '-'.$lenght);
				break;
				}
			 }
		   }
		  }
		if(!$fhg['scanstatus'])
		  {
		   //
		   echo "link: " . $link . "<br>"; 
		   echo "filename: " . $filename . "<br>";
                    $link_info = parse_url($link); $host = $link_info['host'];
                    try{
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $link);
                        curl_setopt($ch, CURLOPT_USERAGENT, $s['useragent']);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3000);
                        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ".$host,"Accept-Charset: utf-8;q=0.7,*;q=0.7;","Keep-Alive: 300","Connection: keep-alive","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: ru,en-us;q=0.7,en;q=0.3","Accept-Encoding: gzip,deflate"));
						//curl_setopt($ch, CURLOPT_HEADER, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        //curl_setopt($ch, CURLOPT_CONNECTIONTIMEOUT, 0);
                        curl_setopt($ch, CURLOPT_REFERER, $s['referer']);
                        $file_contents = curl_exec($ch);
						/*
						print_r($file_contents);
						echo '<br><br><br><br>'.$host.'<pre>';
						print_r(curl_getinfo($ch));
						exit;
						*/
                        curl_close($ch);
                    } catch (Exception $e) {
                        echo "Exception: " . $e->getMessage();
                        exit;
                    }
		  }
//					echo $file_contents;
					echo strlen($file_contents) . "<br>";
                    $image = imagecreatefromstring($file_contents);
                    return imagejpeg($image, $filename, 100);
		}
	}
?>