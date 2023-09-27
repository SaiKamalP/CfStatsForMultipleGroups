<?php 
    error_reporting(E_ALL);

    // Display errors on the fly
    ini_set('display_errors', '1');
    require_once __DIR__."/classesAndFunctions/dbAndOtherDetails.php";
    require_once __DIR__."/classesAndFunctions/verifyReCaptcha.php";
    function failedAttemptGetBackToLoginPage(){
        header("Location: .?m=0");
        exit;
    }
    function getBackToLoginPage($m){
        header("Location: .?m=".$m);
        exit;
    }
    if(!verifyReCaptcha()){
        failedAttemptGetBackToLoginPage();
    }

    function isAValidString($string){
        $validCharacters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#_.- ";
        for($i=0;$i<strlen($string);$i++){
            if(strpos($validCharacters,$string[$i])===false){
                return false;
            }
        }
        return true;

    }
    function isAValidName($string,$maxlen=30){
        if(strlen($string)<=0 || strlen($string)>=$maxlen){
            return false;
        }
        return isAValidString($string);
    }

    $clientName=$_POST['signup-name'];
    $ClientCf_handle=strtolower($_POST['signup-cf_handle']);
    $ClientEmail=$_POST['signup-email'];
    $ClientPassword=$_POST['signup-password'];

    if((!isAValidName($clientName)) || (!isAValidName($ClientCf_handle)) || (!isAValidName($ClientEmail,80)) || (!isAValidName($ClientPassword,100))){
        failedAttemptGetBackToLoginPage();
    }

    $ch=curl_init("https://codeforces.com/api/user.info?handles=".$ClientCf_handle);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $responce=curl_exec($ch);
    $responce=json_decode($responce,true);
    if(!($responce['status']=="OK")){
        failedAttemptGetBackToLoginPage();
    }
    $ClientPassword=hash('sha256',$GIBRISH1.$ClientPassword.$GIBRISH2);

    $conn=mysqli_connect($host,$username,$password,$dbName);

    $checkHandleInDBQuery="SELECT `cf_handle` FROM `".$cfUsersTableName."` WHERE `cf_handle`=?";
    $stmt=mysqli_prepare($conn,$checkHandleInDBQuery);
    if($stmt){
        $stmt->bind_param("s",$ClientCf_handle);

        if($stmt->execute()){
            $result=$stmt->get_result();
            if($result->num_rows >0){
                getBackToLoginPage("2");
            }
            else{
                $newUserInsertQuery="INSERT INTO `".$cfUsersTableName."`(`id`, `name`, `email`, `cf_handle`, `password`, `in_group`, `user_type`) VALUES (NULL,?,?,?,?,'0','NORMAL')";
                $insertStmt=mysqli_prepare($conn,$newUserInsertQuery);
                if($insertStmt){
                    $insertStmt->bind_param("ssss",$clientName,$ClientEmail,$ClientCf_handle,$ClientPassword);
                    if($insertStmt->execute()){
                        getBackToLoginPage("1");
                        exit;
                    }
                    else{
                        failedAttemptGetBackToLoginPage();
                    }
                }
                else{
                    failedAttemptGetBackToLoginPage();

                }
            }
        }
        else{
            failedAttemptGetBackToLoginPage();
        }
    }
    else{
        failedAttemptGetBackToLoginPage();
    }



?>