<html>
<body>

<h1>WSDL communicator</h1>

<?php

########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 03-06-2017  
# EDIT     : 03-27-2017 
# COMMENTS : This script communicates with the WSDL.
# VERSION  : 0.99B
########################################################### 




####################
# Var definitions
####################

if ($_GET) {
    $operation = $_GET['operation'];
    $ticket = $_GET['ticket'];
	$ID = $_GET['ID'];
	$_application = $_GET['application'];
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
#	Get ApplicationRequested Catalog
#############################################

function GetAppReqCatalog()
{
	
	$status = "not_init";
	echo "\n",'Getting appCatalog values',"\n";
	
	try
	{
		## Database Settings
		$serverName = "<server>";
		$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
		## Establishes the connection
		$conn = sqlsrv_connect($serverName, $connectionOptions);

		## Test Connection
		if ($conn)
		{
			echo "connected";
		}
		else
		{
			die(print_r(sqlsrv_errors(), true));
		}
		
		## Prepare the request
		$query =  "select * from requestCatalog";
		## Execute request
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			 die( print_r( sqlsrv_errors(), true));
		}
		
		## Fetch information for each type of request
		while( $obj = sqlsrv_fetch_object( $stmt )) 
		{
			$ApplicationRequestedq = $obj->ApplicationRequested;
			$TicketTypeq = $obj->TicketType;
			$Category = $obj->Category;
			$application = $obj->application;
			#################################################################
			#							CALLBACK
			#	Call function to get info from WSDL for each type of request
			#################################################################
			$execute = GetWsdlInfo($ApplicationRequestedq,$TicketTypeq,$Category,$application);
			echo "\n", 'Status for GetWsdlInfo: ',  $execute, "\n";
			
			if ($test == "failed")
			{
				$status = "failed";
			}			
		}
		if ($status != "failed")
		{
			$status = "ok";
		}	
	}		
	catch (Exception $e)
	{
		
		echo '[ERROR] Could not process the appCatalog information, please review the DataBase, a notification will be sent';
		echo "\n",'Caught exception: ',  $e->getMessage(), "\n";
		$status = "failed";		
	}
	return $status;
}	

#############################################
#	Get WSDL Info
#############################################

