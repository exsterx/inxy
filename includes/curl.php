<?php

/**

* Copyright (c) 2008, alexandr.shurigin@gmail.com "phpdude"

*

* ����������� ��������� ��������������� � ������������� ��� � ���� ��������� ����, ��� � �

* �������� �����, � ����������� ��� ���, ��� ���������� ��������� �������:

*

*     * ��� ��������� ��������������� ��������� ���� ������ ���������� ��������� ����

*       ����������� �� ��������� �����, ���� ������ ������� � ����������� ����� �� ��������.

*     * ��� ��������� ��������������� ��������� ���� ������ ����������� ��������� ����

*       ���������� �� ��������� �����, ���� ������ ������� � ����������� ����� �� �������� �

*       ������������ �/��� � ������ ����������, ������������ ��� ���������������

*     * �� �������� "������ 2315", �� ����� �� ����������� �� ����� ���� ������������ �

*       �������� ��������� ��� ����������� ���������, ���������� �� ���� �� ���

*       ���������������� ����������� ����������.

*

* ��� ��������� ������������� ����������� ��������� ���� �/��� ������� ���������

* "��� ��� ����" ��� ������-���� ���� ��������, ���������� ���� ��� ���������������,

* �������, �� �� ������������� ���, ��������������� �������� ������������ �������� �

* ����������� ��� ���������� ����. �� � ���� ������, ���� �� ��������� ���������������

* �������, ��� �� ����������� � ������ �����, �� ���� �������� ��������� ���� � �� ����

* ������ ����, ������� ����� �������� �/��� �������� �������������� ���������, ��� ����

* ������� ����, �� ��Ѩ� ���������������, ������� ����� �����, ���������,

* ����������� ��� ������������� ������, ���������� ������������� ��� �������������

* ������������� ��������� (�������, �� �� ������������� ������� ������, ��� �������,

* �������� �������������, ��� �������� ������������ ��-�� ��� ��� ������� ���, ��� �������

* ��������� �������� ��������� � ������� �����������), ���� ���� ����� �������� ���

* ������ ���� ���� �������� � ����������� ����� �������.

*

* ������ 0.1beta

*/



class Curl

{

    private $_ch;

    private $_opts;

    private $_content;

    private $_headers;

    private $_options;

    private $_cookies;

    private $_tmpfile;



    public $curlinfo;



    public function __construct()

    {

        if (!extension_loaded('curl')) {

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

                dl('curl.dll');

            } else {

                dl('curl.so');

            }

        }

        if (!extension_loaded('curl'))

        {

            die("<b>Unable to load extension Curl! Application Exit</b><br/>");

        }

        $this->_ch = false;

