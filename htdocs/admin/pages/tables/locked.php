<?php 

########################################################### 
# AUTHOR   : Francisco Campos
# DATE     : 03-27-2017  
# EDIT     : 04-02-2017 
# COMMENTS : A simple web console
# VERSION  : 0.99B
########################################################### 


    session_start();
    $role = $_SESSION['sess_userrole'];
    if(!isset($_SESSION['sess_username'])){
      header('Location: ../../index.php?err=2');
    }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>ADGroups Automation</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- DATA TABLES -->
    <link href="../../plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../../dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="../../dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue">
    <div class="wrapper">
      
      <header class="main-header">
        <a href="../../home.php" class="logo"><b>RBI</b> Automation</a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
		  
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">

                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                  </li>
                  <!-- Menu Body -->
                  <li class="user-body">
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="#" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="#" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
			              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <button type="submit" class="fa fa-users" id="restore2">ADGroup Console Member: <?php echo $_SESSION['sess_username'];?></button>			  
                </a>				
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
				    <p>

                      <small>  </small>
                    </p>
                    <p>
                      <?php echo $_SESSION['sess_username'];?>
                      <b>Role: <?php echo $role;?></b>
                    </p>
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                     <a href="../../logout.php"><button type="submit" class="btn btn-primary btn-danger" id="restore1">LOGOUT</button></a>	
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <div class="user-panel">

            <div class="pull-left info">
              <p>RBI</p>
            </div>
          </div>
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="active treeview">
              <a href="#">
                <i class="fa fa-dashboard"></i> <span>Dashboard</span> <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="active"><a href="../../home.php"><i class="fa fa-circle-o"></i> Dashboard</a></li>
              </ul>
            </li>

            <li class="treeview active">
              <a href="#">
                <i class="fa fa-table"></i> <span>Process Tracking</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
				<li class="active"><a href="pending.php"><i class="fa fa-circle-o"></i> Pending Tickets</a></li>	
				<li class="active"><a href="locked.php"><i class="fa fa-circle-o"></i> Failed Tickets</a></li>	
				<li class="active"><a href="succesful.php"><i class="fa fa-circle-o"></i> Succesful Tickets</a></li>					
                <li class="active"><a href="transactions.php"><i class="fa fa-circle-o"></i> Transaction List</a></li>													
              </ul>
            </li>
			
			<li class="treeview active">
              <a href="#">
                <i class="fa fa-cog"></i> <span>Process Catalogs</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">					
				<li class="active"><a href="domainCatalog.php"><i class="fa fa-circle-o"></i> Domain Catalog</a></li>							
              </ul>
            </li>

<?php 
		if($role=="admin"){			
			echo '<li class="treeview active">';
              echo '<a href="#">';
                echo '<i class="fa fa-eye"></i> <span>Execution Debug</span>';
               echo '<i class="fa fa-angle-left pull-right"></i>';
              echo '</a>';
                   echo '<ul class="treeview-menu">	';			
				echo '<li class="active"><a href="datas.php"><i class="fa fa-circle-o"></i> Operation Log</a></li>	';							
              echo '</ul>';
           echo ' </li>';
		   
			echo '<li class="treeview active">';
              echo '<a href="#">';
                echo '<i class="fa fa-users"></i> <span>Management</span>';
               echo '<i class="fa fa-angle-left pull-right"></i>';
              echo '</a>';
                   echo '<ul class="treeview-menu">	';			
				echo '<li class="active"><a href="requestUser.php"><i class="fa fa-circle-o"></i> User Management</a></li>	';							
              echo '</ul>';
           echo ' </li>';		   
    }

?>
            <li><a href="#"><i class="fa fa-book"></i> Documentation</a></li>
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Right side column. Contains the navbar and content of the page -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Failing Tickets
            <small>Reopen All Failing Tickets</small>
			<a href="http://bkdalutlwp04/admin/plugins/operations/operations.php?operation=updateallticketcatalog"><button type="submit" class="btn btn-primary" id="restore1">REOPEN</button>	</a>
