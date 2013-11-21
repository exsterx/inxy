<?php
set_time_limit(60 * 60 * 2);
ignore_user_abort();
include_once(dirname(__file__) . '/includes/prepare.php');
include_once(dirname(__file__) . '/includes/SQLmanager.php');
# WORK #########################################################################
$limit = $settings->get('threads');
$sql->Query("UPDATE galleries SET status='error',error='bad success' WHERE status='process' AND dateProcess < '".time()."' AND dateProcess!=0");
if($limit){
    $sql->Query("SELECT galleryID FROM galleries WHERE status='process'");
    $limit -= $sql->size_of_result;
    if($limit > 0){
        $sql->Query("SELECT galleryID FROM galleries WHERE status = 'new' ORDER BY dateAdded DESC LIMIT " . $limit);
        foreach($sql->GetAssoc() as $g){
            echo 'parse gallery '.$g['galleryID']."\n";
            passthru('('.PHP.' -f '. PATH_DIR . 'process.php '.$g['galleryID'].' & ) >> /dev/null 2>&1');
        }
    }
}
?>