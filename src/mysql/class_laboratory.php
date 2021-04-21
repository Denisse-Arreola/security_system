<?php

require_once("connection.php");

class laboratory{

    private $id;
    private $name;
    private $alarm;
    private $alarm_status;
    private $temperature;
    private $humidity;
    private $motion;
    private $alarm_run;
    private $date_time;

    public function __construct(){
        $args = func_get_args();

        if (func_num_args() == 0){
            $this->id = 0;
            $this->name = "";
            $this->alarm = 0;
            $this->alarm_status = false;
            $this->temperature = 0;
            $this->humidity = 0;
            $this->motion = false;
            $this->alarm_run = false;
            $this->date_time = "";
        }

        // Constructor for searching by id
        if(func_num_args() == 1){
            $sql = "select * from vw_laboratory where id = ?;";
            $conn = mysqlConnection::getConnection();
            $command = $conn->prepare($sql);
            $command->bind_param('i', $args[0]);

            $command->bind_result(
                $id_,
                $name_,
                $alarm_,
                $alarm_status_,
                $temperature_,
                $humidity_,
                $motion_,
                $alarm_run_,
                $date_time_
            );

            $command->execute();

            if ($command->fetch()){
                $this->id = $id_;
                $this->name = $name_;
                $this->alarm = $alarm_;
                $this->alarm_status = $alarm_status_;
                $this->temperature = $temperature_;
                $this->humidity = $humidity_;
                $this->motion = $motion_;
                $this->alarm_run = $alarm_run_;
                $this->date_time = $date_time_;
                
            }

            mysqli_stmt_close($command);
            $conn->close();
        }

        // Registrar laboratorio
        if(func_num_args() == 2){
            $this->id = $args[0];
            $this->name = $args[0];
            $this->alarm = 0;
            $this->alarm_status = false;
            $this->temperature = 0;
            $this->humidity = 0;
            $this->motion = false;
            $this->alarm_run = false;
            $this->date_time = "";
            
        }

        // Para mandar los datos desde el Arduino
        if (func_num_args() == 5){
            $this->id = $args[0];
            $this->name = "";
            $this->alarm = 0;
            $this->alarm_status = false;
            $this->temperature = $args[1];
            $this->humidity = $args[2];
            $this->motion = $args[3];
            $this->alarm_run = $args[4];
            $this->date_time = "";
                
        }

    }

    public function set_alarm_status($status){
        $conn = mysqlConnection::getConnection();
        
        //EXAMPLE
        //                 lab / alarm status
        //call sp_turn_alarm(1, false);
        $sql = "call sp_turn_alarm(".$this->id.", ".$status.");";
        $command = $conn->prepare($sql);
		
		$command->execute();

        if($command->error !=""){
            return json_encode(array("error"=>$command->error, "status"=>false));

        }else{
            return json_encode(array("response"=>"The alarm has changed", "status"=>true));

        }        
		

        mysqli_stmt_close($command);
        $conn->close();
    }

    public function set_lab_results(){
        $conn = mysqlConnection::getConnection();

        //EXAMPLE
        //                  temperature / humidity / motion / alarm running / lab 
        //call sp_insert_status( 12.3,       93,     true,       true,       1);
        $sql = "call sp_insert_status(?,?,?,?,?);";
        $command = $conn->prepare($sql);
        $command->bind_param('ddiii',
                            $this->temperature,
                            $this->humidity,
                            $this->motion,
                            $this->alarm_run,
                            $this->id);
		
		$command->execute();

        if($command->error !=""){
            return json_encode(array("error"=>$command->error, "status"=>false));

        }else{
            return json_encode(array("response"=>"Data saved", "status"=>true));

        }        
		

        mysqli_stmt_close($command);
        $conn->close();
    }


    public function get_alarm_status(){
        return json_encode( array("alarm"=>$this->alarm_status, "status"=>true));

    }

