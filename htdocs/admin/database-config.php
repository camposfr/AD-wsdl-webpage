<?php

   ## define database related variables
   $serverName = "<server>";
   $connectionOptions = array(
    "Database" => "<database>",
    "Uid" => "<id>",
    "PWD" => "<password>"
    );

   ## try to conncet to database
   $dbh = sqlsrv_connect($serverName, $connectionOptions);

   if(!$dbh){

      echo "unable to connect to database";
   }
   
?>
