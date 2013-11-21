<?php
//ini_set("display_errors","1");
//ini_set("error_reporting", E_ALL);
include_once('/home/httpd/bbw-fat-tube.com/content/html/redtube/includes/prepare.php');
$sql->Query("SET NAMES utf8");
# WORK #########################################################################
if($_POST['submit'] && trim($_POST['niches'])){
    $sql->Query("TRUNCATE TABLE niches");
    foreach(explode("\n",trim($_POST['niches'])) as $n){
        $n  =strtolower(trim($n));
        if(!trim($n)){continue;}
        $sql->Query("SELECT * FROM niches WHERE name = \"".  mysql_escape_string($n)."\"");
        if(!$sql->size_of_result){
            $sql->Query("INSERT INTO niches SET name = \"".  mysql_escape_string($n)."\"");
        }
    }
}
# NICHES #
unset($niches);
$sql->Query("SELECT * FROM niches ORDER BY name");
if($sql->size_of_result){
    foreach($sql->GetAssoc() as $n){
        $niches[] = $n['name'];
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Add/Edit Niches</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body{font:14px arial;}
    </style>
</head>
<body>
    <table align="center" width="400">
        <tr><td align="center">Add/Edit Niches</td></tr>
        <form method="post">
            <tr><td align="center"><textarea name="niches" style="width:400px;height:600px;"><?php echo implode("\n",$niches);?></textarea></td></tr>
            <tr><td align="center"><input type="submit" value="Save" name="submit"></td></tr>
        </form>
    </table>  
</body>
</html>