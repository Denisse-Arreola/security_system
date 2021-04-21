<?php

require_once("connection.php");

class user{

    private $id;
    private $firstname;
    private $lastname;
    private $password;
    private $lab;

    public function __construct(){
        $args = func_get_args();

        if (func_num_args() == 0){
            $this->id = 0;
            $this->firstname = "";
            $this->lastname = "";
            $this->password = "";
            $this->lab = 0;
        }

        // Constructor for searching by id
        if(func_num_args() == 1){
            $sql = "select * from vw_employee where id = ?;";
            $conn = mysqlConnection::getConnection();
            $command = $conn->prepare($sql);
            $command->bind_param('i', $args[0]);

            $command->bind_result(
                $id_,
                $firstname_,
                $lastname_,
                $password_,
                $lab_
            );

            $command->execute();

            if ($command->fetch()){
                $this->id = $id_;
                $this->firstname = $firstname_;
                $this->lastname = $lastname_;
                $this->password = $password_;
                $this->lab = $lab_;               

                json_decode(self::getJSON());
            }

            mysqli_stmt_close($command);
            $conn->close();
        }

        // Constructor for verifying a user
        if(func_num_args() == 2){
            $this->id = $args[0];
            $this->firstname = "";
            $this->lastname = "";
            $this->password = $args[1];
            $this->lab = 0;
            
        }

        // Constructor for sending data for a new user
        if (func_num_args() == 5){
            $this->id = $args[0];
            $this->firstname = $args[1];
            $this->lastname = $args[2];
            $this->password = $args[3];
            $this->lab = $args[4];
        }

    }

    public function newUser(){

        $conn = mysqlConnection::getConnection();

        //EXAMPLE
        //                           ID      FIRSTNAME   LASTNAME       PASSWORD     LAB
        //call sp_insert_employee(319124849, "MARCUS", "HERNANDEZ", "security_system", 1);
        $sql = "call sp_insert_employee(?, ?, ?, ?, ?);";
        $command = $conn->prepare($sql);
        $command->bind_param('isssi',
                            $this->id,
                            $this->firstname,
                            $this->lastname,
                            $this->password,
                            $this->lab);
		
		$command->execute();

    

        if($command->error !=""){
            return json_encode(array("error"=>$command->error, "status"=>false));

        }else{
            return json_encode(array("response"=>"Log up successful", "status"=>true));
        }        
		

        mysqli_stmt_close($command);
        $conn->close();
    }


    public function userAcess(){

        $sql = "select * from vw_employee where id = ? and pass = ?;";
        $conn = mysqlConnection::getConnection();
        $command = $conn->prepare($sql);
        $command->bind_param('is', $this->id, $this->password);

        $command->bind_result(
                $id_,
                $firstname_,
                $lastname_,
                $password_,
                $lab_
            );

        $command->execute();
        if ($command->fetch()){

            $this->id = $id_;
            $this->firstname = $firstname_;
            $this->lastname = $lastname_;
            $this->password = $password_;
            $this->lab = $lab_;               

            return self::getJSON();

        }else{

            return json_encode(array("error"=>"Not found", "status"=>false));
            die;
        }

        mysqli_stmt_close($command);
        $conn->close();
    }


    public function getJSON(){
        return json_encode(
            array(
                'id'=>$this->id,
                'firstname'=>$this->firstname,
                'lastname'=>$this->lastname,
                'password'=>$this->password,
                'lab'=>$this->lab,
                'status'=>true
            )
        );
    }

}

?>