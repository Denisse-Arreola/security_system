<?php

require_once("mysql/class_user.php");
require_once("mysql/class_laboratory.php");


    // Regarding users
    if (isset($_GET['session_action'])){

        if($_GET['session_action'] == "start_session" && isset($_GET["number"]) && isset($_GET["password"])){
            $number = $_GET["number"];
            $password = $_GET["password"];

            $verify = start_session($number, $password);

            if($verify){
                echo $_SESSION["user"];
            }else{
                error(5);
            }

            
        }else if($_GET['session_action'] == "new_user" && isset($_GET["firstname"]) && isset($_GET["lastname"]) && isset($_GET["number"]) && isset($_GET["password"]) && isset($_GET["key"])){
            $firstname = $_GET["firstname"];
            $lastname = $_GET["lastname"];
            $number = $_GET["number"];
            $password = $_GET["password"];
            $key = $_GET["key"];

            $user = new user($number, $firstname, $lastname, $password, $key);
            $status = json_decode($user->newUser());

            if($status->status == true){
                $verify = start_session($number, $password);

                if($verify){
                    echo $_SESSION["user"];
                }else{
                    error(5);
                }
            }else{
                echo json_encode($status);
            }

        }else if($_GET['session_action'] == "verify"){
            verify_session();

        }else if($_GET['session_action'] == "session_destroy"){
        
            session_start();
            if(empty($_SESSION)){
				    error();

            } else{
                session_destroy();

                echo json_encode(Array(
                        'status' => 'Sesion terminada',
                        'res' => true
                        )
                );
            }

        }else{
            error(4);
        }


    // Regarding to the security system
    } else if (isset($_GET['system'])){

        // Change alarm state
        if($_GET['system'] == "alarm" && isset($_GET['num']) && isset($_GET['on'])){
            
            if ($_GET['on'] != 'true' && $_GET['on'] != 'false'){
                // Invalid value for this parameter    
                error(3);
            }

            $lab = new laboratory($_GET['num']);
            $alarm = $lab->set_alarm_status($_GET['on']);
            echo $alarm;


        // Check the alarm state
        }else if($_GET['system'] == "alarm" && isset($_GET['num'])){

            $lab = new laboratory($_GET['num']);
            $alarm = $lab->get_alarm_status();
            echo $alarm;

        }else if($_GET['system'] == "alarm_motion" && isset($_GET['num'])){

            $lab = new laboratory($_GET['num']);
            $alarm = $lab->get_last_alarm();
            echo $alarm;
        
        }else if($_GET['system'] == "lab" && isset($_GET['num']) && isset($_GET['name'])){

            // queda pendiente, es para registrar laboratorio
			echo "llegue aquí";

       
        }else if($_GET['system'] == "data" && isset($_GET['num']) && isset($_GET['d'])){
            
            $lab = new laboratory($_GET['num']);

            if($_GET['d'] == "today"){
                $data = $lab->get_lab_results(0);
                echo $data;

            }else if($_GET['d'] == "yesterday"){
                $data = $lab->get_lab_results(1);
                echo $data;

            }else if($_GET['d'] == "week"){
                $data = $lab->get_lab_results(2);
                echo $data;

            }else{
                error(4);             
            }

         // Extraer todos los datos
        }else if($_GET['system'] == "data" && isset($_GET['num'])){
            $lab = new laboratory($_GET['num']);
            $data = $lab->get_lab_results(0);
            echo $data;

        }else if($_GET['system'] == "lab" && isset($_GET['num']) && isset($_GET['t']) && isset($_GET['h']) && isset($_GET['m']) && isset($_GET['ar'])){

            $lab = new laboratory($_GET['num'], $_GET['t'], $_GET['h'], $_GET['m'], $_GET['ar']);
            $send = $lab->set_lab_results();
            echo $send;

        }else if($_GET['system'] == "last_data" && isset($_GET['num'])){
            $lab = new laboratory($_GET['num']);
            $temperature = json_decode($lab->get_last_result(0));
            $humidity = json_decode($lab->get_last_result(1));
            $motion = json_decode($lab->get_last_result(2));

            $array = json_encode(array(
                "id"=> $temperature->id,
                "laboratory"=> $temperature->laboratory,
                "temperature"=>$temperature->data,
                "humidity"=>$humidity->data,
                "time_M"=>$motion->time,
                "status"=>true
                
            ));

            echo $array;
            
        }else{
            // Missing parameter or erroneous values
            error(4);
        }

    }else{

        // Return the error
        error(1);
    }



    // Return an error
    function error($n){

        $error = array( 
                    // If any parameter wasn't found
                    1 => json_encode( array (
                                        "error" => "No parameters were set",
                                        "status" => false
                                    )),
                    // If there isn't any user using the program
                    2 => json_encode( array (
                                        "error" => "No session active",
                                        "status" => false
                                    )),

                    3 => json_encode( array (
                                        "error" => "Invalid value for parameter 'ON', it is supposed to be TRUE or FALSE",
                                        "status" => false
                                    )),
                    4 => json_encode( array (
                                        "error" => "Parameters missing or erroneous values for parameters",
                                        "status" => false
                                    )),
                    5 => json_encode( array (
                                        "error" => "User not found",
                                        "status" => false
                                    ))
                    );

        // Show corresponding JSON error
        echo $error[$n];  
    }

    // Start a new session
    function start_session($number, $password){
        $user = new user($number, $password);
        $user_data = $user->userAcess();
		$status = json_decode($user_data);

        if($status == true ){

            session_start();
            $_SESSION["user"] = $user_data;
            return true;

        }else{

            return false;
        }
    }

    // Verify active users
    function verify_session() {

        session_start();
        if(empty($_SESSION)){
            error(2);

        }else{
            echo $_SESSION["user"];          
        }

    }


?>