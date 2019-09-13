<html>
<body>

<h1>DB Operations</h1>

<?php

########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 03-06-2017  
# EDIT     : 05-10-2017 
# COMMENTS : This script communicates with the DB.
# VERSION  : 1.75RC2
########################################################### 




####################
# Var definitions
####################

if ($_GET) {
    $operation = $_GET['operation'];
    $ticket = $_GET['ticket'];
	$ID = $_GET['ID'];
	$_application = $_GET['application'];
	$_company = $_GET['company'];
	$_domain = $_GET['domain'];		
	$_apprequested = $_GET['apprequested'];
	$_tickettype = $_GET['tickettype'];
	$_category = $_GET['category'];
	$_app_name = $_GET['app_name'];
	$_assignment_group = $_GET['assignment_group'];
	$_ADGroup = $_GET['ADGroup'];
	$_UserCompany = $_GET['UserCompany'];	
	$_domain = $_GET['domain'];		
} else {
    $operation = $argv[1];
    $ticket = $argv[2];
	$ID = $argv[3];
}

###################
#	Functions
###################


#############################################
#	Erase DomainCatalog Entry
#############################################

function EraseDomainCatalogEntry($ID)
{

	$status = "not_init";
	echo "\n",'Erasing Domain Catalog Entry: ', $ID,"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	try 
	{	
		$query = "delete from domainCatalog where id = '$ID'";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}

#############################################
#	Update TicketCatalog Entry
#############################################

function UpdateTicketCatalogEntry($ID)
{

	$status = "not_init";
	echo "\n",'Updating ticket Entry: ', $ID,"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query = "UPDATE ticketCatalog  SET ProcessStatus = 'NO', ErrorDescription = '' where id = '$ID'";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}

#############################################
#	Update All TicketCatalog Entries
#############################################

function UpdateAllTicketCatalogEntries()
{

	$status = "not_init";
	echo "\n",'Updating all ticket Entries: ',"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query = "UPDATE ticketCatalog  SET ProcessStatus = 'NO', ErrorDescription = ''";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}

#############################################
#	Delete TicketCatalog Entry
#############################################

function DeleteTicketCatalogEntry($ID)
{

	$status = "not_init";
	echo "\n",'Deleting ticket Entry: ', $ID,"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query = "delete from ticketCatalog  where id = '$ID'";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}

#############################################
#	Delete All TicketCatalog Entries
#############################################

function DeleteAllTicketCatalogEntries()
{

	$status = "not_init";
	echo "\n",'Deleting all ticket Entries: ',"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	try 
	{	
		$query = "Delete from ticketCatalog  WHERE ProcessStatus = 'FAILED'";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}

#############################################
#	Restore requestCatalog
#############################################

function RestoreDomainCatalog()
{

	$status = "not_init";
	echo "\n",'Restoring Domain Catalog: ',"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## Delete the existent entries, then create valid entries
	try 
	{	
		$status = "ok";	
		$query = "Delete from domainCatalog ";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into domainCatalog (UserCompany,domain) VALUES('BKC','bkglobal.corp.whopper.com')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into domainCatalog (UserCompany,domain) VALUES('TH','tdlgc.thi.local')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}	
		$query = "Insert into domainCatalog (UserCompany,domain) VALUES('RBI','bkglobal.corp.whopper.com;tdlgc.thi.local')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
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
#	Add domainCatalog Entry
#############################################

function AddDomainCatalogEntry($company, $domain)
{

	$status = "not_init";
	echo "\n",'Adding Domain Catalog Entry ',"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query =  "Insert into domainCatalog With (ROWLOCK)(UserCompany,domain) SELECT '$company','$domain'  WHERE not exists (select UserCompany from domainCatalog where UserCompany = '$company')"; #Validate that it doesnt exist
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		$status = "ok";	
	}
	catch (Exception $e)
	{
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";	
	}
	
	return $status;
}


#############################################
#	Backup Historic Entries and Delete Them
#############################################

function BackupCatalogEntries()
{

	$status = "not_init";
	echo "\n",'Backing up historics',"\n";
	
	## Database Settings
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	try 
	{	
	
		## backup TransactionLog
		$query = "select * from TransactionLog";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		
		$outCSV =  "ticket" .  "," . "ProcessType" . "," . "status" . "," . "process_date". "," . "description" . "," . "LoginId" . "," . "UserCompany" . "," . "ADGroup";
		while( $obj = sqlsrv_fetch_object( $stmt )) 
		{
			$ticket = $obj->ticket;
			if (empty($ticket)) 
			{
				$ticket = " ";
			}
			$ProcessType = $obj->ProcessType;
			if (empty($ProcessType)) 
			{
				$ProcessType = " ";
			}
			$status = $obj->status;
			if (empty($status)) 
			{
				$status = " ";
			}
			$process_date = $obj->process_date;
			if (empty($process_date)) 
			{
				$process_date = " ";
			}
			$description = $obj->description;

			strtr($description, array('.' => ' ', ',' => ' '));
			$description = preg_replace('/[.,]/', '', $description);
			str_replace(',', '', $description);
			str_replace(',', '', $description);
			if (empty($description)) 
			{
				$description = " ";
			}
			$LoginId = $obj->LoginId;
			if (empty($LoginId)) 
			{
				$LoginId = " ";
			}
			$UserCompany = $obj->UserCompany;
			if (empty($UserCompany)) 
			{
				$UserCompany = " ";
			}
			$ADGroup = $obj->ADGroup;
			if (empty($ADGroup)) 
			{
				$ADGroup = " ";
			}
			
			$outCSV .= "\n" . $ticket .  "," . $ProcessType . "," . $status . "," . $process_date->format('c') . "," . $description . "," . $LoginId . "," . $UserCompany . "," . $ADGroup;	
		}
		
		## print results to file
		$file = fopen("C:/ADGroups_bk/TransactionLog.csv","w");
		echo fwrite($file,$outCSV);
		fclose($file);
		
		## Delete TransactionLog entries
		$query = "delete from TransactionLog";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}

		## Delete ticketCatalog entries
		$query2 = "delete from ticketCatalog";
		$stmt = sqlsrv_query( $conn, $query2);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}		
		
		
		$status = "ok";	
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


echo "Operation: ", $operation;


#############################
## Operations for Web Page
#############################


## Erases a given entry from the domainCatalog DB
if ($operation == "erasedomaincatalogid")
{
	$execute = EraseDomainCatalogEntry($ID);
	echo "\n", 'Status for EraseDomainCatalogEntry: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/domainCatalog.php"); # Redirect browser
	exit;
}

## Updates a given entry from the ticketCatalog DB
if ($operation == "updateticketcatalogid")
{
	$execute = UpdateTicketCatalogEntry($ID);
	echo "\n", 'Status for UpdateTicketCatalogEntry: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Updates all entries from the ticketCatalog DB
if ($operation == "updateallticketcatalog")
{
	$execute = UpdateAllTicketCatalogEntries();
	echo "\n", 'Status for UpdateAllTicketCatalogEntries: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Deletes a given entry from the ticketCatalog DB
if ($operation == "deleteticketcatalogid")
{
	$execute = DeleteTicketCatalogEntry($ID);
	echo "\n", 'Status for DeleteTicketCatalogEntry: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Deletes all entries from the ticketCatalog DB
if ($operation == "deleteallticketcatalog")
{
	$execute = DeleteAllTicketCatalogEntries();
	echo "\n", 'Status for DeleteAllTicketCatalogEntries: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Restores all original entries from the domainCatalog DB
if ($operation == "restoredomain")
{
	$execute = RestoreDomainCatalog();
	echo "\n", 'Status for RestoreDomainCatalog: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/domainCatalog.php"); # Redirect browser
	exit;
}

## Adds an entry to the domainCatalog DB
if ($operation == "adddomain")
{
	$execute = AddDomainCatalogEntry($_company, $_domain);
	echo "\n", 'Status for AddDomainCatalogEntry: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/domainCatalog.php"); # Redirect browser
	exit;
}

## Backups historic entries and deletes all entries
if ($operation == "backupcatalog")
{
	$execute = BackupCatalogEntries();
	echo "\n", 'Status for BackupCatalogEntries: ',  $execute, "\n";
	header("Location: http://bkdalutlwp04/admin/pages/tables/datas.php"); # Redirect browser
	exit;
}


########################################################
#	Unit Testing Examples
########################################################

## Tests Adding an entry to the domainCatalog DB
#if ($operation == "adddomain")
#{
#	$test = AddDomainCatalogEntry("1", "2");
#	echo "\n", 'Status for AddDomainCatalogEntry: ',  $test, "\n";
#	header("Location: http://bkdalutlwp04/admin/pages/tables/appCatalog.php"); # Redirect browser
#	exit;
#}

?>

</body>
</html> 


