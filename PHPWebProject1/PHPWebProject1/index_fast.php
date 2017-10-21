<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<meta http-equiv="refresh" content="120;url=index_fast.php">

<head>
<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />

<title>Holken</title>

<link rel="stylesheet" href="style_fast.css" type="text/css" media="screen" />

</head>

<body>

<div id="container">

	<!-- Start of Page Header -->

	<div id="header_container">
	<div id="page_header">
		<img src='\pictures\homeAuto_FlowGraf.png' >
		
		<img src='\pictures\homeAuto_AverageGraph.png' >
		<div id="header_company">
		<h1><span>Holken</span></h1>
		</div>
		
		

	</div>
	</div>

	<!-- End of Page Header -->


	<!-- Start of Left Sidebar -->

	<div id="left_sidebar">

		<!-- Start of User Login -->

		<?php 
			 	date_default_timezone_set('Europe/Stockholm');
			 	include ("\homeFunctions.php");
				$username		= getConfig("DBUSN");
				$password		= getConfig('DBPSW');
				$database		= getConfig('DBNAME');
			
				$sensors = array();
				mysql_connect(getConfig('DBIP'),$username,$password);
				@mysql_select_db($database) or die( "Unable to select database");
				$query = "SELECT * FROM sensorconfig;";
				$result = mysql_query($query);
				
				 if ($result) 
				 {
				 	$myrow=mysql_fetch_array($result);
				   	do
				   	{	
				   		$ids[]       = $myrow['sensorid'];  //It would not create the graphs without using '[]'
				     	$names[]     = $myrow['sensorname'];  //It would not create the graphs without using '[]'
				     	$visible[]   = $myrow['visible'];  //It would not create the graphs without using '[]'
				   	}while ($myrow=mysql_fetch_array($result));
				   	mysql_free_result($result);
				 }
				 mysql_close();
				$sensors[0] = $ids;
				$sensors[1] = $names;
				$sensors[2] = $visible;
				
?>

		<div class="box_container">
		<div id="userlogin">

			<h2><span>User Login</span></h2>
<?php
			date_default_timezone_set('Europe/Stockholm');
		
			$isset =true;
			foreach($sensors[1] as $sensorName)
			{
				if(isset($_POST[$sensorName]))
				{
					//print("isset ");
					//print($_POST[$sensorName]." ");
					//print($sensorName."<BR>");		
				}
				else
				{
					//print("error ");
					//print($sensorName."<BR>");
					//$isset =false;		
				}
			}
			
			if($isset)
			{
				
				$i=1;
				$str = "";
				foreach($sensors[1] as $sensorName)
				{
					$str = $str. $sensorName .'='.'$_GET['.$sensorName.']';
					if($i < sizeof($sensors[1]))
						$str = $str . "&";
					$i++;
				}
				print("<FORM NAME ='form1' METHOD ='POST' ACTION = index_fast.php?".$str.">");

			}
			else
			{
				print("<FORM NAME ='form1' METHOD ='POST' ACTION = index_fast.php>");
			}
?>
		From: <INPUT TYPE = "Text" VALUE ="<?php
		date_default_timezone_set('Europe/Stockholm');
		$past = mktime(date("H"), date("i"),0,date("m"),date("d")-1,date("Y"));
		$typ = date("Y-m-d H:i", $past);
		//$typ = date("Y-m-d", $past);
		print ($typ);?>" NAME = "fromdate" SIZE="60" STYLE="margin: 0px; padding: 0px;"><BR>
		
		To:  <INPUT TYPE = "Text" VALUE ="<?php
		date_default_timezone_set('Europe/Stockholm');
		$past = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$typ2 = date("Y-m-d", $past)." ".date('H:i',time());
		print ($typ2);?>" NAME = "todate" STYLE="margin: 0px; padding: 0px;"><BR>
		 
		 <?php 
			 	if(isset($Submit1))
				{
					
					foreach($sensors[1] as $sensorName)
					{
						$visible= " ";
						if(isset($_POST[$sensorName]))
							$visible = "checked";
									
						$prnt = '<INPUT TYPE=CHECKBOX NAME='.'"'.$sensorName.'" '.$visible.' >'.$sensorName.'<BR>'	;			
						
						print ($prnt);
					}
				}
				else
				{
					$i = 0;
					foreach($sensors[1] as $sensorName)
					{
						$visible= " ";
						if($sensors[2][$i] == 'True')
							$visible = "checked";
							
						print ("<INPUT TYPE=CHECKBOX NAME=".'"'.$sensorName.'" '.$visible." >".$sensorName."<BR>");
						$i++;
					}
				}
			?>
		

		<INPUT TYPE = "Submit" Name = "Submit1" VALUE = "Visa">
		<BR>

	</FORM>

			

		</div>
		</div>

		<!-- End of User Login -->


		<!-- Start of Latest News -->

		<div class="box_container">
		<div id="news">

			<h2><span>Latest News</span></h2>
<p></p>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>

<p></p>

				



			

			<div class="clearthis">&nbsp;</div>
		</div>
		</div>

		<!-- End of Latest News -->


	</div>

	<!-- End of Left Sidebar -->


	<!-- Start of Main Content Area -->

	<div id="maincontent_container">
	<div id="maincontent">


		<div id="maincontent_top">

			<!-- Start of How We Started -->

			<div id="started_container">
			<div id="started">

			
			<?php
			date_default_timezone_set('Europe/Stockholm');
						
			$i = 0;
			foreach($sensors[1] as $sensorName)
			{
			$post = $sensorName;
				if(isset($_POST[$post]))
					$sensor[$i] = $_POST[$post];
				else
					$sensor[$i] = "off";
				$i++;
			}
			
			
			if(isset($_POST['fromdate']))
			{
				$i=0;
				$str = "";
				foreach($sensors[1] as $sensorName)
				{
					$str = $str  .$sensorName .'='.$sensor[$i];
					if($i < (sizeof($sensors[1]))-1)
						$str = $str . "&";
					$i++;
					
				}
				print "<li><img src='homeAuto_graph.php?fromdate=".$_POST['fromdate']."&todate=".$_POST['todate']."&pic=2&".$str."' ></li>";
				
				//print "../homeAuto_graph.php?fromdate=".$_POST['fromdate']."&todate=".$_POST['todate']."&pic=2&".$str."'";
			//	print "<li><img src='../homeAuto_graph.php?fromdate=".$_POST['fromdate']."&todate=".$_POST['todate']."&pic=2"."&sensor1=".$sensor[0]."&sensor2=".$sensor[1]."&sensor3=".$sensor[2]."&sensor4=".$sensor[3]."&sensor5=".$sensor[4]."' ></li>";
			}
			else
			{
				print "<li><img src='pictures\\homeAuto_graph.png' ></li>";
			}
			?>
				
								
				
		

			</div>
			</div>

			<!-- End of How We Started -->

		</div>


		<!-- Start of Featured Products -->

		<div class="clearthis">&nbsp;</div>

		<!-- End of Featured Products -->


	</div>
	</div>

	<!-- End of Main Content Area -->


	<!-- Start of Page Footer -->

	<!-- End of Page Footer -->


	<div class="clearthis">&nbsp;</div>

</div>

</body>
</html>