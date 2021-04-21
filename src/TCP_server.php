#!/xampp/php

<?php

set_time_limit(0);
$my_t = @getdate(@date("U"));
echo "\n\nUsing server\n";
echo "IoT - 2021\n";
print("\nUTime: $my_t[0]\n");
$t = @time();
// IP address of the laptop that is running Apache
$address = "192.168.1.67";  
$port = 25000;      // TCP port connected where the client IoT Arduino ESP01 is connected
echo "Server is waited for messages ...";
echo "\n". @date("d_m_Y")."\n";

do{
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(!$socket){
        echo "It wasn't possible to connect to the socket ... \n";
        break;
    }

    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
    // catch the port
    $result = @socket_bind($socket, 0 , $port);

    if(!$result){
        echo "It wasn't possible to catch the socket ... \n";
        break;
    }

    // Listen for connections
    $result = @socket_listen($socket, 10);
    // or die("Could not set up socket listener\n");
    if(!$result){
        echo "It wasn't possible to listen by the socket ... \n";
        break;
    }

    if(($spawn = @socket_accept($socket)) == false){
        echo "It wasn't possible to accept the socket connection ...\n";
        @socket_close($spawn);
        break;
    }

    echo "\n\nSpawning socket ...\n";
    $input = @socket_read($spawn, 2048);
    // or die ("Could not read input\n");
    echo "Incoming message ...\n";

    if(($input === '') || ($input == false)){
        echo "Socket closed by origin ...\n";
        socket_close($spawn);
        break;
    }

    //$msg_obj = json_decode($input);
    $today_date = @date("D M j G:i:s T Y");
    echo "Today is $today_date\n";
    echo "\nExtracting data ... \n\n";
	$msg_obj = json_decode($input);

    if(($lab = $msg_obj->lab) != NULL){
        echo "Laboratory ".$lab."\n";
    }
    
    if(($temperature = $msg_obj->temperature) != NULL){
        echo "Temperature : ".$temperature." oC\n";
    }

    if(($humidity = $msg_obj->humidity) != NULL){
        echo "Humidity : ".$humidity."%\n";
    }

    if(($motion = $msg_obj->motion) == true){
        echo "Motion : Detected\n";
    }else{
        echo "Motion : Not detected\n";
    }

    if(($alarm = $msg_obj->alarm_running) == true){
        echo "Alarm : ON\n";
    }else{
        echo "Alarm : OFF\n\n";
    }
	
    // Send data
    echo "Saving data in 'security_system' database ...\n";
    $api = "http://localhost/IoT_projects/security_system/src/api.php?";
    $system = "lab";
    $request = $api."system=".$system."&num=".$lab."&t=".$temperature."&h=".$humidity."&m=".$motion."&ar=".$alarm;
    $send = json_decode(file_get_contents($request));

    if($send->status == true){
        echo $send->response."\n";

    }else{
        echo $send->error;

    }

	echo "Sending response ...\n";
	$system = "alarm";
	$request = $api."system=".$system."&num=".$lab;
	$response = file_get_contents($request);    
    
    // echo $response;    
	
    if(($sent = @socket_write($spawn, $response, strlen($response))) === false){
        echo "It couldn't send  ACK! ...";
        // socket_close($spawn);
        break;
    }

    @socket_close($spawn);
	echo "Socket closed\n";

}while($input != "quit");

socket_close($spawn);


?>