        $this->_opts = unserialize('a:96:{s:11:"autoreferer";s:19:"CURLOPT_AUTOREFERER";s:14:"binarytransfer";s:22:"CURLOPT_BINARYTRANSFER";s:10:"buffersize";s:18:"CURLOPT_BUFFERSIZE";s:6:"cainfo";s:14:"CURLOPT_CAINFO";s:6:"capath";s:14:"CURLOPT_CAPATH";s:11:"closepolicy";s:19:"CURLOPT_CLOSEPOLICY";s:14:"connecttimeout";s:22:"CURLOPT_CONNECTTIMEOUT";s:6:"cookie";s:14:"CURLOPT_COOKIE";s:10:"cookiefile";s:18:"CURLOPT_COOKIEFILE";s:9:"cookiejar";s:17:"CURLOPT_COOKIEJAR";s:13:"cookiesession";s:21:"CURLOPT_COOKIESESSION";s:4:"crlf";s:12:"CURLOPT_CRLF";s:13:"customrequest";s:21:"CURLOPT_CUSTOMREQUEST";s:17:"dns_cache_timeout";s:25:"CURLOPT_DNS_CACHE_TIMEOUT";s:20:"dns_use_global_cache";s:28:"CURLOPT_DNS_USE_GLOBAL_CACHE";s:9:"egdsocket";s:17:"CURLOPT_EGDSOCKET";s:8:"encoding";s:16:"CURLOPT_ENCODING";s:11:"failonerror";s:19:"CURLOPT_FAILONERROR";s:4:"file";s:12:"CURLOPT_FILE";s:8:"filetime";s:16:"CURLOPT_FILETIME";s:14:"followlocation";s:22:"CURLOPT_FOLLOWLOCATION";s:12:"forbid_reuse";s:20:"CURLOPT_FORBID_REUSE";s:13:"fresh_connect";s:21:"CURLOPT_FRESH_CONNECT";s:9:"ftpappend";s:17:"CURLOPT_FTPAPPEND";s:8:"ftpascii";s:16:"CURLOPT_FTPASCII";s:11:"ftplistonly";s:19:"CURLOPT_FTPLISTONLY";s:7:"ftpport";s:15:"CURLOPT_FTPPORT";s:10:"ftpsslauth";s:18:"CURLOPT_FTPSSLAUTH";s:7:"ftp_ssl";s:15:"CURLOPT_FTP_SSL";s:12:"ftp_use_eprt";s:20:"CURLOPT_FTP_USE_EPRT";s:12:"ftp_use_epsv";s:20:"CURLOPT_FTP_USE_EPSV";s:6:"header";s:14:"CURLOPT_HEADER";s:14:"headerfunction";s:22:"CURLOPT_HEADERFUNCTION";s:14:"http200aliases";s:22:"CURLOPT_HTTP200ALIASES";s:8:"httpauth";s:16:"CURLOPT_HTTPAUTH";s:7:"httpget";s:15:"CURLOPT_HTTPGET";s:10:"httpheader";s:18:"CURLOPT_HTTPHEADER";s:15:"httpproxytunnel";s:23:"CURLOPT_HTTPPROXYTUNNEL";s:12:"http_version";s:20:"CURLOPT_HTTP_VERSION";s:6:"infile";s:14:"CURLOPT_INFILE";s:10:"infilesize";s:18:"CURLOPT_INFILESIZE";s:9:"interface";s:17:"CURLOPT_INTERFACE";s:9:"krb4level";s:17:"CURLOPT_KRB4LEVEL";s:15:"low_speed_limit";s:23:"CURLOPT_LOW_SPEED_LIMIT";s:14:"low_speed_time";s:22:"CURLOPT_LOW_SPEED_TIME";s:11:"maxconnects";s:19:"CURLOPT_MAXCONNECTS";s:9:"maxredirs";s:17:"CURLOPT_MAXREDIRS";s:4:"mute";s:12:"CURLOPT_MUTE";s:5:"netrc";s:13:"CURLOPT_NETRC";s:6:"nobody";s:14:"CURLOPT_NOBODY";s:10:"noprogress";s:18:"CURLOPT_NOPROGRESS";s:8:"nosignal";s:16:"CURLOPT_NOSIGNAL";s:14:"passwdfunction";s:22:"CURLOPT_PASSWDFUNCTION";s:4:"port";s:12:"CURLOPT_PORT";s:4:"post";s:12:"CURLOPT_POST";s:10:"postfields";s:18:"CURLOPT_POSTFIELDS";s:9:"postquote";s:17:"CURLOPT_POSTQUOTE";s:5:"proxy";s:13:"CURLOPT_PROXY";s:9:"proxyauth";s:17:"CURLOPT_PROXYAUTH";s:9:"proxyport";s:17:"CURLOPT_PROXYPORT";s:9:"proxytype";s:17:"CURLOPT_PROXYTYPE";s:12:"proxyuserpwd";s:20:"CURLOPT_PROXYUSERPWD";s:3:"put";s:11:"CURLOPT_PUT";s:5:"quote";s:13:"CURLOPT_QUOTE";s:11:"random_file";s:19:"CURLOPT_RANDOM_FILE";s:5:"range";s:13:"CURLOPT_RANGE";s:8:"readdata";s:16:"CURLOPT_READDATA";s:12:"readfunction";s:20:"CURLOPT_READFUNCTION";s:7:"referer";s:15:"CURLOPT_REFERER";s:11:"resume_from";s:19:"CURLOPT_RESUME_FROM";s:14:"returntransfer";s:22:"CURLOPT_RETURNTRANSFER";s:7:"sslcert";s:15:"CURLOPT_SSLCERT";s:13:"sslcertpasswd";s:21:"CURLOPT_SSLCERTPASSWD";s:11:"sslcerttype";s:19:"CURLOPT_SSLCERTTYPE";s:9:"sslengine";s:17:"CURLOPT_SSLENGINE";s:17:"sslengine_default";s:25:"CURLOPT_SSLENGINE_DEFAULT";s:6:"sslkey";s:14:"CURLOPT_SSLKEY";s:12:"sslkeypasswd";s:20:"CURLOPT_SSLKEYPASSWD";s:10:"sslkeytype";s:18:"CURLOPT_SSLKEYTYPE";s:10:"sslversion";s:18:"CURLOPT_SSLVERSION";s:15:"ssl_cipher_list";s:23:"CURLOPT_SSL_CIPHER_LIST";s:14:"ssl_verifyhost";s:22:"CURLOPT_SSL_VERIFYHOST";s:14:"ssl_verifypeer";s:22:"CURLOPT_SSL_VERIFYPEER";s:6:"stderr";s:14:"CURLOPT_STDERR";s:13:"timecondition";s:21:"CURLOPT_TIMECONDITION";s:7:"timeout";s:15:"CURLOPT_TIMEOUT";s:9:"timevalue";s:17:"CURLOPT_TIMEVALUE";s:12:"transfertext";s:20:"CURLOPT_TRANSFERTEXT";s:17:"unrestricted_auth";s:25:"CURLOPT_UNRESTRICTED_AUTH";s:6:"upload";s:14:"CURLOPT_UPLOAD";s:3:"url";s:11:"CURLOPT_URL";s:9:"useragent";s:17:"CURLOPT_USERAGENT";s:7:"userpwd";s:15:"CURLOPT_USERPWD";s:7:"verbose";s:15:"CURLOPT_VERBOSE";s:13:"writefunction";s:21:"CURLOPT_WRITEFUNCTION";s:11:"writeheader";s:19:"CURLOPT_WRITEHEADER";}');

    }



    public function init($url = false,$options=array())

    {

        $this->_ch = curl_init();



        if($url)

        {

            $options['url'] = $url;

            $options['referer'] = dirname($url)."/";

        }



        $this->_headers = array();

        $this->_options = array();

        return $this->options($options);

    }





    public function serverfriendly()

    {

        $options = array();

        $options['ssl_verifyhost'] = 0;

        $options['ssl_verifypeer'] = 0;

        $options['useragent'] = "phpdude Curl v0.1b";

        $options['referer'] = dirname($options['url']);

        $options['httpheader'] = array("Accept-Charset: windows-1251;q=0.7,*;q=0.7;","Keep-Alive: 300","Connection: keep-alive","Accept: */*");



        return $this->options($options);

    }



    public function setopt($option,$value)

    {

        $option = strtolower($option);



        if(!array_key_exists($option,$this->_opts))

        {

            trigger_error("Option: <b>$option</b> Not Found!", E_USER_ERROR);

        }



        $this->_options[$option] = $value;

        if($option=="cookie" || $option=="post")

        {

            return $this;

        }

        if($option=="httpheader")

        {

            $this->_options['httpheader'] = array_unique(@array_merge((array)$this->_options['httpheader'],(array)$value));


        }



        curl_setopt($this->_ch,constant($this->_opts[$option]),$value);



        return $this;

    }



    public function options($options)

    {

        if(!is_array($options) || !$options)

        {

            return false;

        }

        foreach($options as $key=>$val)

        {

            $this->setopt($key,$val);

        }

        return $this;

    }



    public function exec($return = true,$newcookiesession=false)

    {

        $this->mr_before_exec($return,$newcookiesession);

        return $this->mr_after_exec(curl_exec($this->_ch));

    }



    public function mr_before_exec($return = true,$newcookiesession=false)

    {

        $this->_tmpfile = tmpfile();



        curl_setopt($this->_ch, CURLOPT_WRITEHEADER,$this->_tmpfile);

        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER,$return ? 1 : 0);

        curl_setopt($this->_ch, CURLOPT_HEADER, 0);

        curl_setopt($this->_ch, CURLOPT_CRLF, 1);



        if(@$this->_options['cookie'] || $sesscookies = $this->getCookies($this->_options['url']))

        {

            $cookies = (array)$this->_options['cookie'];

            if($newcookiesession)

            {

                $this->_cookies = array();

            }

            else

            {

                $cookies = array_merge($cookies,$sesscookies);

            }

            curl_setopt($this->_ch,CURLOPT_COOKIE, http_build_query($cookies,"","; "));

        }



        if(@$this->_options['post'])

        {

            $postfields = is_array($this->_options['post']) ? http_build_query($this->_options['post'],"","&") : $this->_options['post'];

            curl_setopt($this->_ch,CURLOPT_POSTFIELDS, $postfields);

            curl_setopt($this->_ch,CURLOPT_POST, 1);

        }

        return $this;

    }



    public function mr_after_exec($result)

    {

        if(curl_errno($this->_ch))

        {

            throw new Exception("CURL ERROR: ".curl_error($this->_ch),curl_errno($this->_ch));

        }

            

        $info = curl_getinfo($this->_ch);

        $this->curlinfo = new stdClass();

        foreach($info as $f=>$v)

        {

            $this->curlinfo->$f = $v;

        }



        curl_close($this->_ch);

        $this->_content = $result;



        $this->_header_parser();



        $charset = "";

        if($contenttype = $this->getHeader("content-type"))

        {

            $contenttype = is_array($contenttype) ? $contenttype[0] : $contenttype;

            if(preg_match('#text/html\s*;\s*charset\s*=\s*([^\s]+)#i',$contenttype,$charset))

            {

                $charset = $charset[1];

            }

        }

        if(preg_match("#<meta[^>]+Content.Type[^>]+>#ism",$this->_content,$contenttype))

        {

            if(preg_match('#\s*;\s*charset\s*=\s*(["\'a-z0-9\\-]+)#i',$contenttype[0],$chset))

            {

                $charset = trim($chset[1],"\"'");

            }

        }

        if($charset && strtolower($charset)!="utf-8")

        {

            //$this->_content = iconv($charset,"UTF-8",$this->_content);

        }



        return $this->_content;

    }



    public function getOptionsList()

    {

        echo "<pre>";

        print_r($this->_opts);

        echo "</pre>";

    }



    public function getContent()

    {

        return $this->_content;

    }



    public function getOptions()

    {

        return $this->_options;

    }



    public function getOption($name)

    {

        return @$this->_options[$name];

    }



    public function getHeaders()

    {

        return $this->_headers;

    }



    public function getHeader($name)

    {

        return @$this->_headers[$this->_normalize($name)];

    }



    public function addCookies($cookies)

    {

        $this->_cookies = @array_merge((array)$this->_cookies,$cookies);

        return $this;

    }



    public function addCookie($name, $data)

    {

        $this->_cookies[$name] = $data;

        return $this;

    }



    public function getCookies($url="",$name=false,$valueonly = true)

    {

        if(!$url)

        {

            $cookies = $name ? array($this->cookie($name,true)) : $this->_cookies;

            if($valueonly)

            {

                foreach($cookies as $k=>$cookie)

                {

                    $cookies[$k] = $valueonly ? $cookie['value'] : $cookie;

                }

            }

            return $cookies;

        }



        $domain = ltrim(parse_url($url,PHP_URL_HOST),".");

        $path = parse_url($url,PHP_URL_PATH);



        $cookies = $name ? array($name => $this->cookie($name)) : (array)$this->_cookies;



        $return = array();

        foreach($cookies as $name=>$cookie)

        {

            if(!@$cookie['path'] || !$cookie['domain'] || (strpos($path,$cookie['path'])!==false && strpos($domain,ltrim($cookie['domain'],"."))!==false))

            {

                $return[$name] = $valueonly ? $cookie['value'] : $cookie;

            }

        }



        return $return;

    }



    public function getChannel()

    {

        return $this->_ch;

    }



    public function setChannel($ch)

    {

        $this->_ch = $ch;

    }



    public function cookie($name,$allinfo = false)

    {

        $cookie = @$this->_cookies[$name];

        if($allinfo)

        {

            return $cookie;

        }

        else

        {

            return @$cookie['value'];

        }

    }



    private function strip($content)

    {

        return preg_replace("#\\s+#"," ",$content);

    }



    private function _header_parser()

    {

        $tmpfile = $this->_tmpfile;



        fseek($tmpfile,0);

        fgets($tmpfile);

        while(!feof($tmpfile) && is_resource($tmpfile))

        {

            $line = trim(fgets($tmpfile));

            if(!$line)

            {

                continue;

            }



            $header = $this->_normalize(substr($line,0,strpos($line,":")));

            $val = trim(substr($line,strpos($line,":")+1));

            if(!@$this->_headers[$header])

            {

                $this->_headers[$header] = $val;

            }

            else

            {

                if(!is_array($this->_headers[$header]))

                {

                    $this->_headers[$header] = array($this->_headers[$header]);

                }

                $this->_headers[$header][] = $val;

            }

        }



        $this->_cookies_parser();

    }



    private function _cookies_parser()

    {

        $cookies = $this->getHeader("Set-Cookie");

        if(!$cookies)

        {

            return;

        }

        if(!is_array($cookies))

        {

            $cookies = array($cookies);

        }

        foreach($cookies as $cookie)

        {

            $cookiename = "";

            $cookieinfo = array();

            $data = explode(";",$cookie);

            foreach($data as $part)

            {

                $parts = explode("=",$part);

                if(!$cookiename)

                {

                    $cookiename = $parts[0];

                    $cookieinfo = array("value"=>urldecode($parts[1]));

                }

                else

                {

                    $cookieinfo[$this->_normalize($parts[0])] = $parts[1];

                }



            }

            $this->_cookies[$cookiename] = $cookieinfo;

        }

    }



    private function _normalize($str)

    {

        return strtolower(trim($str));

    }

}
?>