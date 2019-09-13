<?php 
########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 04-01-2017  
# EDIT     : 04-02-2017 
# COMMENTS : This script Authenticates with DB.
# VERSION  : 0.99B
########################################################### 

####################
# Var definitions
####################

	## Require db config
	require 'database-config.php';

	session_start();

	$username = "";
	$password = "";

	## Get operations and data	
	if(isset($_POST['username']))
	{
		$username = $_POST['username'];
	}
	if (isset($_POST['password'])) 
	{
		$password = $_POST['password'];
		$password = md5($password);
	}
	
    header('Location: index.php?err=1');

	## Compare with DB in order to grant access
	if(($result = sqlsrv_query($dbh,"SELECT * FROM ADGroupUsers where usr = '$username' and pass = '$password'")) !== false)
	{
		while( $obj = sqlsrv_fetch_object( $result )) 
		{

			session_regenerate_id();
			$_SESSION['sess_user_id'] = $obj->id;
			$_SESSION['sess_username'] = $obj->usr;
			$_SESSION['sess_userrole'] = $obj->usr_role;

			echo $_SESSION['sess_userrole'];
			session_write_close();

			if( $_SESSION['sess_userrole'] == "admin")
			{
				header('Location: home.php');
			}
			else
			{
				header('Location: home.php');
			}
		
		}
    }


?>



