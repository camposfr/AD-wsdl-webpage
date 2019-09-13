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
    if(!isset($_SESSION['sess_username']) ){
      header('Location: index.php?err=2');
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>RBI ADGroups Automation | Dashboard</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />    
    <!-- FontAwesome 4.3.0 -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />    
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />

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
        <!-- Logo -->
        <a href="home.php" class="logo"><b>RBI </b>ADGroups </a>
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
                     <a href="logout.php"><button type="submit" class="btn btn-primary btn-danger" id="restore1">LOGOUT</button></a>	
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
                <li class="active"><a href="home.php"><i class="fa fa-circle-o"></i> Dashboard</a></li>
              </ul>
            </li>

            <li class="treeview active">
              <a href="#">
                <i class="fa fa-table"></i> <span>Process Tracking</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
				<li class="active"><a href="pages/tables/pending.php"><i class="fa fa-circle-o"></i> Pending Tickets</a></li>	
				<li class="active"><a href="pages/tables/locked.php"><i class="fa fa-circle-o"></i> Failed Tickets</a></li>	
				<li class="active"><a href="pages/tables/succesful.php"><i class="fa fa-circle-o"></i> Succesful Tickets</a></li>					
                <li class="active"><a href="pages/tables/transactions.php"><i class="fa fa-circle-o"></i> Transaction List</a></li>													
              </ul>
            </li>
			
			<li class="treeview active">
              <a href="#">
                <i class="fa fa-cog"></i> <span>Process Catalogs</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">						
				<li class="active"><a href="pages/tables/domainCatalog.php"><i class="fa fa-circle-o"></i> Domain Catalog</a></li>							
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
				echo '<li class="active"><a href="pages/tables/datas.php"><i class="fa fa-circle-o"></i> Operation Log</a></li>	';							
              echo '</ul>';
           echo ' </li>';
		   
			echo '<li class="treeview active">';
              echo '<a href="#">';
                echo '<i class="fa fa-users"></i> <span>Management</span>';
               echo '<i class="fa fa-angle-left pull-right"></i>';
              echo '</a>';
                   echo '<ul class="treeview-menu">	';			
				echo '<li class="active"><a href="pages/tables/requestUser.php"><i class="fa fa-circle-o"></i> User Management</a></li>	';							
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
            Automation Dashboard
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3>
    <?php
			# this code Snippet should be moved
		$serverName = "RBDALSQLWS01";
		$connectionOptions = array(
			"Database" => "STK_OpsCenterMonitor",
			"Uid" => "opscenteradmin",
			"PWD" => "Pr1.m4n4g3_ops7"
			);
		//Establishes the connection
		$conn = sqlsrv_connect($serverName, $connectionOptions);


		if ($conn){
			if(($result = sqlsrv_query($conn,"SELECT COUNT(*) AS NumberofClosed FROM ticketCatalog where ProcessStatus = 'CLOSED'; ")) !== false){
				while( $obj = sqlsrv_fetch_object( $result )) 
				{
					echo $obj->NumberofClosed;
				}
			}
		}
		else
		{
			die(print_r(sqlsrv_errors(), true));
		}
    ?>				  
				  
				  <sup style="font-size: 20px"></sup></h3>
                  <p>Number of Succesfully Completed Tickets</p>
                </div>
                <div class="icon">
                  <i class="ion ion-pie-graph"></i>
                </div>
                <a href="pages/tables/succesful.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
			
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-yellow">
                <div class="inner">
                  <h3>
    <?php
			# this code Snippet should be moved
		$serverName = "RBDALSQLWS01";
		$connectionOptions = array(
			"Database" => "STK_OpsCenterMonitor",
			"Uid" => "opscenteradmin",
			"PWD" => "Pr1.m4n4g3_ops7"
			);
		//Establishes the connection
		$conn = sqlsrv_connect($serverName, $connectionOptions);


		if ($conn){
			if(($result = sqlsrv_query($conn,"SELECT COUNT(*) AS NumberofPending FROM ticketCatalog where ProcessStatus = 'NO'; ")) !== false){
				while( $obj = sqlsrv_fetch_object( $result )) 
				{
					echo $obj->NumberofPending;
				}
			}
		}
		else
		{
			die(print_r(sqlsrv_errors(), true));
		}
    ?>				  
				  
				  <sup style="font-size: 20px"></sup></h3>
                  <p>Number of Pending Tickets</p>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="pages/tables/pending.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->			
			
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3>
    <?php
			# this code Snippet should be moved
		$serverName = "RBDALSQLWS01";
		$connectionOptions = array(
			"Database" => "STK_OpsCenterMonitor",
			"Uid" => "opscenteradmin",
			"PWD" => "Pr1.m4n4g3_ops7"
			);
		//Establishes the connection
		$conn = sqlsrv_connect($serverName, $connectionOptions);


		if ($conn){
			if(($result = sqlsrv_query($conn,"SELECT COUNT(*) AS NumberofFailed FROM ticketCatalog where ProcessStatus = 'FAILED'; ")) !== false){
				while( $obj = sqlsrv_fetch_object( $result )) 
				{
					echo $obj->NumberofFailed;
				}
			}
		}
		else
		{
			die(print_r(sqlsrv_errors(), true));
		}
    ?>					  
				  </h3>
                  <p>Failed/Locked Tickets</p>
                </div>
                <div class="icon">
                  <i class="ion ion-pie-graph"></i>
                </div>
                <a href="pages/tables/locked.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
          </div><!-- /.row -->
       </section><!-- /.content -->	
	

          </div><!-- /.row (main row) -->

 
      </div><!-- /.content-wrapper -->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> 2.0
        </div>
      </footer>
    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.3 -->
    <script src="plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- jQuery UI 1.11.2 -->
    <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>    
    <!-- Morris.js charts -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="plugins/morris/morris.min.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- jQuery Knob Chart -->
    <script src="plugins/knob/jquery.knob.js" type="text/javascript"></script>
    <!-- daterangepicker -->
    <script src="plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- datepicker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <!-- Slimscroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>

    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="dist/js/pages/dashboard.js" type="text/javascript"></script>

    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js" type="text/javascript"></script>
	    <!-- page script -->

  </body>
</html>