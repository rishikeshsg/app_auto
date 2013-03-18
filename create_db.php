<?php

include "connect.php";
$max_userlevel = 2;
$max_resource = 3;
$max_req = 11;
$max_user = 5;

$con = mysql_connect ($server,$user,$pass);

if(!$con){
  echo "Connection Failure";
	exit();
}

$p = mysql_query("CREATE DATABASE IF NOT EXISTS ".$database);
if(!$p){
	echo "Unable to create db";
	exit();
}
echo "Database successfully created</br>";
mysql_select_db($database,$con);

$p = mysql_query("CREATE TABLE IF NOT EXISTS user_type(u_level INT($max_userlevel) PRIMARY KEY AUTO_INCREMENT,u_type VARCHAR(20) NOT NULL)");
if(!$p){
	echo "Unable to create user_type table";
	exit();
}
echo "user_type table successfully created</br>";

$p = mysql_query("CREATE TABLE IF NOT EXISTS user_details(uid INT($max_user) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(40) NOT NULL,uname VARCHAR(40) NOT NULL UNIQUE,email VARCHAR(40) NOT NULL UNIQUE,pass VARCHAR(40) NOT NULL,designation VARCHAR(40) NOT NULL,contact VARCHAR(10) NOT NULL,u_type INT($max_userlevel) NOT NULL,FOREIGN KEY(u_type) REFERENCES user_type(u_level))");
if(!$p){
	echo "Unable to create user_details table";
	exit();
}
echo "user_details table successfully created</br>";

$p = mysql_query("CREATE TABLE IF NOT EXISTS resource(rid INT($max_resource) PRIMARY KEY AUTO_INCREMENT,r_name VARCHAR(40) NOT NULL,location VARCHAR(40) NOT NULL,facilities VARCHAR(1000))");
if(!$p){
	echo "Unable to create resource table";
	exit();
}
echo "resource table successfully created</br>";

$p = mysql_query("CREATE TABLE IF NOT EXISTS request(req_id INT($max_req) PRIMARY KEY AUTO_INCREMENT,title VARCHAR(100) NOT NULL,description VARCHAR(2000),s_date DATE NOT NULL, e_date DATE NULL,s_time TIME NOT NULL, e_time TIME NOT NULL,entrydate TIMESTAMP NOT NULL,uid INT($max_user) NOT NULL, rid INT($max_resource) NOT NULL,status VARCHAR(100) DEFAULT 'pending',FOREIGN KEY(uid) REFERENCES user_details(uid),FOREIGN KEY(rid) REFERENCES resource(rid))");
if(!$p){
	echo "Unable to create request table";
	exit();
}
echo "request table successfully created</br>";

$p = mysql_query("CREATE TABLE IF NOT EXISTS request_status(remark VARCHAR(1000),status VARCHAR(100) DEFAULT 'pending',status_timestamp TIMESTAMP,uid INT($max_user) NOT NULL,req_id INT($max_req) NOT NULL,PRIMARY KEY(req_id,uid),FOREIGN KEY(uid) REFERENCES user_details(uid),FOREIGN KEY(req_id) REFERENCES request(req_id))");
if(!$p){
	echo "Unable to create request_status table";
	exit();
}
echo "request_status table successfully created</br>";
?>
