########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 03-06-2017  
# EDIT     : 05-23-2017 
# COMMENTS : This script adds Active Directory users, 
#            to specific groups taken from a WSDL.
# VERSION  : 1.55RC2
<#
CHANGES : 
			Initial Version
#>
########################################################### 


#############################################
#	Colour for verbose
#############################################

function Print-Me()
{

	Param( [parameter(Mandatory = $true, ValueFromPipeline = $true)] $logEntry,
		[switch]$displaygreen,
		[switch]$error,
		[switch]$warning,
		[switch]$displaynormal,
		[switch]$displayblue
	)

	if ($error) 
	{ 
		Write-Host "$logEntry" -Foregroundcolor Red; 
	}

	elseif ($warning) 
	{ 
		Write-Host "$logEntry" -Foregroundcolor Yellow;
	}

	elseif ($displaynormal) 
	{ 
		Write-Host "$logEntry" -Foregroundcolor White; 
	}

	elseif ($displaygreen) 
	{ 
		Write-Host "$logEntry" -Foregroundcolor Green;  
	}
	
		elseif ($displayblue) 
	{ 
		Write-Host "$logEntry" -Foregroundcolor Blue;  
	}

	else 
	{ 
		Write-Host "$logEntry";
	}

}

###################
#	Log File
###################

$now = Get-Date -format "dd/MM/yy"
$dateLog = Get-Date -format "dd_MM_yy_H_m_s"
$date = Get-Date
$logOut = 'c:\logs\set_ABI_AD_grp_'
$logOut += $dateLog
$logOut += '.log'

"----------------------------------------------------------------------------------------------------------------------------" | out-file $logOut -append
"---------------------------------------		$date		---------------------------------------" | out-file $logOut -append

##################################
#	Add-ons and Modules
##################################


#########################	DataTools Load	########################
try
{
	'Loading DataTools Module' | Print-Me -displaynormal
	$status = "not_init"
	## Imports DataTools module for Database manipulation
	Import-Module DataTools
	$status = "ok"
}
catch
{
	$ErrorMessage = $_.Exception.Message
	$FailedItem = $_.Exception.ItemName
	"[FATAL ERROR] DataTools Module couldn't be loaded. Script will stop!" | Print-Me -error
	$status = "failed"
}
Finally	
{
	Write-host 'Status for loading DataTools Module: '$status
	"This script attempted to Load DataTools Module at $date with status: $status" | out-file $logOut -append
	if($ErrorMessage)
	{
		"Error message: $ErrorMessage" | out-file $logOut -append
	}
	if($FailedItem)
	{
		"Failed Item: $FailedItem" | out-file $logOut -append
	}	
}
########################################################################

#########################	Active Directory Load	########################
try
{
	'Loading Active Directory Module' | Print-Me -displaynormal
	$status = "not_init"
	## Imports Active Directory module for PowerShell
	Import-Module activedirectory
	$status = "ok"	
}
catch
{
	$ErrorMessage = $_.Exception.Message
	$FailedItem = $_.Exception.ItemName
	"[FATAL ERROR] ActiveDirectory Module couldn't be loaded. Script will stop!"  | Print-Me -error
	$status = "failed"
}
Finally	
{
	Write-host 'Status for loading Active Directory Module: '$status
	"This script attempted to Load Active Directory Module at $date with status: $status" | out-file $logOut -append
	if($ErrorMessage)
	{
		"Error message: $ErrorMessage" | out-file $logOut -append
	}	
	if($FailedItem)
	{
		"Failed Item: $FailedItem" | out-file $logOut -append	
	}
}
########################################################################

####################
# Var definitions
####################

#########################	WSDL Login	########################

$dumpURI =  "http://<server>/admin/plugins/wsdlComm/wsdlComm2.php?operation=getwsdl&ticket="
$closeURI = "http://<server>/admin/plugins/wsdlComm/wsdlComm2.php?operation=closeticket&ticket="
$failedURI = "http://<server>/admin/pages/tables/locked.php"

########################################################################

#########################	Database SQL Server	########################

[string]$host_DB = "<server>" ## Note that this address might change
[string]$name_DB = "<database>"
[string]$user_DB = "<user>"
[string]$pass_DB = "<password>" 
[string]$conn_DB = "Data Source=$host_DB;Initial Catalog=$name_DB;User Id=$user_DB;Password=$pass_DB" 

