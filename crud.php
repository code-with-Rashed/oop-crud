<?php 
  class Database{
      private $db_host = "" ; //Your Hosting Name
      private $db_username = "";  //Database User Name
      private $db_password = "" ;    //Database Password
      private $db_name = "" ;   //Database Name
      
      private $conn = false;
      private $mysqli = "";
      private $result = [];

      //open connection
      public function __construct(){
          if(!$this->conn){
             $this->mysqli = new mysqli($this->db_host , $this->db_username , $this->db_password , $this->db_name);
             $this->conn = true;
             if($this->mysqli->connect_error){
                 array_push($this->result , $this->mysqli->connect_error);
                 return false;
             }
          }else{
              return false;
          }

      }

      //Insert Function
      public function insert(string $table , $params=[]){
        if( $this->tableExist($table) ){
            $table_column = implode(" , ",array_keys($params));
            $table_value = implode("' , '",$params);
            $sql = "INSERT INTO $table ($table_column) VALUES ('$table_value')";
            if($this->mysqli->query($sql)){
                array_push($this->result , $this->mysqli->insert_id);
                return true;
            }else{
                array_push($this->result , $this->mysqli->error);
                return false;
            }
        }
      }
      //Update Function
       public function update(string $table , $update = [] , $where = null){
           if($this->tableExist($table)){
              $args = [];
              foreach($update as $key=>$value){
                $args [] = " $key = '$value' ";
              }
              $sql = "UPDATE $table SET ". implode(" , ",$args);
              if($where != null){
                $sql .= " WHERE $where";
              }
              if($this->mysqli->query($sql)){
                array_push($this->result , $this->mysqli->affected_rows);
                return true;
              }else{
                array_push($this->result , $this->mysqli->error);
                return false;   
              }
           }
       }
      //Delete Function
      public function delete(string $table , string $where){
        if($this->tableExist($table)){
            $sql = "DELETE FROM $table WHERE $where";
            if($this->mysqli->query($sql)){
              array_push($this->result , $this->mysqli->affected_rows);
              return true;
            }else{
              array_push($this->result , $this->mysqli->error);
              return false;   
            }
        }
       }
      //select Function
      public function select(string $table , $rows = "*" , $join = null , $where = null , $order = null , $start = null , $limit = null){
        if($this->tableExist($table)){
              $sql = "SELECT $rows FROM $table";
            if($join != null){
              $sql .= " JOIN $join ";
            }
            if($where != null){
              $sql .= " WHERE $where ";
            }
            if($order != null){
              $sql .= " ORDER BY $order ";
            }
            if($start !== null && $limit != null){
              $sql .= " LIMIT $start , $limit ";
            }
            $query = $this->mysqli->query($sql);
            if($query){
              if($query->num_rows > 0){
                $this->result = $query->fetch_all(MYSQLI_ASSOC);
                return true;
              }else{
                array_push($this->result , "Data Not Found.");
                return false;
              }
            }else{
              array_push($this->result , $this->mysqli->error);
              return false;
            }
        }
      }
      // Count Total Record
      public function pagination(string $table , $join = null , $where = null){
        if($this->tableExist($table)){
          $sql = "SELECT COUNT(*) FROM $table";
          if($join != null){
            $sql .= " JOIN $join";
          }
          if($where != null){
            $sql .= " WHERE $where";
          }
          $total = $this->mysqli->query($sql);
          if( $total && $total->num_rows > 0 ){
              array_push($this->result , $total->fetch_row());
              return true;
          }else{
            array_push($this->result , $this->mysqli->error);
            return false;
          }
        }
      }
      //checking table in Database
      private function tableExist(string $table){
       $sql = "SHOW TABLES FROM $this->db_name LIKE '$table' ";
       $tableInDb = $this->mysqli->query($sql);
       if($tableInDb){
         if($tableInDb->num_rows == 1){
             return true ;
         }else{
           array_push($this->result, $table . " does not exist in this Database.");
           return false;
         }
       }else{
        return false;
       }
      }
      //send result
      public function getResult(){
        $value = $this->result ;
        $this->result = [];
        return $value;
      }
      //   close connection
      public function __destruct(){
        if($this->conn){
           if($this->mysqli->close()){
             $this->conn = false;
             return true;
           }
        }else{
            return false;
        }  
      }
  }
?>