<?php
    require_once __DIR__."/../classesAndFunctions/jwsTokenVerification.php";
    require_once __DIR__."/../classesAndFunctions/getUserType.php";
    header('Content-Type: application/json');

    function returnFailedStatus()
    {
        $responce = array(
            'status' => 'FAILED'
        );
        echo json_encode($responce);
        exit;
    }
    

    $jwtResult =getJWTAuthResult();
    if ($jwtResult==null) { returnFailedStatus();}

    $userTypeFetch=getUserType($jwtResult['payload']['sub']);
    if($userTypeFetch['status']=='FAILED'){ returnFailedStatus();}
    $userType=$userTypeFetch['result'];
    $responce = array(
        'status' => 'SUCCESS',
        'user_type' => $userType
    );
    echo json_encode($responce);
    exit;
    
?>
