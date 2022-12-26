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

  //insert method
  public function insert(string $table, array $params = [])
  {
    $this->table_exist($table);
    $column_name = implode(" , ", array_keys($params));
    $column_value = implode("' , '", $params);
    $insert_sql = "INSERT INTO $table ($column_name) VALUE ('$column_value')";
    try {
      $this->mysqli->query($insert_sql);
    } catch (\Throwable $err) {
      echo "<br>Query : $insert_sql<br>";
      die($err->getMessage());
    }
    $this->set_result($this->mysqli->insert_id);
    return true;
  }
  //-------------

  //update method
  public function update(string $table, array $update = [], string $where = null): bool
  {
    $this->table_exist($table);
    $args = [];
    foreach ($update as $key => $value) {
      $args[] = "$key = '$value'";
    }
    $update_sql = "UPDATE $table SET " . implode(" , ", $args);
    if ($where) {
      $update_sql .= " WHERE $where";
    }
    try {
      $this->mysqli->query($update_sql);
    } catch (\Throwable $err) {
      echo "<br>Query : $update_sql<br>";
      die($err->getMessage());
    }
    $this->set_result($this->mysqli->affected_rows);
    return true;
  }
  //--------------

  //delete method
  public function delete(string $table, string $where): bool
  {
    $this->table_exist($table);
    $delete_sql = "DELETE FROM $table WHERE $where";
    try {
      $this->mysqli->query($delete_sql);
    } catch (\Throwable $err) {
      echo "<br>Query : $delete_sql<br>";
      die($err->getMessage());
    }
    $this->set_result($this->mysqli->affected_rows);
    return true;
  }

  //select method
  public function select(string $table, string $column = "*", string $join = null, string $where = null, string $order = null, int $start = null, int $limit = null)
  {
    $this->table_exist($table);
    $select_sql = "SELECT $column FROM $table";
    if ($join) {
      $select_sql .= " JOIN $join";
    }
    if ($where) {
      $select_sql .= " WHERE $where";
    }
    if ($order) {
      $select_sql .= " ORDER BY $order";
    }
    if (!is_null($start) && $limit) {
      $select_sql .= " LIMIT $start , $limit";
    }
    try {
      $result = $this->mysqli->query($select_sql);
    } catch (\Throwable $err) {
      echo "<br>Query : $select_sql<br>";
      die($err->getMessage());
    }
    $this->set_result($result->fetch_all(MYSQLI_ASSOC));
    return true;
  }
  //-------------

  //total row count method
  public function count_row(string $table, string $join = null, string $where = null): bool
  {
    $this->table_exist($table);
    $count_sql = "SELECT COUNT(*) FROM $table";
    if ($join) {
      $count_sql .= " JOIN $join";
    }
    if ($where) {
      $count_sql .= " WHERE $where";
    }
    try {
      $total = $this->mysqli->query($count_sql);
    } catch (\Throwable $err) {
      echo "<br>Query : $count_sql<br>";
      die($err->getMessage());
    }
    $this->set_result($total->fetch_row());
    return true;
  }
  //----------------------

  //direct sql comand execution method
  public function sql(string $sql_query): bool
  {
    try {
      $run_query = $this->mysqli->query($sql_query);
    } catch (\Throwable $err) {
      echo "<br>Query : $sql_query<br>";
      die($err->getMessage());
    }
    $sql_array = explode(" ", $sql_query);
    $sql_type = strtolower($sql_array[0]);
    match ($sql_type) {
      "select" => $this->set_result($run_query->fetch_all(MYSQLI_ASSOC)),
      "insert" => $this->set_result($this->mysqli->insert_id),
      "update" => $this->set_result($this->mysqli->affected_rows),
      "delete" => $this->set_result($this->mysqli->affected_rows),
      default => $this->set_result($run_query)
    };
    return true;
  }
  //----------------------------------

  //check if table is exist for use with queries
  private function table_exist(string $table)
  {
    $sql = "SHOW TABLES FROM $this->db_name LIKE '$table'";
    try {
      $result = $this->mysqli->query($sql);
      if ($result->num_rows) {
        return true;
      } else {
        die("This table ($table) does not exist in this ($this->db_name) DATABASE");
      }
    } catch (\Throwable $err) {
      echo "<br>Query : $sql<br>";
      die($err->getMessage());
    }
  }
  //-------------------------------------

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