<?php 
		if($role=="admin"){					
            echo '<small>Delete All Failing entries in Automation System</small>';
			echo '<a href="http://<server>/admin/plugins/operations/operations.php?operation=deleteallticketcatalog"><button type="submit" class="btn btn-primary btn-danger" id="restore1">DELETE</button></a>';	
		}
?>			
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Tables</a></li>
            <li class="active">Data tables</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">


              <div class="box">
                <div class="box-header">
                  <h3 class="box-title"></h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
					    <th>id</th>
                        <th>Date of Request</th>							
                        <th>Date of Processing</th>						
                        <th>Ticket</th>
						<th>Company/Domain</th>
						<th>User</th>
						<th>Reason</th>
						<th>Request</th>
						<th>Status</th>
						<th>Reopen Ticket</th>
<?php 
					if($role=="admin"){								
						echo '<th>Delete Entry</th>';
					}					
?>							
                      </tr>
                    </thead>
                    <tbody>

    <?php
$serverName = "<server>";
$connectionOptions = array(
    "Database" => "<database>",
    "Uid" => "<id>",
    "PWD" => "<password>"
    );
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);


if ($conn){
    if(($result = sqlsrv_query($conn,"SELECT * FROM ticketCatalog where ProcessStatus = 'FAILED'")) !== false){
		$x = 0;
        while( $obj = sqlsrv_fetch_object( $result )) {
					   $x = $obj->id;
			           echo '<tr>';
					   echo '<td>',$obj->id,'</td>';
					   echo '<td>',($obj->OpenTime)->format('Y-m-d   H:i:s'),'</td>';					   
					   echo '<td>',($obj->process_date)->format('Y-m-d   H:i:s'),'</td>';
                       echo '<td>',$obj->TaskID,'</td>';
                       echo '<td>',$obj->UserCompany,'</td>';
					   echo '<td>',$obj->LoginId,'</td>';
                       echo '<td>',$obj->ErrorDescription,'</td>';
					   echo '<td>',$obj->ApplicationRequested,'</td>';					   
					   echo '<td>',$obj->ProcessStatus,'</td>';
					   echo '<td><a href="http://<server>/admin/plugins/operations/operations.php?operation=updateticketcatalogid&ticket=X&ID=',$x,'"><button type="submit" class="btn btn-primary" id="',$x,'">REOPEN</button></a></td>';
					   if ($role == "admin")
					   {
							echo '<td><a href="http://<server>/admin/plugins/operations/operations.php?operation=deleteticketcatalogid&ticket=X&ID=',$x,'"><button type="submit" class="btn btn-primary btn-danger" id="',$x,'">DELETE</button></a></td>';	
					   }
                       echo '</tr>';
        }
    }
}else{
    die(print_r(sqlsrv_errors(), true));
}
    ?>
                    </tbody>
                    <tfoot>
                      <tr>
					    <th>id</th>
                        <th>Date of Request</th>							
                        <th>Date of Processing</th>						
                        <th>Ticket</th>
						<th>Company/Domain</th>
						<th>User</th>
						<th>Reason</th>
						<th>Request</th>
						<th>Status</th>
						<th>Reopen Ticket</th>
<?php 
					if($role=="admin"){								
						echo '<th>Delete Entry</th>';
					}					
?>	
                      </tr>
                    </tfoot>
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> 2.0
        </div>
      </footer>
    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.3 -->
    <script src="../../plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="../../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- DATA TABES SCRIPT -->
    <script src="../../plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
    <script src="../../plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
    <!-- SlimScroll -->
    <script src="../../plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='../../plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="../../dist/js/app.min.js" type="text/javascript"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="../../dist/js/demo.js" type="text/javascript"></script>
    <!-- page script -->
    <script type="text/javascript">
      $(function () {
        $("#example1").dataTable();
        $('#example2').dataTable({
          "bPaginate": true,
          "bLengthChange": false,
          "bFilter": false,
          "bSort": true,
          "bInfo": true,
          "bAutoWidth": false
        });
      });
    </script>

  </body>
</html>