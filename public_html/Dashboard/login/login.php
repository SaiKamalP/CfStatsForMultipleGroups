<?php
    require_once __DIR__."/classesAndFunctions/jwsAuth/JWS.php";
    require_once __DIR__."/classesAndFunctions/dbAndOtherDetails.php";
    require_once __DIR__."/classesAndFunctions/verifyReCaptcha.php";
    
    if(!verifyReCaptcha()){
        die("\nFAILED ReCaptcha");
    }
    
    function isAValidString($string){
        $validCharacters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#_.-";
        for($i=0;$i<strlen($string);$i++){
            if(strpos($validCharacters,$string[$i])===false){
                return false;
            }
        }
        return true;

    }
    function isAValidName($string,$maxlen=30){
        if(strlen($string)<=0 || strlen($string)>$maxlen){
            return false;
        }
        return isAValidString($string);
    }



    $clientUsername=$_POST['login-username'];
    $clientPassword=$_POST['login-password'];
    if(!isAValidName($clientUsername) || !isAValidName($clientPassword,100)){
        die("bad username or password");
    }
    else{
        $clientPassword=hash('sha256',$GIBRISH1.$clientPassword.$GIBRISH2);
        
        
        $conn=mysqli_connect($host,$username,$password,$dbName);
        if($conn){
            $userVerifiactionQuery="SELECT `cf_handle`, `user_type` FROM `".$cfUsersTableName."` WHERE `cf_handle` = ? AND `password` = ?";

            $userVerifiactionStmt=mysqli_prepare($conn,$userVerifiactionQuery);
            if($userVerifiactionStmt){
                $userVerifiactionStmt->bind_param("ss",$clientUsername,$clientPassword);
                if($userVerifiactionStmt->execute()){
                    $result=$userVerifiactionStmt->get_result();
                    $row1=$result->fetch_assoc();
                    if($result->num_rows>0){
                        $headers = array(
                            'alg' => 'HS256',
                            'typ' => 'JWT'
                        );
                        $payload = array(
                            'sub' => $clientUsername,
                            'iat' => time()+604800 //7-days valid token
                        );
                        
                        $jws = new JWS();
                        $jwsTokenGenerated=$jws->encode($headers, $payload, $key);
                        
                        setcookie('jwtToken',$jwsTokenGenerated,time()+604800,'','',false,true);
                        echo "SUCCESS ".$row1['user_type'];
                        header('Location: ../');
                        exit;
                    }
                    else{
                        header("location: .");
                        die("Username or Password is incorect.");
                    }
                }
                else{
                    header("location: .");
                    die("Failed to execute the statement.");
                }
            }
            else{
                header("location: .");
                die("Failed to make prepare statement.");
            }
        }
        else{
            header("location: .");
            die("Failed to connect to database.");
        }
        
    }
    

   
?>