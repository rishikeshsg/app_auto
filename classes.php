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
	public function fetch_array($query_result){
		if($query_result){
			return mysql_fetch_array($query_result);
		}
		else{
			return null;
		}
	}
}
/* Class for resources. */
class resources{
	private $resource_details;
	private $rid;											//id of the resource
	private $r_db;
	
	/* Constructor. */
	public function resources($id){							//$id is passed as the POST method variable from the drop down list, so $id = $_POST['r_id']
		$this->r_db = new db();
		$this->resource_details = mysql_fetch_array($this->r_db->query('SELECT * FROM resource WHERE rid = $id'));
	}
	
	public function get_details(){
		return $this->resource_details;						//assign all details from this array
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

/* Class for user. */
class user{
	private $uid;
	private $name;
	private $uname;
	private $pass;
	private $email;
	private $designation;
	private $contact;
	private $u_type;
	private $pic;
	private $about;
	private $u_db;
	private $super_flag;
	private $user_details;
	
	// counting the request and approval status
	private $request_made;
	private $request_pending;
	private $request_approved;
	private $request_rejected;
	private $approval_pending;
	private $approval_rejected;
	private $approval_approved;
	private $total_approval;

	
	public function user($id){
		$this->u_db = new db();
		$this->user_details = mysql_fetch_array($this->u_db->query('SELECT * from user_details WHERE uid = '.$id.''));
		//print_r($this->user_details);
		//echo $this->user_details['u_type'];
		if($this->user_details){
			$this->uid = $this->user_details['uid'];
			$this->uname = $this->user_details['uname'];
			$this->email = $this->user_details['email'];
			$this->designation = $this->user_details['designation'];
			$this->pic = $this->user_details['pic'];
			$this->desc = $this->user_details['about'];
			$this->u_type = $this->user_details['u_type'];
			$this->contact = $this->user_details['contact'];
			$this->request_made = 0;
			$this->request_pending = 0;
			$this->request_approved = 0;
			$this->request_rejected = 0;
			$this->approval_approved = 0;
			$this->approval_pending = 0;
			$this->approval_rejected = 0;
			$this->total_approval = 0;
			
			//$this->super_flag = $this->user_details['super_flag'];
		}	
		else{
			//echo error
			//call destructor?
			//contructor not fully executed;either fetch error or resource not present; display error message;
		}	
	}
	
	/* Next two functions to be called from super admin page. */
	private function add_user(){								//as parameters all the details are to be passed.
		//$this->u_db->query('-- Insert query here');
	}
	private function delete_user(){								//as param the id to be passed.
		//$this->u_db->query('-- Delete query here');
	}
	
	public function pubAdd_user($isSuper){						//isSuper is a boolean var that will be set by a user class public function $user->issuper() to true if the user is a super admin, false otherwise
		if($isSuper) $this->add_user();
		else ;													//echo you don't have the privileges
	}
	
	public function pubDel_user($isSuper){						//isSuper is a boolean var that will be set by a user class public function $user->issuper() to true if the user is a super admin, false otherwise
		if($isSuper) $this->delete_user();
		else ;													//echo you don't have the privileges
	}
	
	/* Update/change some personal detail like phone number or email or something. */
	public function change_detail($detail, $val){
		//$this->u_db->query('-- update user table set $detail = $val where id = $this->uid');
	}
	
	/* Check if superadmin. */
	public function issuper(){
		if($this->super_flag)return true;
		else return false;
	}
	
	/* Get user details. */
	public function get_details(){
		return	$this->user_details;
	}
	public function get_profile_data(){
		while($profile_details = current($this->user_details))
		{
			next($this->user_details);
			if(!(key($this->user_details) == "uid" || key($this->user_details) == "pass" || key($this->user_details) == "u_type" || key($this->user_details) == "about" || key($this->user_details) == "pic"))
			{
				if(key($this->user_details) == "uname")
					$user_type = "username";
				else $user_type = key($this->user_details);
				echo "<li><a> ".ucwords($user_type)." : &nbsp".current($this->user_details)." </a></li>";
			}
			
			next($this->user_details);
		}
	}
	
	// Get about me from user_details
	public function get_about_me(){
		echo "<li><a> ".$this->desc." </a></li>";	
	}
	
	// get the user pic otherwise default
	public function get_pic(){
		echo $this->pic;
	}
	
	// get the request status i.e. total requests made, requests approved, requests pending.
	public function get_request_number(){
		$result = $this->u_db->query('SELECT * from request WHERE uid = '.$this->uid.'');
		while($row = mysql_fetch_array($result)){
			if(strtolower($row['status']) == 'pending'){
				$this->request_made++;
				$this->request_pending++;		
			}
			if(strtolower($row['status']) == 'approved')
			{
				$this->request_made++;
				$this->request_approved++;
			}
			if(strtolower($row['status']) == 'rejected')
			{
				$this->request_made++;
				$this->request_rejected++;
			}
		}	
		echo "<li><a> Request made : &nbsp".$this->request_made." </a></li>";
		echo "<li><a> Request Pending : &nbsp".$this->request_pending." </a></li>";
		echo "<li><a> Request Approved : &nbsp".$this->request_approved." </a></li>";
		echo "<li><a> Request Rejected : &nbsp".$this->request_rejected." </a></li>";
	}
	
	// get the approval status if its user type is > 1 i.e. it has the power to approve any request.
	public function get_approval_number(){
		if(intval($this->u_type) >= 2)
		{
			$result = $this->u_db->query('SELECT * from request_status WHERE uid = '.$this->uid.'');
			while($row = mysql_fetch_array($result)){
				
				if(strtolower($row['status']) == 'pending'){
					$this->total_approval++;
					$this->approval_pending++;		
				}
				if(strtolower($row['status']) == 'approved')
				{
					$this->total_approval++;
					$this->approval_approved++;
				}
				if(strtolower($row['status']) == 'rejected')
				{
					$this->total_approval++;
					$this->approval_rejected++;
				}
			}
			echo "<li><a> Total Approval : &nbsp".$this->total_approval." </a></li>";
			echo "<li><a> Approval Pending : &nbsp".$this->approval_pending." </a></li>";
			echo "<li><a> Approval Approved : &nbsp".$this->approval_approved." </a></li>";
			echo "<li><a> Approval Rejected : &nbsp".$this->approval_rejected." </a></li>";
			
		}
	}
	
	// get the  user type
	public function get_user_type(){
		return $this->u_type;
	}
}
?>
