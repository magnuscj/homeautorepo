<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php"); 
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
include ("homeFunctions.php");

$fileName = "pictures\homeAuto_AverageGraph.png";
$sleepTime = 180;
if(isCli())
{
	print "Sleeping ". $sleepTime."s. \n";
	sleep($sleepTime);
}
do
{
	if(isCli())
	{
		$time = time();
		print date('H:i:s',$time).", Working \n";
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
	$senNo = 0;
	
	foreach($sensors[$colID] as $sensorId)
	{
		if($sensors[$colType][$senNo] == "temp"  && $sensors[$colName][$senNo] == "Ute")
		{		
			$flowSumArray			= array(0,0,0,0,0,0,0,0,0,0,0,0);
			$flowXArray				= array();
			$plotArray				= array();
			$daysInMonth 			= array(31,29,31,30,31,30,31,31,30,31,30,31);
			$months 				= array('J','F','M','A','M','J','J','A','S','O','N','D');
						
			$graph = new Graph(300,187,'auto');
			$graph->SetMargin(35,15,20,30);
			$graph->SetScale("textlin");
			$graph->SetBox(false);
			$graph->ygrid->SetFill(false);
			$graph->xaxis->SetTickLabels($months);
			$graph->yaxis->HideLine(false);
			$graph->yaxis->HideTicks(false,false);
			$graph->SetBackgroundGradient('steelblue4:1.3','steelblue4:1.4',GRAD_HOR,BGRAD_MARGIN);
			$graph->SetColor('khaki:1.5');
			$year = date("Y");
						
			for($j=0; $j<2 ;$j++)
			{		
				for($i=0; $i<12 ;$i++)
				{
					$fdate = "$year-".zeroAdjust($i+1)."-01";
					$tdate = "$year-".zeroAdjust($i+1)."-".$daysInMonth[$i];
					$flowSumArray[$i] =  getAvg($fdate, $tdate, $sensorId, $username, $password, $serverHostName, $database);
					if($flowSumArray[$i]==null)
						$flowSumArray[$i] = 0;
			
					$flowXArray[$i]   = $tdate;	
				}
			
				$b1plot= new BarPlot($flowSumArray);							
				$b1plot->value->SetFormat('%d');
				$b1plot->value->Show();
				$b1plot->value->SetColor('black');
				$b1plot->SetColor("black");
				$b1plot->SetFillColor('orange');
				$plotArray[] =$b1plot;
				$graph->title->Set("Medeltemperatur");
				$year = $year - 1;
			}
			
			$gbplot = new GroupBarPlot($plotArray);	// Create the grouped bar plot		
			$graph->Add($gbplot);					// ...and add it to the graPH			
		}
		$senNo++;
	}
	
	if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($fileName);
		$utr = time()-$time;
		print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep(3600);
	}
	
	if(!isCli())
	{
		$graph->Stroke();
	}
	
}while (isCli());
?>