########################################################################

#########################	Email Notifications	########################

[string]$emailFrom	   = "<email>"
#[string[]]$emailTo     = "<ADGroupsTransactions@rbi.com>"
[string[]]$emailTo     = "<email1>", "<email2>", "<email3>"
[string]$emailSubjectS = "ADGroups Automation Success"
[string]$emailSubjectF = "ADGroups Automation ERROR"
[string]$emailSubjectM = "ADGroups Automation Malformed ticket"
[string]$emailServer   = "<ip address of por25 server>" ## Note that this address might change

########################################################################

###################
#	Functions
###################

#############################################
#	Check DB Connection
#############################################

function Check-DBConn 
{
    
	$status = "not_init"
	'Checking DB Connection' | Print-Me -displaynormal

 
	## Prepare queries for testing database 
	$query1 =  "SELECT * from domainCatalog"
	$query2 =  "SELECT * from ticketCatalog;"
	$query3 =  "SELECT * from TransactionLog;"
	
	try
	{
		## Tests simple query for each table
		$result1 = Get-DatabaseData -query "$query1" -isSQLServer -connectionString $conn_DB
		$result2 = Get-DatabaseData -query "$query2" -isSQLServer -connectionString $conn_DB
		$result3 = Get-DatabaseData -query "$query3" -isSQLServer -connectionString $conn_DB
		
		if ($result1 -And $result2 -And $result3)
		{
			$status = "ok"
		}
		else 
		{
			$status = "missing_data"
		}
	}

	catch 
	{
	
		'[ERROR] Could not access Database tables' | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"		
	}
	
	Finally	
	{

		"This script attempted to check Database at $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}
		if($FailedItem)
		{		
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status	
}


#############################################
#	Get WSDL Tickets
#############################################

function Get-WsdlTickets 
{
	
	$status = "not_init"
	'Getting WSDL Tickets' | Print-Me -displayblue
	
	try 
	{
		## Setting web client
		$web = New-Object Net.WebClient
		## Calling PHP WSDL communicator in order to save the tickets from HPSM via WSDL to the Database
		$web.DownloadString($dumpURI)
		$web
		## Return status
		if ($web)
		{
			$status = "ok"
		}
		else 
		{
			$status = "missing_data"
		}
	}
	
	catch 
	{

		'[FATAL ERROR] Could not load the WSDL Tickets, please review the Web Service and the web dumper, a notification will be sent' | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"	
	}
	
	Finally	
	{

		"This script attempted to read WSDL tickets at $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status
}

#############################################
#	Close WSDL Tickets
#############################################

function Close-WsdlTickets ([string]$Ticket)
{
	
	$status = "not_init"
	"Closing WSDL Ticket: $Ticket" | Print-Me -displaynormal
	
	try 
	{
		## Setting web client
		$web = New-Object Net.WebClient
		## Calling PHP WSDL communicator in order to close the tickets in HPSM via WSDL 
		$ticketURI = $closeURI 
		$ticketURI += $Ticket
		$web.DownloadString($ticketURI)
		$web
		## Return status
		if ($web)
		{
			$query =  "SELECT ProcessStatus from ticketCatalog where ProcessStatus ='CLOSED' and TaskID = '$Ticket';"
			$results = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB

			if ($results.ProcessStatus -eq "CLOSED")
			{
				$status = "ok"
			}
			else 
			{
				$status = "failed"
			}
		}
		else 
		{
			$status = "missing_data"
		}
	}
	
	catch 
	{
	
		'[ERROR] Could not close the WSDL Tickets, please review the Web Service and the web ticket closing system, a notification will be sent' | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"
	}
	
	Finally	
	{

		"This script attempted to close WSDL ticket: $Ticket at $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status	
}

#############################################
#	Process WSDL Info
#############################################

function Process-WsdlInfo()
{
	
	$status = "not_init"
	'Getting WSDL Tickets Info from DataBase' | Print-Me -displaynormal
	$processedTickets = @() 
	
	try 
	{	
		$status = "ok"
		
		$query =  "SELECT * from ticketCatalog where ProcessStatus ='NO';"
		$results = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB
		$tables = @($results)
		
		## System for processing the Tickets
		foreach($user in $tables)
		{		
			
			if ($user.TaskID)
			{
				## Set an idividual ticket status for validation
				$ticketStatus = "ok"
				
				## Declare variables
				$AssignmentGroup = $user.AssignmentGroup -split ';'
				$UserCompanys = $user.UserCompany -split ';'
				$UserID = $user.LoginId
				$UserTicket = $user.TaskID
				
				## Displays the most important info for each ticket
				Write-host ""
				"---------------------------------------------------------" | Print-Me -displaynormal
				Write-host ""
				"ticket: $UserTicket" | Print-Me -warning
				"user: $UserID" | Print-Me -warning
				"domain: $UserCompanys" | Print-Me -warning
				"Group(s): $AssignmentGroup" | Print-Me -warning
				Write-host ""
				
				## Maintains a record of processed tickets for log purposes
				$processedTickets += $UserTicket
				
				for ($i = 0; $i -lt $AssignmentGroup.length; $i++) 
				{		
					
					#Depending on size of the array we define the domain
					if ($UserCompanys.length -gt 1)
					{
						$groupDomain = $UserCompanys[$i]
					}
					else
					{
						$groupDomain = $UserCompanys
					}
					
					## Translates domain
					$query2 =  "SELECT domain from domainCatalog where UserCompany = '$groupDomain';" 
					$results2 = Get-DatabaseData -query "$query2" -isSQLServer -connectionString $conn_DB
					$UserCompany = $results2.domain -split ';'
					
					## If domain contains subdomains RBI for example
					for ($j = 0; $j -lt $UserCompany.length; $j++) 
					{
					
						#################################################################
						#							CALLBACK
						#	Calls function to Add user to group in ActiveDirectory
						#################################################################
						$callback = Set-UserinGroup $AssignmentGroup[$i] $UserID $UserCompany[$j] $UserTicket 
						Write-host 'Status for callback Set-UserinGroup: '$callback	
						
						## If callback fails then mark ticket as failed
						if ($callback -eq "failed")
						{
							$ticketStatus = "failed"
						}
					}
				}
				
			
				## Validation will be complete if the ticket was properly assessed
				if ($ticketStatus -eq "ok")
				{
					#################################################################
					#							CALLBACK
					#	Calls function to close Ticket
					#################################################################
					$callback = Close-WsdlTickets $UserTicket
					Write-host 'Status for callback Close-WsdlTickets: '$callback
					
					#################################################################
					#							CALLBACK
					#	Calls function to Send Email Notification
					#################################################################
					$callback = Process-EmailNotification $AssignmentGroup $UserID $UserCompanys $UserTicket $ticketStatus $FailedItems $ErrDescription $Msg
					Write-host 'Status for callback Process-EmailNotification: '$callback
				}
			}
		}
	}
	
	catch 
	{
	
		'[ERROR] Could not process WSDL Ticket info, please review the Web Service, a notification will be sent' | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"
	}
	
	Finally
	{

		"This script attempted to process  WSDL ticket(s): $processedTickets . At $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}	
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status
}


#############################################
#	Validate User in Group
#############################################

function Validate-UserinGroup ([string]$Group,[string]$User,[string]$domain,[string]$Ticket,[string]$ADStatus,[string]$ADErr)
{
	$status = "not_init"
	"validating user:$User in group:$Group in server:$domain for ticket:$Ticket" | Print-Me -displaynormal
	
	try 
	{
	
		if (((Get-ADUser $User -Server $domain -Properties memberof).memberof -like "CN=$Group*") -And ($ADStatus -eq "ok"))
		{
			$status = "ok"
		}
		else
		{
			$status = "failed"
		}	
	}
	
	catch 
	{
	
		"[ERROR] Could not validate user:$User in group:$Group in Server:$domain for ticket:$Ticket, please review the user in AD, a notification will be sent" | Print-Me -error
		$ErrorMessages = $_.Exception.Message
		$FailedItems = $_.Exception.ItemName
		$status = "failed"
	}
	
	Finally	
	{

		"This script attempted to validate user:$User in group:$Group in Server:$domain for ticket:$Ticket at $date with status: $status" | out-file $logOut -append
		if($ErrorMessages)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}	
		if($FailedItems)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	if ($ErrorMessages) 
	{

		## Use custom error descriptions
		if($ErrorMessages -like '*Insufficient access rights*')
		{
			$ErrDescription = "Automation System cant process due insufficient access rights in AD"
		}
		elseif (($ErrorMessages -like '*Cannot find an object with identity*') -And ($ErrorMessages -Match $Group) -And ($Group -ne $null))
		{
			$ErrDescription = "AD Group not found"
		}
		elseif (($ErrorMessages -like '*Cannot find an object with identity*') -And ($ErrorMessages -Match $User) -And ($User -ne $null))
		{
			$ErrDescription = "AD User not found"
		}
		elseif ($ErrorMessages -like '*Unable to contact the server*')
		{
			$ErrDescription = "Cannot Access AD Server"
		}
		elseif ($ErrorMessages -like '*The server has rejected the client credentials*')
		{
			$ErrDescription = "Wrong credentials in AD"
		}					
		else
		{
			$ErrDescription = "Unidentified error"
		}

	}	
	else
	{
		if ($status -eq "failed")
		{
			$ErrDescription = "AD User is not in AD Group"
		}	
	}
	
	## Include errors from processing
	$ErrDescription += "`n"
	$ErrDescription += $ADErr
	
	#################################################################
	#							CALLBACK
	#	Calls function to save Process Transaction in DB
	#################################################################
	$callback = Process-Trans $Group $User $domain $Ticket $status $FailedItems $ErrDescription $Msg 'Validate user in AD Group'
	Write-host 'Status for callback Process-Trans: '$callback
	
	## If process has failed notify for each failing item
	if ($status -eq "failed")
	{
		#################################################################
		#							CALLBACK
		#	Calls function to Send Email Notification
		#################################################################
		$callback = Process-EmailNotification $Group $User $domain $Ticket $status $FailedItems $ErrDescription $Msg
		Write-host 'Status for callback Process-EmailNotification: '$callback
	}
		
	return $status		
}

#############################################
#	Process Transaction
#############################################

function Process-Trans ([string]$Group,[string]$User,[string]$domain,[string]$Ticket,[string]$StatusM,[string]$FailedItemM,[string]$ErrorMessageM,[string]$Msg,[string]$ProcessType)
{
	$status = "not_init"
	'Saving Processed Transactions' | Print-Me -displaynormal
	
	try 
	{	
		
		## Replace quotes in order to save in DB
		if ($FailedItemM -Or $ErrorMessageM -Or ($StatusM -eq "failed"))
		{
			$ErrorMessageM = $ErrorMessageM -replace '['']',""
			$description = $ErrorMessageM
		}
		else 
		{
			$description = 'Action properly processed'		
		}
		
		$query =  "Insert into TransactionLog With (ROWLOCK) (ticket,status,process_date,description,LoginId,UserCompany,ADGroup,ProcessType) SELECT '$Ticket','$StatusM','$date','$description','$User','$domain','$Group','$ProcessType';"
		$result1 = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB
		$status = "ok"
	}

	catch 
	{
	
		'[ERROR] Could not save processed Ticket info, please review the DataBase and ActiveDirectory system, a notification will be sent' | Print-Me -displaynormal
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"
	}
	
	Finally
	{

		"This script attempted to save processed Ticket info for Ticket: $Ticket . At $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}	
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status	
}

#############################################
#	Process Email Notification
#############################################

function Process-EmailNotification ([string]$Group,[string]$User,[string]$domain,[string]$Ticket,[string]$StatusM,[string]$FailedItemM,[string]$ErrorMessageM,[string]$Msg)
{
	
	$status = "not_init"
	"Sending Email to:$emailTo from:$emailFrom via server:$emailServer for ticket:$Ticket for user:$User for a $StatusM status" | Print-Me -displaynormal
	$subject = ""
	
	$querym =  "SELECT ApplicationRequested from ticketCatalog where TaskID = '$Ticket';"
	$resultsm = Get-DatabaseData -query "$querym" -isSQLServer -connectionString $conn_DB
	$catalogApp = @($resultsm)
	
	$AppProcessed = $catalogApp.ApplicationRequested
	
	if ($StatusM -eq "ok")
	{
		$subject = $emailSubjectS
		$body = "Administrator, `n`n We succeded to process the following ticket. `n`n Ticket: $Ticket `n Request: $AppProcessed `n User: $User `n Group(s): $Group `n Domain(s): $domain `n Date: $date `n Status: $StatusM  `n`nRegards, `nADGroups Automation System"
	}
	else 
	{
		$subject = $emailSubjectF
		$body = "Administrator, `n`n We failed to process the following ticket. `n`n Ticket: $Ticket `n Request: $AppProcessed `n User: $User `n Group(s): $Group `n Domain(s): $domain `n Date: $date `n Status: $StatusM `n Error message: $ErrorMessageM `nFor more information access: $failedURI `n`nRegards, `nADGroups Automation System"
	}
	
	$ErrorActionPreference = "Stop"  
	try 
	{
		Send-MailMessage -From $emailFrom -To $emailTo -Subject $subject -SmtpServer $emailServer -Body $body
		$status = "ok"
	}
	
	catch 
	{
		"[FATAL ERROR] Could send email for user:$User in group:$Group in Server:$domain for ticket:$Ticket, please review the SMTP Relay configuration, writting in Log" | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"	
	}
	
	Finally	
	{

		"This script attempted to send email for user:$User in group:$Group in Server:$domain for ticket:$Ticket at $date, with status: $status ." | out-file $logOut -append
		if($ErrorMessage)
		{
			"Fatal Error message: $ErrorMessage" | out-file $logOut -append
		}	
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status
}

#############################################
#	Set User in Group
#############################################

function Set-UserinGroup([string]$Group,[string]$User,[string]$domain,[string]$Ticket)
{

	"Setting user: $User in group: $Group at server: $domain for Ticket:$Ticket" | Print-Me -displayblue
	$status = "not_init"
   
   if (($Group -ne $null) -And ($User -ne $null) -And ($domain -ne $null))
   {
		try
		{
			## Add the target user to this group.
			Add-ADGroupMember -Identity $Group -Members $User -Server $domain
			$status = "ok"
		}
		catch 
		{
		
			"[ERROR] Could not process Ticket:$Ticket to set $User in $Group for server $domain, please review the Active Directory and these settings, a notification will be sent" | Print-Me -error
			$ErrorMessage = $_.Exception.Message
			$FailedItem = $_.Exception.ItemName
			$status = "failed"
		}
		
		Finally	
		{

			"This script attempted process Ticket:$Ticket to set $User in $Group for server $domain in Active Directory at $date with status: $status" | out-file $logOut -append
			if($ErrorMessage)
			{
				"Error message: $ErrorMessage" | out-file $logOut -append
			}	
			if($FailedItem)
			{
				"Failed Item: $FailedItem" | out-file $logOut -append
			}	
			

			
			## Updates ticket status to reflect ticket processing, Will only mark as closed after the ticket has been succesfully closed in WSDL
			$query = "UPDATE ticketCatalog  SET ProcessStatus = 'FAILED', process_date = '$date' where TaskID = '$Ticket';"
			$result1 = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB
			
			if ($status -ne "ok")
			{
				if ($ErrorMessage)
				{

					## Use custom error descriptions
					if($ErrorMessage -like '*Insufficient access rights*')
					{
						$ErrDescription = "Automation System cant process due insufficient access rights in AD"
					}
					elseif (($ErrorMessage -like '*Cannot find an object with identity*') -And ($ErrorMessage -Match $Group) -And ($Group -ne $null))
					{
						$ErrDescription = "AD Group not found"
					}
					elseif (($ErrorMessage -like '*Cannot find an object with identity*') -And ($ErrorMessage -Match $User) -And ($User -ne $null))
					{
						$ErrDescription = "AD User not found"
					}
					elseif ($ErrorMessage -like '*Unable to contact the server*')
					{
						$ErrDescription = "Cannot Access AD Server"
					}
					elseif ($ErrorMessage -like '*The server has rejected the client credentials*')
					{
						$ErrDescription = "Wrong credentials in AD"
					}
					elseif ($ErrorMessage -like '*Missing an argument*')
					{
						$ErrDescription = "Ticket lacks all parameters"
					}	
					elseif ($ErrorMessage -like '*Cannot validate argument on parameter*')
					{
						$ErrDescription = "Invalid parameters"
					}						
					else
					{
						$ErrDescription = "Unidentified error"
					}
					
					$query = "UPDATE ticketCatalog  SET ErrorDescription = '$ErrDescription' where TaskID = '$Ticket';"
					$result1 = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB
				}
			}
			
			#################################################################
			#							CALLBACK
			#	Calls function to Validate user in group in ActiveDirectory
			#################################################################
			$callback = Validate-UserinGroup $Group $User $domain $Ticket $status $ErrDescription
			Write-host 'Status for callback Validate-UserinGroup: '$callback
			if ($callback -eq "failed")
			{
				$status = "failed"
			}
			
			#################################################################
			#							CALLBACK
			#	Calls function to save Process Transaction in DB
			#################################################################
			$callback = Process-Trans $Group $User $domain $Ticket $status $FailedItem $ErrDescription $Msg 'Add user to AD Group'
			Write-host 'Status for callback Process-Trans: '$callback
		}
   }
   else
   {
		$status = "missing_data"
   }
   
	return $status 
}

#############################################
#	Notify Malformed Tickets
#############################################

function Notify-MalformedTickets ()
{
	
	$status = "not_init"
	"Notifying Malformed tickets from WSDL" | Print-Me -displaynormal
	$malformedTickets = @() 
	
	try 
	{
		## Detect malformed Tickets
		$query =  "SELECT ticket,ProcessType from TransactionLog where ProcessType ='Malformed Ticket from WSDL';"
		$results = Get-DatabaseData -query "$query" -isSQLServer -connectionString $conn_DB
		$tables = @($results)
		$status = "ok"
		
		## System for processing the Tickets
		foreach($user in $tables)
		{		
			## If ticket is indeed malformed
			if ($user.ProcessType)
			{
				$malTicket = $user.ticket
				$body = "Administrator, `n`nWe got a Malformed Ticket from HPSM WSDL. `n`nTicket: $malTicket `nDate: $date `n`nPlease review the ticket manually and contact the HPSM and WSDL owners. `n`nRegards, `nADGroups Automation System"
				## Send corresponding Email
				Send-MailMessage -From $emailFrom -To $emailTo -Subject $emailSubjectM -SmtpServer $emailServer -Body $body
				"Sending Email to:$emailTo from:$emailFrom via server:$emailServer for ticket:$malTicket for a malformed ticket" | Print-Me -warning
				## Update DB
				$query2 =  "UPDATE TransactionLog  SET ProcessType = 'Got Malformed Ticket from WSDL' where ticket = '$malTicket';"
				$results2 = Get-DatabaseData -query "$query2" -isSQLServer -connectionString $conn_DB
				$malformedTickets += $malTicket
			}
		}
	}	
	
	catch 
	{
	
		'[ERROR] Could not notify about malformed WSDL Tickets, please review the Web Service and the database, a notification will be sent' | Print-Me -error
		$ErrorMessage = $_.Exception.Message
		$FailedItem = $_.Exception.ItemName
		$status = "failed"
	}
	
	Finally	
	{

		"This script attempted to notify about malformed WSDL tickets: $malformedTickets at $date with status: $status" | out-file $logOut -append
		if($ErrorMessage)
		{
			"Error message: $ErrorMessage" | out-file $logOut -append
		}
		if($FailedItem)
		{
			"Failed Item: $FailedItem" | out-file $logOut -append
		}	
	}
	
	return $status	
}


########################################################
#	Execution flux
########################################################

## Checks Database 
$execute = Check-DBConn
Write-host 'Status for Check-DBConn: '$execute

## Obtains WSDL tickets from the PHP script
$execute = Get-WsdlTickets
Write-host 'Status for Get-WsdlTickets: '"`n"$execute

## Checks for malformed tickets and makes notification
$execute = Notify-MalformedTickets 
Write-Host 'Status for Notify-MalformedTickets: '$execute

## Processes the tickets
$execute = Process-WsdlInfo 
Write-host 'Status for Process-WsdlInfo: '$execute

########################################################
#	Unit Testing Examples
########################################################

## Tests Closing a given ticket
#$test = Close-WsdlTickets "T144641"
#Write-host 'Status for Close-WsdlTickets: '$test

## Tests Adding a given user to the AD
#$test = Set-UserinGroup "EchoSign_BKC_APP_A_S" "bkjg039" "bkglobal.corp.whopper.com"
#Write-host 'Status for Set-UserinGroup: '$test

## Finalize Log file
"----------------------------------------------------------------------------------------------------------------------------" | out-file $logOut -append
