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
		$serverName = "RBDALSQLWS01";
		$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
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
		"PWD" => "<password>"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## WSDL settings
	$wsdl='http://<wsdl site with port>/SM/7/TaskApplications.wsdl'; #Note that this address might change
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
		if (sizeof($tickets->keys) >= 1)
		{
			for ($n = 0; $n < sizeof($tickets->keys); $n++)
			{
				if (sizeof($tickets->keys) > 1)
				{	
					$ticketN = $tickets->keys[$n]->TaskID->_;
				}
				else
				{
					$ticketN = $tickets->keys->TaskID->_;
				}
				
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
				$ApplicationRequested = ($result->instance->middle->ApplicationRequested->_);
				print_r ("\xA".$ApplicationRequested.'<br />');					
				$UserCompany = ($result->instance->UserCompany->_);
				print_r ("\xA".$UserCompany.'<br />');	
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
				
				#############################################
				#	Temporal conditional for specific ticket
				#############################################			
				
				if ($TaskID == "T101777") # FIXME TEMPORAL
				##if (true)
				{
					
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
				
				} # FIXME TEMPORAL
			#############################################
			#	Temporal conditional for specific ticket
			#############################################			
			}
		}
		else
		{
			echo "\n", 'No tickets found for: ', $Applicationq,  "\n";
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
	$serverName = "RBDALSQLWS01";
	$connectionOptions = array(
		"Database" => "STK_OpsCenterMonitor",
		"Uid" => "opscenteradmin",
		"PWD" => "Pr1.m4n4g3_ops7"
		);
		
	## Establishes the connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);

	## WSDL settings
    $wsdl='http://<wsdl with port>/SM/7/TaskApplications.wsdl'; #Note that this address might change
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

?>

</body>
</html> 