    public function get_last_alarm(){
        $sql = 'select * from vw_laboratory_alarm_motion where id = ? and motion = true and alarm_running = true order by counter desc limit 1;';
        $conn = mysqlConnection::getConnection();
        $command = $conn->prepare($sql);
        $command->bind_param('i', $this->id);

        $command->bind_result(
                $counter,
                $id_,
                $name_,
                $motion_,
                $alarm_run_,
                $date_,
                $time_
         );

        $command->execute();


        if($command->fetch()){
            $array = json_encode(array(
                "id"=>$id_,
                "laboratory"=>$name_,
                "motion"=>$motion_,
                "alarm"=>$alarm_run_,
                "date"=>$date_,
                "time"=>$time_,
                "status"=>true
            ));

            return $array;

        }else{
            return json_encode(array("error"=>"Without results", "status"=>false));
            die;
        }

        mysqli_stmt_close($command);
        $conn->close(); 
    }

    public function get_lab_results($n){
        $query = array(
            0 => 'select * from vw_laboratory_data_min where id = ? and date(date_time) = current_date();',
            // today results
            1 => 'select * from vw_laboratory_data_min where id = ? and day(date_time) = day(current_date()) - 1;',
            // yesterday result
            2 => 'select * from vw_laboratory_data_week where id = ? order by date_time;'
            // this week result
        );
        $sql = $query[$n];
        $conn = mysqlConnection::getConnection();
        $command = $conn->prepare($sql);
        $command->bind_param('i', $this->id);

        $command->bind_result(
                $id_,
                $name_,
                $temperature_,
                $humidity_,
                $motion_,
                $alarm_run_,
                $date_time_,
				$date_,
                $time_
         );

        $command->execute();

		$data = array();
        $lab_no = 0;
        $counter = 1;

        while($command->fetch()){
			$lab_no = $id_;
			$lab_name = $name_;
			$array = array(
				"counter"=>$counter,
				"temperature"=>round($temperature_, 2),
				"humidity"=>round($humidity_),
				"motion"=>$motion_,
				"alarm"=>$alarm_run_,
				"date"=>$date_,
				"time"=>$time_
			);
            $counter = $counter + 1;
			array_push($data, $array);
        }

        if ($lab_no != 0){
            $lab = json_encode( array(
									"lab"=>$lab_no,
									"name"=>$lab_name,
									"data"=>$data,
                                    "status"=>true
								)
            );
		    return $lab;
        }else{
            return json_encode(array("error"=>"Without results", "status"=>false));
            die;
        }
	    

        mysqli_stmt_close($command);
        $conn->close(); 
    }

    public function get_last_result($n){
        $query = array(
            0 => 'select id, laboratory, temperature, date_format(date_time, "%H:%i") as time from vw_laboratory where id = ?;',
            // today results
            1 => 'select id, laboratory, humidity, date_format(date_time, "%H:%i") as time from vw_laboratory where id = ?;',
            // yesterday result
            2 => 'select id, laboratory, motion, time from vw_motion where id = ? and motion = true order by num desc limit 1;'
            // this week result
        );
        $sql = $query[$n];
        $conn = mysqlConnection::getConnection();
        $command = $conn->prepare($sql);
        $command->bind_param('i', $this->id);

        $command->bind_result(
                $id_,
                $name_,
                $data_,
                $time_
         );

         $data = "";

         if($n = 0){
            $data = "temperature";
         }else if($n = 1){
            $data = "humidity";
         }else if($n = 2){
            $data = "motion";
         }else{
            $data = "";
         }

        $command->execute();

        if ($command->fetch()){
			$array = array(
				"id"=>$id_,
				"laboratory"=>$name_,
				"data"=>$data_,
                "time"=>$time_
			);

            return(json_encode($array));
           
        }else{
            return json_encode(array("error"=>"Without results", "status"=>false));
            die;
        }
	    

        mysqli_stmt_close($command);
        $conn->close(); 
    }

}

?>