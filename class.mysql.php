<?PHP 
class mysql_conn {
    private $debug = true; // if true, we'll print information on mysql errors.
//  public $debug = false;  // if true, we'll print information on mysql errors. 
    
    
    public $insert_id;      // the id of the last inserted post
    public $resource;       // the mysql resource of the last query (or null after no return)
    public $affected_rows;  // number of affected rows by the last query
    public $num_rows;       // number of returned rows by last query
    public $queries;        // number of queries performed since script start
    public $conn_start;     // unix timestamp (in microseconds)
                            // for when the class was initialized
    public $last_query;    // holds the last query sent to the server.

    
    private $link;          // handle to our mysql connection
/*
    private $username;      // mysql username
    private $password;      // mysql password
    private $database;      // mysql database name
    private $hostname;      // mysql server hostname
    private $port;          // mysql server port
*/
    
// constructor
    function __construct($username, $password, $database, $hostname = 'localhost', $port = '3306') {
        $this->conn_start = microtime(true);
        $this->link = mysql_connect($hostname.":".$port,$username,$password) or  $this->err(mysql_error());
        if(!$this->link) { $this->err(mysql_error); }
        mysql_select_db($database,$this->link) or $this->err(mysql_error($this->link));
    }
   
// return for how long in microseconds since this instance of the class  was initialized
    public function execTime() {
        return microtime(true)-$this->conn_start;
    }
    
    public function ainsert($table,$fields,$values) {
        $v = "";
        $f = "";
        foreach($values as $i => $value) {
            $field = $fields[$i];
            $v .= "'".$this->sqlesc($value)."',";
            $f .= "`$field`,";
        }
        $v = substr($v,0,-1);
        $f = substr($f,0,-1);
        return $this->q("INSERT INTO `$table` ($f) values ($v)");
    }
    
// public query function 
    public function query() {
        $args = func_get_args();
        $this->q($this->escape_args($args));
        if(is_resource($this->result)) {
            $array = [];
            while($row = mysql_fetch_assoc($this->result)) {
                $array[] = $row;
            }
            return $array;
        } else {
            return $this->result;
        }
    }
    
    public function __destruct() {
        mysql_close($this->link);
    }
    

// escapes arguments with odd index numbers, and assumes even are safe.
    private function escape_args(array $args) {
        $query = "";
        foreach($args as $i => $arg) {
            if($i % 2) {
                $query .= $this->sqlesc($arg);
            } else {
                $query .= $arg;
            }
        }
        return $query;
    }

    
// all queries made through mysql_query should eventually go through here
    private function q($query) {
        $this->last_query = $query;
        $this->result = mysql_query($query,$this->link) or $this->err(mysql_error());
        $this->queries++;
        $this->affected_rows = mysql_affected_rows($this->link);
        if(is_resource($this->result)) {
            $this->num_rows = mysql_num_rows($this->result);
        } else {
            $this->num_rows = 0;
        }
        
        $this->insert_id = mysql_insert_id($this->link); // <-- Ugly sollution, I know
    }

// escapes strings with mysql_real_escape_string 
// (if magic_quotes_gpc is enabled, first apply stripslashes)
    private function sqlesc($string) {
        return mysql_real_escape_string(get_magic_quotes_gpc() ? stripslashes($string) : $string);
    }
    
// if debug output is enabled, print the mysql error message
// as well as the query that caused it, and die.
// if not, die silently.
    private function err($err) {
        if($this->debug) {
            $err .= nl2br((!empty($this->last_query) ? "\n\nquery: ".$this->last_query : "\n\n"));
            die($err);
        } else {
            die();
        }
    }
    
/** insert_array (leaving this commented for now)
    $array = [
        'table' => "tablename",
        'data' => [
            $fieldName => $fieldValue
        ];
    ];
 

    disabling this for now. 
    made this to make the migration from json- format less painful.
    not sure I want it live. 

    public function insert_array(array $array) {
        $table = $array['table'];
        $values = ""; $fields = "";
        
        foreach($array['data'] as $field => $value) {
            $fields .= (strlen($fields) ? " , " : "") . "`$field`";
            $values .= (strlen($values) ? "," : "") . "'$value'";
        }
        
        return $this->insert("insert into `$table` ( $fields ) values ( $values )");
    }
*/
}
