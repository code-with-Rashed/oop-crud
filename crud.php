<?php
//connect database then execute crud operation
class Database
{
  private $db_host = "localhost"; //Your Hosting Name
  private $db_username = "root";  //Database User Name
  private $db_password = "";    //Database Password
  private $db_name = "test";   //Database Name

  private $mysqli = ""; // This will be our mysqli object
  private $result = []; // Any results from a query will be stored here
  private $myQuery = ""; // used for debugging process with SQL return

  private $conn = false;

  //open connection
  public function __construct()
  {
    if (!$this->conn) {
      $this->mysqli = new mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name);
      $this->conn = true;
      if ($this->mysqli->connect_error) {
        array_push($this->result, $this->mysqli->connect_error);
        return false;
      }
    } else {
      return false;
    }
  }

  //Insert Function
  public function insert(string $table, $params = [])
  {
    if ($this->tableExist($table)) {
      $table_column = implode(" , ", array_keys($params));
      $table_value = implode("' , '", $params);
      $sql = "INSERT INTO $table ($table_column) VALUES ('$table_value')";
      $this->myQuery = $sql;
      if ($this->mysqli->query($sql)) {
        array_push($this->result, $this->mysqli->insert_id);
        return true;
      } else {
        array_push($this->result, $this->mysqli->error);
        return false;
      }
    }
  }

  //Update Function
  public function update(string $table, $update = [], $where = null)
  {
    if ($this->tableExist($table)) {
      $args = [];
      foreach ($update as $key => $value) {
        $args[] = " $key = '$value' ";
      }
      $sql = "UPDATE $table SET " . implode(" , ", $args);
      if ($where != null) {
        $sql .= " WHERE $where";
      }
      $this->myQuery = $sql;
      if ($this->mysqli->query($sql)) {
        array_push($this->result, $this->mysqli->affected_rows);
        return true;
      } else {
        array_push($this->result, $this->mysqli->error);
        return false;
      }
    }
  }

  //Delete Function
  public function delete(string $table, string $where)
  {
    if ($this->tableExist($table)) {
      $sql = "DELETE FROM $table WHERE $where";
      $this->myQuery = $sql;
      if ($this->mysqli->query($sql)) {
        array_push($this->result, $this->mysqli->affected_rows);
        return true;
      } else {
        array_push($this->result, $this->mysqli->error);
        return false;
      }
    }
  }

  //select Function
  public function select(string $table, $rows = "*", $join = null, $where = null, $order = null, $start = null, $limit = null)
  {
    if ($this->tableExist($table)) {
      $sql = "SELECT $rows FROM $table";
      if ($join != null) {
        $sql .= " JOIN $join ";
      }
      if ($where != null) {
        $sql .= " WHERE $where ";
      }
      if ($order != null) {
        $sql .= " ORDER BY $order ";
      }
      if ($start !== null && $limit != null) {
        $sql .= " LIMIT $start , $limit ";
      }
      $this->myQuery = $sql;
      $query = $this->mysqli->query($sql);
      if ($query) {
        $this->result = $query->fetch_all(MYSQLI_ASSOC);
        return true;
      } else {
        array_push($this->result, $this->mysqli->error);
        return false;
      }
    }
  }

  // Count table records
  public function tableRowsCount(string $table, $join = null, $where = null)
  {
    if ($this->tableExist($table)) {
      $sql = "SELECT COUNT(*) FROM $table";
      if ($join != null) {
        $sql .= " JOIN $join";
      }
      if ($where != null) {
        $sql .= " WHERE $where";
      }
      $this->myQuery = $sql;
      $total = $this->mysqli->query($sql);
      if ($total && $total->num_rows > 0) {
        array_push($this->result, $total->fetch_row());
        return true;
      } else {
        array_push($this->result, $this->mysqli->error);
        return false;
      }
    }
  }

  //sql command exequte function
  public function sql(string $sql)
  {
    $this->myQuery = $sql;
    $query = $this->mysqli->query($sql);
    if ($query) {
      $sql_arr = explode(" ", $sql);
      $sql_type = strtolower($sql_arr[0]);
      switch ($sql_type) {
        case 'insert':
          array_push($this->result, $this->mysqli->insert_id);
          break;
        case 'update':
          array_push($this->result, $this->mysqli->affected_rows);
          break;
        case 'delete':
          array_push($this->result, $this->mysqli->affected_rows);
          break;
        case 'select':
          array_push($this->result, $query->fetch_all(MYSQLI_ASSOC));
          break;
      }
      return true;
    } else {
      array_push($this->result, $this->mysqli->error);
      return false;
    }
  }

  // Private function to check if table exists for use with queries
  private function tableExist(string $table)
  {
    $sql = "SHOW TABLES FROM $this->db_name LIKE '$table' ";
    $this->myQuery = $sql;
    $tableInDb = $this->mysqli->query($sql);
    if ($tableInDb) {
      if ($tableInDb->num_rows == 1) {
        return true;
      } else {
        array_push($this->result, $table . " does not exist in this Database.");
        return false;
      }
    } else {
      return false;
    }
  }
  
    //set result
    private function set_result($results)
    {
      array_push($this->result, $results);
    }
    
  //send result
  public function getResult()
  {
    $val = $this->result;
    $this->result = [];
    return $val;
  }

  //send sql query for debugging
  public function getSql()
  {
    return $this->myQuery;
  }

  //escape string
  public function escapeString($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $this->mysqli->real_escape_string($data);
    return $data;
  }

  //close connection
  public function __destruct()
  {
    if ($this->conn) {
      if ($this->mysqli->close()) {
        $this->conn = false;
        return true;
      }
    } else {
      return false;
    }
  }
}
