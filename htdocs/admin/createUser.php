<?php 
########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 04-01-2017  
# EDIT     : 04-02-2017 
# COMMENTS : This script Manipulates users for web console
# VERSION  : 0.99B
########################################################### 

####################
# Var definitions
####################

	##If access is not granted return to index with error
    session_start();
    $role = $_SESSION['sess_userrole'];
    if(!isset($_SESSION['sess_username']) || $role!="admin")
	{
      header('Location: index.php?err=2');
    }

	## Require db config
	require 'database-config.php';

	## Get operations and data
	if ($_GET) {
		$operation = $_GET['operation'];
		$usr = $_GET['usr'];
		$pass = md5($_GET['pass']);
		$passc = md5($_GET['passc']);
		$roles = $_GET['role'];
		$ID = $_GET['ID'];
	} 
	
###################
#	Functions
###################
	
	
#############################################
#	Delete User Entry
#############################################

function DeleteUser($ID)
{

	$status = "not_init";
	require 'database-config.php';
	echo "\n",'Deleting User Entry: ', $ID,"\n";
	
	try 
	{	
	if(($result = sqlsrv_query($dbh,"delete from ADGroupUsers  where id = '$ID'")) !== false)
	{
		$status = "ok";	
    }
		
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}	

#############################################
#	Create User Entry
#############################################

function CreateUser($usr, $pass, $passc, $roles)
{
	
	$status = "not_init";
	require 'database-config.php';
	echo "\n",'Create User Entry: ',"\n";
	
	try 
	{	
		## User already exists
		if(($result = sqlsrv_query($dbh,"SELECT * FROM ADGroupUsers where usr = '$usr'")) !== false)
		{
			while( $obj = sqlsrv_fetch_object( $result )) 
			{
				$status = "fail";	
				header('Location: pages/tables/requestUser.php?stus=1');
			}
		}	

		## Passwords are different
		if ($pass != $passc )
		{
			$status = "fail";	
			header('Location: pages/tables/requestUser.php?stus=2');
		}	

		if(($result = sqlsrv_query($dbh,"Insert into ADGroupUsers With (ROWLOCK) (usr,pass,usr_role) SELECT '$usr','$pass','$roles' WHERE not exists (select usr from ADGroupUsers where usr = '$usr')")) !== false)
		{
			$status = "ok";		
		}
    }
		
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}	
	


########################################################
#	Execution flux
########################################################

## If access is not granted return to index with error
if(!isset($_SESSION['sess_username']) || $role!="admin")
{
    header('Location: index.php?err=2');
}

else {
	
	#############################
	## Operations for Web Page
	#############################
	echo "Operation: ", $operation;

	## Operation to delete user
	if ($operation == "deleteusr")
	{
		$execute = DeleteUser($ID);
		echo "\n", 'Status for DeleteUser: ',  $execute, "\n";
		header("Location: http://<server>/admin/pages/tables/requestUser.php?stus=5"); # Redirect browser
		exit;
	}

	## Operation to create user
	if ($operation == "createusr")
	{
		$execute = CreateUser($usr, $pass, $passc, $roles);
		echo "\n", 'Status for CreateUser: ',  $execute, "\n";
		header("Location: http://<server>/admin/pages/tables/requestUser.php?stus=3"); # Redirect browser
		exit;
	}
}
?>