<?php 
    require_once __DIR__."/dbAndOtherDetails.php";
    require_once __DIR__."/verifyReCaptcha.php";

    if(!verifyReCaptcha()){
        die("\nFAILED ReCaptcha");
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
    $ClientCf_handle=$_POST['signup-cf_handle'];
    $ClientEmail=$_POST['signup-email'];
    $ClientPassword=$_POST['signup-password'];

    if((!isAValidName($clientName)) || (!isAValidName($ClientCf_handle)) || (!isAValidName($ClientEmail,80)) || (!isAValidName($ClientPassword,100))){
        die("Invalid credentials");
    }

    $ch=curl_init("https://codeforces.com/api/user.info?handles=".$ClientCf_handle);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $responce=curl_exec($ch);
    $responce=json_decode($responce,true);
    if(!($responce['status']=="OK")){
        die("Invalid credentials");
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
                die("Handle already Registered, please login");
            }
            else{
                $newUserInsertQuery="INSERT INTO `".$cfUsersTableName."`(`id`, `name`, `email`, `cf_handle`, `password`, `in_group`, `user_type`) VALUES (NULL,?,?,?,?,'0','NORMAL')";
                $insertStmt=mysqli_prepare($conn,$newUserInsertQuery);
                if($insertStmt){
                    $insertStmt->bind_param("ssss",$clientName,$ClientEmail,$ClientCf_handle,$ClientPassword);
                    if($insertStmt->execute()){
                        die("DONE INSERTING");
                    }
                    else{
                        die("FAILED TO INSERT");
                    }
                }
                else{
                    die("Problem in server side, failed to execute statement.");

                }
            }
        }
        else{
            die("Problem in server side, failed to execute statement.");
        }
    }
    else{
        die("Problem in server side, failed to prepare statement.");
    }



?>