function GetWsdlInfo($ApplicationRequestedq, $TicketTypeq, $Categoryq, $Applicationq)
{
	
	$status = "not_init";
	echo "\n",'Getting WSDL values',"\n";
	
	$dateQ = date("m/d/Y h:i:sa");
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## WSDL settings
	$wsdl='<wsdl url>'; #Note that this address might change
	$username="wsdl";
	$password="wsdl";

	$opts = array(
		'ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false)
	);
	$options = array(
		 'login' => $username,
		 'password' => $password,
		 'encoding' => 'UTF-8', 
		 'verifypeer' => false, 
		 'verifyhost' => false, 
		 'soap_version' => SOAP_1_2, 
		 'trace' => 1, 
		 'exceptions' => 1, 
		 "connection_timeout" => 180, 
		 'stream_context' => stream_context_create($opts)
	);
	
	## Extract WSDL information
	try
	{
		## Declare client for WSDL 
		$client = new SoapClient($wsdl, $options);

		## Create request parameters 
		$ticket_param = array( 'model' => array(
					'keys' => '',
					'instance' =>
							array(
							'AccountDeactFlag' => false,
							'header'   		   =>  array(
													'Open' => 'true',
													'TicketType' => $TicketTypeq,
													'ApprovalStatus' => 'approved'),
							'middle'           =>  array(
													'ApplicationRequested' => $ApplicationRequestedq),
							'close'            => ''				
								)
							)
						);						
		## Create request parameters for box
		$ticket_param_box = array( 'model' => array(
					'keys' => '',
					'instance' =>
							array(
							'AccountDeactFlag' => false,
							'header'   		   =>  array(
													'Open' => 'true',
													'TicketType' => $TicketTypeq,
													'AssignmentGroup' => 'RBI SERVERS WINTEL',
													'Category' => $Categoryq,
													'ApprovalStatus' => 'approved'),
							'middle'           =>  array(
													'ApplicationRequested' => ''),												
							'close'            => ''				
								)
							)
						);		
															
		## Call the service, passing the parameters and the name of the operation 
		if ($TicketTypeq == "APPLICATION")
		{
			$tickets = $client->RetrieveTaskApplicationsKeysList($ticket_param);
		}
		else if (($TicketTypeq == "BOX ADD") or ($TicketTypeq == "BOX UPDATE"))
		{	
			$tickets = $client->RetrieveTaskApplicationsKeysList($ticket_param_box);
		}		

		## Retrieve tickets
		for ($n = 0; $n < sizeof($tickets->keys); $n++)
		{
			$ticketN = $tickets->keys[$n]->TaskID->_;
			
			## Define paramether Array
			$key_param = array(
					'keys' => array('TaskID' => $ticketN));
					
			## Call method in order to obtain a given ticket info		
			$result = $client->RetrieveTaskApplicationsList($key_param);	

			## Retrieve Ticket values
			
			print_r ('<br />');
			$TaskID = ($result->instance->header->TaskID->_);
			print_r ("\xA".$TaskID.'<br />');
			$Reason = ($result->instance->header->Reason->_);
			print_r ("\xA".$Reason.'<br />');	
			$AssignmentGroup = ($result->instance->header->AssignmentGroup->_);
			print_r ("\xA".$AssignmentGroup.'<br />');		
			$OpenTime = new \DateTime(($result->instance->header->OpenTime->_));
			$OpenTimeS = date('Y-m-d H:i:s',strtotime($OpenTime->format('c')));		
			print_r ("\xA".$OpenTime->format('c').'<br />');	
			$Open = ($result->instance->header->Open->_);
			print_r ("\xA".$Open.'<br />');	
			$UserCompany = ($result->instance->UserCompany->_);
			print_r ("\xA".$UserCompany.'<br />');	
			$ApplicationRequested = ($result->instance->middle->ApplicationRequested->_);
			print_r ("\xA".$ApplicationRequested.'<br />');	
			$ClosureCode = "";
			## This field is only for APPLICATION types
			if ($TicketTypeq == "APPLICATION")
			{
				$ClosureCode = ($result->instance->close->ClosureCode->_);
				print_r ("\xA".$ClosureCode.'<br />');
			}	
			$LoginId = ($result->instance->LoginId->_);
			print_r ("\xA".$LoginId.'<br />');	
			$FullName = ($result->instance->FullName->_);
			print_r ("\xA".$FullName.'<br />');	
			$Email = ($result->instance->Email->_);
			print_r ("\xA".$Email.'<br />');
			print_r ('<br />');
			
			## If ticket includes this field, insert to DB
			if (($UserCompany) and ($ApplicationRequested) and ($LoginId) and ($Applicationq) and ($Reason))
			{
				$query =  "Insert into ticketCatalog With (ROWLOCK) (TaskID,Reason,application,AssignmentGroup,TicketOpn,UserCompany,ApplicationRequested,LoginId,FullName,Email,ClossC,ProcessStatus,OpenTime) SELECT '$TaskID','$Reason','$Applicationq','$AssignmentGroup','$Open','$UserCompany','$ApplicationRequested','$LoginId','$FullName','$Email','$ClosureCode','NO','$OpenTimeS' WHERE not exists (select TaskID from ticketCatalog where TaskID = '$TaskID')"; 
				$stmt = sqlsrv_query( $conn, $query);
				if( $stmt === false ) 
				{
					 die( print_r( sqlsrv_errors(), true));
				}
				$status = "ok";
				echo "\n", 'Processed Ticket: ',  $TaskID, "\n";
				$description = "Processed Ticket into Database";
				$query =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,LoginId,UserCompany,ADGroup,ProcessType) SELECT '$TaskID','$status','$dateQ','$description','$LoginId','$UserCompany','$AssignmentGroup','$description'";
				$stmt = sqlsrv_query( $conn, $query);
				if( $stmt === false ) 
				{
					 die( print_r( sqlsrv_errors(), true));
				}			
			}
			else
			{
				$status = "failed";
				echo "\n", 'Failed Ticket: ',  $TaskID, "\n";
				$description = "Got Malformed Ticket from WSDL. Please review this ticket manually";
				$processType = "Malformed Ticket from WSDL";
				$query =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,LoginId,UserCompany,ADGroup,ProcessType) SELECT '$TaskID','$status','$dateQ','$description','$LoginId','$UserCompany','$AssignmentGroup','$processType'";
				$stmt = sqlsrv_query( $conn, $query);
				if( $stmt === false ) 
				{
					 die( print_r( sqlsrv_errors(), true));
				}	
			}
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
#	Close WSDL Ticket
#############################################

function CloseWsdlTicket($Ticketq)
{
	
	$dateQ = date("m/d/Y h:i:sa");
	$dateR = date("m-d-YH:i:s");
	$datein = date('c');
	$status = "not_init";
	echo "\n",'Closing Ticket: ', $Ticketq,"\n";
	
	$tmx = new \DateTime($datein);
	echo "\n",'timx: ', $tmx->format('c'),"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## WSDL settings
    $wsdl='<wsdl site>'; #Note that this address might change
	$username="wsdl";
	$password="wsdl";
	$opts = array(
		'ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false)
	);
	$options = array(
		 'login' => $username,
		 'password' => $password,
		 'trace'=>1
	);

	## Extract WSDL information
	try
	{
		
		## Declare client for WSDL 
		$client = new SoapClient($wsdl, $options);

		## create request parameters 
		$ticket_param = array( 'model' => array(
					'keys' => array('TaskID' => $Ticketq),
					'instance' =>
							array(
							'AccountDeactFlag' => true,
							'CloseTime'        => $tmx ,
							'header'   		   =>  array(
													'Open' => false,
													'Assignee' => 'automation'),
							'middle'           =>  '',
							'close'            => array(
													'ClosureCode' => 1,
													'ClosureComments' => array(
													'ClosureComments' => 'ADGroups Automation closed Ticket'))				
								)
							)
						);						
		
		## Call the service, passing the parameters and the name of the operation 				
		$tickets = $client->UpdateTaskApplications($ticket_param);

		$CloseTimer = ($tickets->model->instance->header->CloseTime->_);
		echo "CloseTime: ";
		print_r ("\xA".$CloseTimer.'<br />');

		$Open = ($tickets->model->instance->header->Open->_);	
		$TaskStatus = ($tickets->model->instance->TaskStatus->_);
		echo "Task Status: ";
		print_r ("\xA".$TaskStatus.'<br />');
		
		if (($TaskStatus == "CLOSED") and ($Open == false))
		{
			$status = "ok";	
			$query = "UPDATE ticketCatalog  SET ProcessStatus = 'CLOSED' where TaskID = '$Ticketq'";
			$stmt = sqlsrv_query( $conn, $query);
			if( $stmt === false ) 
			{
				 die( print_r( sqlsrv_errors(), true));
			}
			$description = "Ticket Closed";
			$query2 =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,ProcessType) SELECT '$Ticketq','$status','$dateQ','$description','$description'";
			$stmt = sqlsrv_query( $conn, $query2);
			if( $stmt === false ) 
			{
				 die( print_r( sqlsrv_errors(), true));
			}	
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
#	Erase AppCatalog Entry
#############################################

function EraseAppCatalogEntry($ID)
{

	$status = "not_init";
	echo "\n",'Erasing App Catalog Entry: ', $ID,"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query = "delete from appCatalog where id = '$ID'";
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
#	Erase ReqCatalog Entry
#############################################

function EraseReqCatalogEntry($ID)
{

	$status = "not_init";
	echo "\n",'Erasing Req Catalog Entry: ', $ID,"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	try 
	{	
		$query = "delete from requestCatalog where id = '$ID'";
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
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
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
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
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
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
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
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
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

function RestoreRequestCatalog()
{

	$status = "not_init";
	echo "\n",'Restoring Request Catalog: ',"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## Delete the existent entries, then create valid entries
	try 
	{	
		$status = "ok";	
		$query = "Delete from requestCatalog ";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('TABLEAU','TABLEAU: TABLEAU BKC - type: View','APPLICATION','')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('TABLEAU','TABLEAU: TABLEAU TH - type: View','APPLICATION','')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('ECHOSIGN','ECHOSIGN: New user - type: View','APPLICATION','')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}	
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('ECHOSIGN','ECHOSIGN: Transfer access - type: View','APPLICATION','')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('BOX','','BOX ADD','BOX ADD REQUEST')"; 
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}	
		$query = "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('BOX','','BOX UPDATE','BOX UPDATE REQUEST')"; 
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
#	Restore applicationCatalog
#############################################

function RestoreApplicationCatalog()
{

	$status = "not_init";
	echo "\n",'Restoring Application Catalog: ',"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## Delete the existent entries, then create valid entries
	try 
	{	
		$status = "ok";	
		$query = "Delete from appCatalog ";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('BOX','BOX','RBI SERVERS WINTEL','box_app_a_s','BKC','bkglobal.corp.whopper.com','')";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('BOX','BOX','RBI SERVERS WINTEL','BOX_TH_APP_A_S','TH','tdlgc.thi.local','')";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('ECHOSIGN','New User','RBI SERVICE DESK','EchoSign_BKC_APP_A_S','BKC','bkglobal.corp.whopper.com','APPLICATION')";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('ECHOSIGN','New User','RBI SERVICE DESK','Echosign_TDL_APP_A_S','TH','tdlgc.thi.local','APPLICATION')";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('TABLEAU','TABLEAU BKC','RBI BI INFRASTRUCTURE','tableau_app_a_s','BKC','bkglobal.corp.whopper.com','APPLICATION')";
		$stmt = sqlsrv_query( $conn, $query);
		if( $stmt === false ) 
		{
			$status = "failed";	
			die( print_r( sqlsrv_errors(), true));
		}
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('TABLEAU','TABLEAU TH','RBI BI INFRASTRUCTURE','tableau_tdl_app_a_s','TH','tdlgc.thi.local','APPLICATION')";
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
#	Add requestCatalog Entry
#############################################

function AddRequestCatalogEntry($application, $apprequested, $tickettype, $category)
{

	$status = "not_init";
	echo "\n",'Adding Req Catalog Entry ',"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	
	try 
	{	
		$query =  "Insert into requestCatalog (application,ApplicationRequested,TicketType,Category) VALUES('$application','$apprequested','$tickettype','$category')"; 
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
#	Add ApplicationCatalog Entry
#############################################

function AddApplicationCatalogEntry($application, $app_name, $assignment_group, $ADGroup, $UserCompany, $domain, $TicketType)
{

	$status = "not_init";
	echo "\n",'Adding App Catalog Entry ',"\n";
	
	## Database Settings
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	try 
	{	
		$query =  "Insert into appCatalog (application,app_name,assignment_group,ADGroup,UserCompany,domain,TicketType) VALUES('$application','$app_name','$assignment_group','$ADGroup','$UserCompany','$domain','$TicketType')"; 
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
	$serverName = "<server>";
	$connectionOptions = array(
		"Database" => "<database>",
		"Uid" => "<id>",
		"PWD" => "<password>"
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
		$file = fopen("C:/logs/TransactionLog.csv","w");
		echo fwrite($file,$outCSV);
		fclose($file);
		
		## backup TransactionLog
		$query = "delete from TransactionLog";
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

	
########################################################
#	Execution flux
########################################################


echo "Operation: ", $operation;

#############################
## Operations for Powershell
#############################

## Gets Tickets from HPSM via WSDL
if ($operation == "getwsdl")
{
	$execute = GetAppReqCatalog();
	echo "\n", 'Status for GetAppReqCatalog: ',  $execute, "\n";
}

## Closes Tickets from HPSM via WSDL
if ($operation == "closeticket")
{
	$execute = CloseWsdlTicket($ticket);
	echo "\n", 'Status for CloseWsdlTicket: ',  $execute, "\n";
}

#############################
## Operations for Web Page
#############################

## Erases a given entry from the appCatalog DB
if ($operation == "eraseappcatalogid")
{
	$execute = EraseAppCatalogEntry($ID);
	echo "\n", 'Status for EraseAppCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/appCatalog.php"); # Redirect browser 
	exit;
}

## Erases a given entry from the requestCatalog DB
if ($operation == "erasereqcatalogid")
{
	$execute = EraseReqCatalogEntry($ID);
	echo "\n", 'Status for EraseReqCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/requestCatalog.php"); # Redirect browser
	exit;
}

## Updates a given entry from the ticketCatalog DB
if ($operation == "updateticketcatalogid")
{
	$execute = UpdateTicketCatalogEntry($ID);
	echo "\n", 'Status for UpdateTicketCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Updates all entries from the ticketCatalog DB
if ($operation == "updateallticketcatalog")
{
	$execute = UpdateAllTicketCatalogEntries();
	echo "\n", 'Status for UpdateAllTicketCatalogEntries: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Deletes a given entry from the ticketCatalog DB
if ($operation == "deleteticketcatalogid")
{
	$execute = DeleteTicketCatalogEntry($ID);
	echo "\n", 'Status for DeleteTicketCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Deletes all entries from the ticketCatalog DB
if ($operation == "deleteallticketcatalog")
{
	$execute = DeleteAllTicketCatalogEntries();
	echo "\n", 'Status for DeleteAllTicketCatalogEntries: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/locked.php"); # Redirect browser
	exit;
}

## Restores all original entries from the requestCatalog DB
if ($operation == "restorerequest")
{
	$execute = RestoreRequestCatalog();
	echo "\n", 'Status for RestoreRequestCatalog: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/requestCatalog.php"); # Redirect browser
	exit;
}

## Restores all original entries from the appCatalog DB
if ($operation == "restoreapplication")
{
	$execute = RestoreApplicationCatalog();
	echo "\n", 'Status for RestoreApplicationCatalog: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/appCatalog.php"); # Redirect browser
	exit;
}

## Adds an entry to the requestCatalog DB
if ($operation == "addrequest")
{
	$execute = AddRequestCatalogEntry($_application, $_apprequested, $_tickettype, $_category);
	echo "\n", 'Status for AddRequestCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/requestCatalog.php"); # Redirect browser
	exit;
}

## Adds an entry to the appCatalog DB
if ($operation == "addapplication")
{
	$execute = AddApplicationCatalogEntry($_application, $_app_name, $_assignment_group, $_ADGroup, $_UserCompany, $_domain, $_tickettype);
	echo "\n", 'Status for AddApplicationCatalogEntry: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/appCatalog.php"); # Redirect browser
	exit;
}

## Backups historic entries and deletes all entries
if ($operation == "backupcatalog")
{
	$execute = BackupCatalogEntries();
	echo "\n", 'Status for BackupCatalogEntries: ',  $execute, "\n";
	header("Location: http://<server>/admin/pages/tables/datas.php"); # Redirect browser
	exit;
}


########################################################
#	Unit Testing Examples
########################################################

## Tests Adding an entry to the appCatalog DB
#if ($operation == "testaddapplication")
#{
#	$test = AddApplicationCatalogEntry("1", "2", "3", "4", "5", "6", "7");
#	echo "\n", 'Status for AddApplicationCatalogEntry: ',  $test, "\n";
#	header("Location: http://<server>/admin/pages/tables/appCatalog.php"); # Redirect browser
#	exit;
#}

?>

</body>
</html> 


