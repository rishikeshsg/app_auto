arpit

<?php
/* Config.php sets the global variables for the database once and for all. */
include_once('db_config.php');



/* Class for Database functions. */
class db
{
  /* username, host, database, error and password */
	private	$user;
	private $password;
	private $host;
	private $db;
	private $error;
	private $opendb;
	private $link;
	
	/* constructor, $database,$hostname,$username,$pass are the global vars included from db_config.php */
	public function db()
	{
		$this->db = $database;
		$this->host = $hostname;
		$this->password = $pass;
		$this->user = $username;
		$this->error = "";
		
		$this->opendb = false;
	}
	
	/* opening the connection */
	public function open()
	{ 
		$this->link = mysql_connect($this->host,$this->user,$this->password);
		
		/* if link fails */
		if(!$this->link)
		{
			$this->error .='Could not connect to database.</br>';
			$this->opendb = false;
		}
		else $this->opendb = true;
		
		/* selecting databse and returning false if not able to select */
		if(!mysql_select_db($this->db,$this->link))
		{
			$this->error .='Could not select the database.</br>';
			$this->opendb = false;
		}
		else $this->opendb = true;
	}
	
	/* checking if connection present */
	public function is_dbopen()
	{
		if($this->opendb == true)return (boolean) true;
		else return (boolean) false;
	}
	
	/* closes connection to the database */
	public function close()	
	{
		if($this->is_dbopen())
		{
			if(mysql_close($this->link)) $this->opendb = false;
			else $this->error .= 'Could not close connection to the server.</br>';
		}
		
		else $this->error .= 'Connection was not closed as database connection was not open.</br>';
	}
	
	/* error is printed */
	public function getError()
	{
		if($this->error == '');
		return (string)$this->error;
	}
	
	/* querying db */
	public function query($queryString)
	{
		if (empty($queryString))
		{
			$this->error .= "Sorry, but you probably haven't queried for anything...<br/>";
			exit;		//remove this exit later
		}
		
		/* Opening the db connection. */
		$this->open();
		
		/* checking to see if db open */
		if($this->is_dbopen())
		{
			$resource = mysql_query($queryString);
			$this->close();
			if($resource==null) echo " null resource ";
			if(!$resource)
			{
				$this->error .= 'Query not executed.</br>';
				return null;
			}
			return $resource;
		}
		else 
		{
			$this->error .= 'Query could not be executed as database is not connected initially.</br>';
			return null;
		}
	}
}
/* Class for resources. */
class resources{
	private	$rid;											//the unique id
	private	$r_name;										//name of resource
	private	$location;										//the location details
	private	$alloted;										//fetch from database to see if alloted, this property will be set by the constructor function call
	private $r_db;											//the db object for resource 
	
	public $resource_details;
	
	/* Constructor. */
	public function resources(){
		$this->r_db = new db();
		$this->resource_details = mysql_fetch_array($this->r_db->query('-- select statement here'));
		if($this->resource_details){
			$this->alloted = $resource_details['allot'];		//assuming the attribute name is allot
			$this->rid = $resource_details['rid'];		//assuming the attribute name is alloted
			$this->r_name = $resource_details['r_name'];		//assuming the attribute name is alloted
			$this->location = $resource_details['location'];		//assuming the attribute name is allot
			//other attributes to be aded here
		}
		
		else{
			//contructor not fully executed
		}
	}
	public function add_resource(){
	
	}
	public function delete_resource(){
	
	}
	
	/* Probably useless function. */
	public function add_location(){
	
	}
	
	public function get_details(){
		if(!$this->resource_details) $this->resource_details = mysql_fetch_array(query('-- select statement here'));
		//assign all details from this array
	}
}

class request{
	private $req_id;				//unique for each request
	private $title;
	private	$description;
	private	$s_date;
	private	$s_time;
	private	$e_time;
	private	$e_date;
	private	$entry_date;
	private	$status;
	private $is_sender;
	private $cur_user;
	private $remark;
	private $req_status;
	
	public $request_details;
	public $request_status;
	public $r_db;
	
	public function request($id,$u_id){
		$r_db = new db();
		$this->cur_user = $u_id;
		$this->request_details = mysql_fetch_array($r_db->query("SELECT * FROM request WHERE req_id = ".$id));
		if($this->request_details){
			$this->req_id = $this->request_details['req_id'];
			$this->title = $this->request_details['title'];
			$this->description = $this->request_details['description'];
			$this->s_date = $this->request_details['s_date'];
			$this->s_time = $this->request_details['s_time'];
			$this->e_date = $this->request_details['e_date'];
			$this->e_time = $this->request_details['e_time'];
			$this->entry_date = $this->request_details['entry_date'];
			$this->status = $this->request_details['status'];
			if($this->request_details['uid'] == $this->cur_user){
				$this->is_sender = 1;
				if(!$r_db->query("UPDATE request SET seen = 1 WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user))
						;//call destructor
			}
			else{
				$this->request_status = mysql_fetch_array($r_db->query("SELECT * FROM request_status WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user));
				if($this->request_status){
					$remark = $arr['remark'];
					$req_status = $arr['status'];
					if(!$r_db->query("UPDATE request_status SET seen = 1 WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user))
						;//call destructor
				}
				else
					;//call destructor
			}
		}
		else{
			;//call destructor
		}
	}
	
	public function get__request_details(){
		return $request_details;
	}
	
	public function add_status($stat){
		$r_db->query("UPDATE request_status SET status = ".$stat." WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user);
	}
	
	public function add_remark($remrk){
		$r_db->query("UPDATE request_status SET remark = ".$remrk." WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user);
	}
	
	public function get_status_details(){
		return $request_status;
	}
	
	public function cancel_request(){
		$r_db->query("DELETE FROM request WHERE req_id = ".$this->req_id." AND uid = ".$this->cur_user);
	}
}
?>
