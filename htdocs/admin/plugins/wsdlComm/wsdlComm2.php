<html>
<body>

<h1>WSDL communicator</h1>

<?php

########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 03-06-2017  
# EDIT     : 05-23-2017 
# COMMENTS : This script communicates with the WSDL.
# VERSION  : 1.59RC2
########################################################### 


####################
# Var definitions
####################

if ($_GET) 
{
    $operation = $_GET['operation'];
    $ticket = $_GET['ticket'];	
} 
else 
{
    $operation = $argv[1];
    $ticket = $argv[2];
}

###################
#	Functions
###################

#############################################
#	Get WSDL Info
#############################################

function GetWsdlInfo()
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

    $wsdl='http://<wsdl with port ip>/SM/7/TaskApplications.wsdl'; #Note that this address might change
	$username="wsdl";
	$password="<wsdl password>";

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
							'header'   		   =>  array(
													'AssignmentGroup' => 'RBI AUTOMATION',
													'Open' => true,
													'TicketType' => 'APPLICATION',
													'ApprovalStatus' => 'approved'),
							'AutomationFlag'   =>  'AUTOMATION',						
							'AccountDeactFlag ' => false,
							'middle'            => '',	
							'close'            => ''				
								)
							)
						);						
	
															
		## Call the service, passing the parameters and the name of the operation 
		$tickets = $client->RetrieveTaskApplicationsKeysList($ticket_param);

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
				$AssignmentGroup = ($result->instance->header->AssignmentGroup->_);
				print_r ("\xA".$AssignmentGroup.'<br />');		
				$OpenTime = new \DateTime(($result->instance->header->OpenTime->_));
				$OpenTimeS = date('Y-m-d H:i:s',strtotime($OpenTime->format('c')));		
				print_r ("\xA".$OpenTime->format('c').'<br />');	
				$Open = ($result->instance->header->Open->_);
				print_r ("\xA".$Open.'<br />');	
				$ApplicationRequested = ($result->instance->middle->ApplicationName->_);
				print_r ("\xA".$ApplicationRequested.'<br />');					
				$UserCompany = ($result->instance->ADGroupsDomains->_);
				print_r ("\xA".$UserCompany.'<br />');	
				$LoginId = ($result->instance->LoginId->_);
				print_r ("\xA".$LoginId.'<br />');	
				$ADGroups = ($result->instance->ADGroups->_);
				print_r ("\xA".$ADGroups.'<br />');
				
				## Validate number of groups against number of domains
				$groupQ = substr_count($ADGroups,';') + 1;
				$domainQ = substr_count($UserCompany,';') + 1;
				print_r ("\xA"." Number of groups: ".$groupQ." Number of domains: ".$domainQ.'<br />');
				print_r ('<br />');
				
				#############################################
				#	Temporal conditional for specific ticket
				#############################################			
				
				##if ($TaskID == "T146088") # FIXME TEMPORAL
				if (true)
				{
					
					## If ticket includes these fields, insert into DB
					if (($TaskID) and ($UserCompany) and ($ApplicationRequested) and ($LoginId) and ($ADGroups) and ($Open) and ($groupQ == $domainQ))  
					{
						$query =  "Insert into ticketCatalog With (ROWLOCK) (TaskID,AssignmentGroup,UserCompany,ApplicationRequested,LoginId,ProcessStatus,OpenTime) SELECT '$TaskID','$ADGroups','$UserCompany','$ApplicationRequested','$LoginId','NO','$OpenTimeS' WHERE not exists (select TaskID from ticketCatalog where TaskID = '$TaskID')"; 
						$stmt = sqlsrv_query( $conn, $query);
						if( $stmt === false ) 
						{
							print_r( sqlsrv_errors(), true);
						}
						$status = "ok";
						echo "\n", 'Processed Ticket: ',  $TaskID, "\n";
						$description = "Processed Ticket into Database";
						$query =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,LoginId,UserCompany,ADGroup,ProcessType) SELECT '$TaskID','$status','$dateQ','$description','$LoginId','$UserCompany','$ADGroups','$description'";
						$stmt = sqlsrv_query( $conn, $query);
						if( $stmt === false ) 
						{
							print_r( sqlsrv_errors(), true);
						}			
					}
					else
					{
						$status = "not_init";
						if ($TaskID)
						{
							$status = "failed";
							echo "\n", 'Failed Ticket: ',  $TaskID, "\n";
							$description = "Got Malformed Ticket from WSDL. Please review this ticket manually";
							$processType = "Malformed Ticket from WSDL";
							$query =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,LoginId,UserCompany,ADGroup,ProcessType) SELECT '$TaskID','$status','$dateQ','$description','$LoginId','$UserCompany','$ADGroups','$processType'";
							$stmt = sqlsrv_query( $conn, $query);
							if( $stmt === false ) 
							{
								print_r( sqlsrv_errors(), true);
							}	
						}
					}
				
				} 
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
	$wsdl='http://3<wsdl ip with port>/SM/7/TaskApplications.wsdl'; #Note that this address might change
	$username="wsdl";
	$password="<wsdl password>";
	
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
							'header'   		   =>  array(
													'Open' => false,
													'Assignee' => 'automation',
													'Area' => 'AD GROUPS AUTOMATION',
													'Subarea' => 'ADD USER TO AD GROUPS'),
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
				print_r( sqlsrv_errors(), true);
			}
			$description = "Ticket Closed";
			$query2 =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,ProcessType) SELECT '$Ticketq','$status','$dateQ','$description','$description'";
			$stmt = sqlsrv_query( $conn, $query2);
			if( $stmt === false ) 
			{
				print_r( sqlsrv_errors(), true);
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
	$execute = GetWsdlInfo();
	echo "\n", 'Status for GetWsdlInfo: ',  $execute, "\n";
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


