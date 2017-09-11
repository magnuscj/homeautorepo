<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php"); 
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
include ("homeFunctions.php");
$fileName = "pictures\homeAuto_FlowGraf.png";

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
	
	$i				= 0;	//General counter/index variable
	
	$flowPreArray			= array(1);
	$flowXArray				= array();
	$plotArray				= array();
	$daysInMonth 			= array(31,29,31,30,31,30,31,31,30,31,30,31);
	$months 				= array('J','F','M','A','M','J','J','A','S','O','N','D');
	$td 					= date("d", mktime(0,0,0,date("d")));
	$thisMonth 				= date("m", mktime(0,0,0,date("m")));
	$maxYearsinGraph		= 1;
	
	$graph = new Graph(300,187,'auto');
	$graph->SetMargin(35,15,20,30);
	$graph->SetScale("textlin");
	$graph->SetBox(false);			
	$graph->ygrid->SetFill(false);			
	$graph->yaxis->HideLine(false);
	$graph->yaxis->HideTicks(false,false);
	$graph->title->Set("kwh per manad");
	$graph->SetBackgroundGradient('steelblue4:1.3','steelblue4:1.4',GRAD_HOR,BGRAD_MARGIN);
	$graph->SetColor('khaki:1.5');
	$graph->xaxis->SetTickLabels($months);			
		$year = date("Y");
	
	//Determin which sensors that are valid for this view.
	//The information comes either from the web page or the
	//db.
	foreach($sensors[$colName] as $sensorName)
	{
	
		if($sensors[$colVisible][$i] == 'True')
			$sensorShow[$i] 	= "on";
		else
			$sensorShow[$i] 	= "off";	
		$i++;
	}
	
	$senNo = 0;
	foreach($sensors[$colID] as $sensorId)
	{		
		if($sensors[$colType][$senNo] == "power")
		{			
			for($j=0; $j<2 ;$j++)
			{	
				for($i=0; $i<12 ;$i++)//Months 
				{		
					$fdate = "$year-".zeroAdjust($i+1)."-01";
					$tdate = "$year-".zeroAdjust($i+1)."-".$daysInMonth[$i];
					
					$ret2XY = getDataFromDb($username,$password,$database, $fdate." 00:00",$tdate." 23:59",$sensorId, $serverHostName);
					
					if(($i == ($thisMonth-1))&& (sum($ret2XY[0], false)/1000)>0 && (0 == $j))
					{
						$flowPreArray[$i]= ((sum($ret2XY[0], false)/1000)/$td)*($daysInMonth[$i]- $td);
					}
					else 
						$flowPreArray[$i]=0;
					
					$flowSumArray[$i] =  sum($ret2XY[0], false)/1000;
					$flowXArray[$i] = $tdate;	
				
				}
			
				$b1plot = new BarPlot($flowSumArray);
				$b2plot = new BarPlot($flowPreArray);				
				$b1plot->SetColor("black");
				$b1plot->SetFillColor('orange');
				$b1plot->SetValuePos('top');
				$b1plot->value->SetFormat('%d');
				$b1plot->value->Show();
				$b1plot->value->SetColor('black');				
				$b2plot->SetFillColor("red");
				$b2plot->value->SetFormat('%d');
				//$b2plot->value->Show();
				$b2plot->value->SetColor('black');				
				$plotArray[] = new AccBarPlot(array($b1plot,$b2plot));				
				$year = $year - 1;
			}			
			// Create the grouped bar plot
			$gbplot2 = new GroupBarPlot($plotArray);
			$graph->Add($gbplot2);
		}	
		//Plot power Stop
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
		// Display the graph
		$graph->Stroke();
	}
	
	
}while (isCli());
?>