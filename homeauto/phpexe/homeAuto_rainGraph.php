<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php"); 
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
include ("homeFunctions.php");



// Create the graph.

do 
{
	if(isCli())
	{
		sleep(120);
		print "120";
	}
	
	$max			= array();
	$min			= array();
	$curr			= array();
	$username		= getConfig("DBUSN");
	$password		= getConfig('DBPSW');
	$database		= getConfig('DBNAME');
	$serverHostName	= getConfig('DBIP');
	$sensors 		= getSensorNames($username,$password,$database, $serverHostName); //From sensor configuration
	$webconfig		= getWebConfig($username,$password,$database, $serverHostName);	  //From webpage configuration
	
	//Index names for the sensor configuration db table
	$colID			= 0;
	$colName		= 1;
	$colColor		= 2;
	$colVisible		= 3;
	$colType		= 4;
	$noOfFlowGraphs = 0;
	
	$i				= 0;	//General counter/index variable
	
	
	//Determin which sensors that are valid for this view.
	//The information comes either from the web page or the
	//db.
	foreach($sensors[$colName] as $sensorName)
	{
		if(isset($_GET[$sensorName]))
		{
			$sensorShow[$i] = $_GET[$sensorName];		
		}
		else
		{
			$isset =false;	
				
			if($sensors[$colVisible][$i] == 'True')
				$sensorShow[$i] 	= "on";
			else
				$sensorShow[$i] 	= "off";
		}
		$i++;
	}
	
	
	if(isset($_GET['fromdate']))
	{
		$fdate	= $_GET['fromdate'];	
		$tdate	= $_GET['todate'];
		$pic	= $_GET['pic'];
	}
	else
	{
		$fdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
		$tdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	}
	
	//Title text ------------------
	$tid=localtime();
	if($tid[1]<= 9)
		$zeroMin = '0';
	else
		$zeroMin = '';
		
	if($tid[2]<= 9)
		$zeroHour = '0';
	else
		$zeroHour = '';
		
		
	
	
	//Title text ------------------	
	
	
	$fsplited 	= preg_split ( '/-/' ,$fdate  );
	$tsplited 	= preg_split ( '/-/' ,$tdate  );
	
	$frommonth = (int)$fsplited[1];
	$tomonth   = (int)$tsplited[1];
	$fromyear  = (int)$fsplited[0];
	$toyear	   = (int)$tsplited[0];
	
	
	$i=0;
	
	$changeConfig = true;
	if($changeConfig)
	{
		foreach($sensors[$colID] as $sensorId)
		{
			if($sensorShow[$i] == "on")
				$show ="True";
			else
				$show ="False";
				
			mysql_connect($serverHostName,$username,$password);
			@mysql_select_db($database) or die( "Unable to select database");
			$query = "UPDATE sensorconfig SET visible='".$show. "' WHERE sensorid ='".$sensorId."';";
			$result = mysql_query($query);
			$i++;
			//mysql_free_result($result);
			mysql_close();
		}
		$sensors 		= getSensorNames($username,$password,$database,$serverHostName);
	}
	
	//------------------------------------------------------------------------
	//                          Current temp on each sensor
	//------------------------------------------------------------------------
	
	$curr = currentTemp($sensors,$username,$password,$serverHostName,$database);
	
	//------------------------------------------------------------------------
	
	$senNo = 0;
	foreach($sensors[$colID] as $sensorId)
	{
		$ydata_temptot = array();
		$xdata_timeTot = array();
		
		
		
		
	if($sensors[$colType][$senNo] == "rain")
		{
			$ydata2_temptot 		= array();
			$xdata2_timeTot 		= array();
			$ydata2_floatingAverage = array();
			$ydata2_calcTotAvRobust = array();
			$xdata2_timeTotRobust 	= array();
			$flowSumArray			= array(1);
			$flowXArray				= array();
			
			$doubleTest = (double) 0.0;
			
			
			
		for($i=0; $i<11 ;$i++)
		{
			//$fdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-($i+1),date("Y")));
			//$tdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-($i+0),date("Y")));
			
			$daysInMonth = array(31,29,31,30,31,30,31,31,30,31,30,31);
			$months = array('J','F','M','A','M','J','J','A','S','O','N','D');
			
			$test = zeroAdjust($i+1);
			
			$fdate = "2012-".zeroAdjust($i+1)."-01";
			$tdate = "2012-".zeroAdjust($i+1)."-".$daysInMonth[$i];
			
			$ret2XY = getDataFromDb($username,$password,$database, $fdate,$tdate,$sensorId, $serverHostName);
			
			
			$flowSumArray[$i] =  sum($ret2XY[0], false)*0.254;
			$flowXArray[$i] = $tdate;	
		}
			
			
		
			//------------ Robust Start--------------
			
			/*for($i=1;$i<(sizeof($ydata2_temptot));$i++)
			{
				$minutes = (int)(($xdata2_timeTot[$i]-$xdata2_timeTot[$i-1])/60);
				
				if($minutes>1)//If more than one minute has passed
				{
					
					$averagePower = ($ydata2_temptot[$i]-$ydata2_temptot[$i-1])/$minutes;
					
					for($j=0;$j<$minutes;$j++)
					{
						$ydata2_calcTotAvRobust[]= $averagePower;
						$xdata2_timeTotRobust[] = $xdata2_timeTotRobust[sizeof($xdata2_timeTotRobust)-1]+60;			
					}		
				}
				else
				{
					$doubleTest = (double) ($ydata2_temptot[$i]-$ydata2_temptot[$i-1]);				
					$ydata2_calcTotAvRobust[]= ($ydata2_temptot[$i]-$ydata2_temptot[$i-1]);
					$xdata2_timeTotRobust[]= $xdata2_timeTot[$i];
					$doubleTest = 0.0;
				}	
			}					
			
			$ydata2_floatingAverage = floatAvg(5, $ydata2_calcTotAvRobust);
			
			
			$ydata2_floatingAverage = scaleChange(0.25 , $ydata2_calcTotAvRobust);
			
			//------------ Robust End  --------------
		*/
			
			if(false)
			{
				
				$lineplot2=new LinePlot($ydata2_floatingAverage,$xdata2_timeTotRobust );
				$lineplot2->SetColor($sensors[$colColor][$senNo]);
				$lineplot2->SetFillGradient('royalblue4','royalblue4');
				$lineplot2->SetLegend($sensors[$colName][$senNo]." :  ".strval(($ydata2_temptot[sizeof($ydata2_temptot)-1]-$ydata2_temptot[0])*0.254)." mm" );
					
				$graph->AddY($noOfFlowGraphs,$lineplot2);		
				//$graph->SetYScale($noOfFlowGraphs,'lin',0,10);
				$graph->SetYScale($noOfFlowGraphs,'lin');
				$graph->ynaxis[$noOfFlowGraphs]->SetColor('teal');
				$graph->ynaxis[$noOfFlowGraphs]->title->Set('mm');
				$graph->ynaxis[$noOfFlowGraphs]->title->SetMargin(10);
				$noOfFlowGraphs 		+= 1;
			}
			else
			{
				$data1y=array(56,80,40,116,82,105);
				$data2y=array(61,30,82,105,82,70);
				$data3y=array(115,50,70,93,82,105);
				
				$graph = new Graph(300,187,'auto');
				$graph->SetMargin(35,15,20,30);
				$graph->SetScale("textlin");
				
				// Create the graph. These two calls are always required
				
				
				//$theme_class=new UniversalTheme;
				//$graph->SetTheme($theme_class);
				
				//$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150), array(15,45,75,105,135));
				$graph->SetBox(false);
				
				$graph->ygrid->SetFill(false);
				$graph->xaxis->SetTickLabels($months);
				$graph->yaxis->HideLine(false);
				$graph->yaxis->HideTicks(false,false);
				//$graph->xaxis->SetLabelAngle(90);
				$graph->SetBackgroundGradient('steelblue4:1.3','steelblue4:1.4',GRAD_HOR,BGRAD_MARGIN);
				$graph->SetColor('khaki:1.5');
				// Create the bar plots
				
				
				$b1plot = new BarPlot($flowSumArray);
				$b2plot = new BarPlot($data2y);
				$b3plot = new BarPlot($data3y);
				
				$b1plot->value->SetFormat('%d');
				$b1plot->value->Show();
				$b1plot->value->SetColor('#55bbdd');
				
				
				// Create the grouped bar plot
				$gbplot = new GroupBarPlot(array($b1plot,$b2plot,$b3plot));
				// ...and add it to the graPH
				$graph->Add($b1plot);
				
				
				$b1plot->SetColor("white");
				$b1plot->SetFillColor('royalblue4');
				
				//$b2plot->SetColor("white");
				//$b2plot->SetFillColor("#11cccc");
				
				//$b3plot->SetColor("white");
				//$b3plot->SetFillColor("#1111cc");
				
				$graph->title->Set("mm per manad");
				
			}
		
		}
		
		
		//Plot power Stop
		$senNo++;
	}
	
	
	
		if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$fileName = "rain.png";
		$graph->img->Stream($fileName);
		
		// Send it back to browser
		//$graph->img->Headers();
		//$graph->img->Stream();
		
	}
	
	if(!isCli())
	{
		// Display the graph
		$graph->Stroke();
	}

	sleep(3600);
	
}while (isCli());
?>