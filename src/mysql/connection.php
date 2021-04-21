<?php 

class mysqlConnection {

        public static function getConnection(){
                  

               $var='{ "mysqlCrendentials" : { "server" : "localhost", "user" : "root", "pass" : "", "db" : "security_system" } }';                              

               $config=json_decode($var,true);

               if (isset($config['mysqlCrendentials'])){ 

                        $credentials =$config['mysqlCrendentials'];        

                        if (isset($credentials['server'])){
                            $server=$credentials['server'];
                        } else {
                            $error = json_encode(array("error"=>"Server not found", "status"=>false));
                            echo $error;
                            die;
                        }

                        if (isset($credentials['user'])){
                            $user=$credentials['user'];
                        } else {
                            $error = json_encode(array("error"=>"User not found", "status"=>false));
                            echo $error;
                            die;
                        }

                        if (isset($credentials['pass'])){
                            $pass=$credentials['pass'];
                        } else {
                            $error = json_encode(array("error"=>"Invalid password", "status"=>false));
                            echo $error;
                            die;
                        }

                        if (isset($credentials['db'])){
                            $db=$credentials['db'];
                        } else {
                            $error = json_encode(array("error"=>"Database incorrect", "status"=>false));
                            echo $error;
                            die;
                        }

                        $conn= mysqli_connect($server,$user,$pass,$db);

                        if ($conn===false){
                            $error = json_encode(array("error"=>"Connection error", "status"=>false));
                            echo $error;
                            die;
                        } 
                         
                        $conn->set_charset('utf8');

                         return $conn;
                } else {
                    $error = json_encode(array("error"=>"Credentials not found", "status"=>false));
                    echo $error;
                    die;

                }
        }
    }


    
?>
