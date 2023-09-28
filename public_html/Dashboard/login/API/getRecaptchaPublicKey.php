<?php 
    require_once __DIR__."/../classesAndFunctions/dbAndOtherDetails.php";
    header("Content-Type: application/json");
    $result=array(
        "status"=>"SUCCESS",
        "result"=>$reCaptchaPublicKey
    );
    echo json_encode($result);
    exit;
?>