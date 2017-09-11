<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php"); 
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
require_once ('jpgraph/jpgraph_canvas.php');
include ("homeFunctions.php");

$fileName = "pictures\homeAuto_Alarm.png";
$sleepTime = getConfig("SLEEP")+5;
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
	
	$username		= getConfig("DBUSN");
	$password		= getConfig('DBPSW');
	$database		= getConfig('DBNAME');
	$serverHostName	= getConfig('DBIP');
	$sensors 		= getSensorNames($username,$password,$database, $serverHostName); //From sensor configuration
	
	//Index names for the sensor configuration db table
	$colID			= 0;
	$colName		= 1;
	$colColor		= 2;
	$colVisible		= 3;
	$colType		= 4;
	$noOfFlowGraphs = 0;
	$txt			="";
	$i				= 0;	//General counter/index variable
	
	$senNo 			= 0;
	
	$tdate 			= date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	
	$graph = new CanvasGraph(395,206,'auto');
	$graph->SetMargin(8,8,8,8);
	$graph->SetMarginColor('black:1.1');
	$graph->SetColor('gray:0.43');
	
	
		
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
	
	
	
	// .. and a circle (x,y,diameter)
	
		
	
		$swiches = getSwiches($serverHostName,$username,$password, $database);
		$swichsStatus = array("","","");
		$graph->InitFrame();
		$i=0;
		$longestName=0;
		$rowNo =0;
		$pos	 	= 55;
		$posText	= -10 + $pos;
		$posSym  	= $pos;
		foreach($swiches as $swich)
		{
			if(strlen($swich)>$longestName)
				$longestName = strlen($swich);
		}
		
		$sensorNo = 0;
		
		$t3 = new Text("Larm",10,10);
		$t3->SetFont(FF_ARIAL,FS_BOLD,20);
		$t3->SetColor('gray:2.7');
		$t3->Stroke($graph->img);
		
		foreach($swiches as $swich)
		{
			
			if($sensorNo==5)
			{
				$rowNo 	= 1;
				$i 		= 0; 
			}
			$swichsStatus = getSwichStatus($serverHostName,$username,$password, $database, $swich);
			
			if ($swichsStatus[1]== "0")
				$graph->img->SetColor('green');
			elseif($swichsStatus[1]== "1")
				$graph->img->SetColor('red');
			else
				$graph->img->SetColor('orange');
			
			$graph->img->FilledCircle(22+$rowNo*222,$posSym+$i,10);
			//$graph->img->SetColor('orange');
			//$graph->img->Line(100,150,200,150);
		
			
			
			$t2 = new Text($swich,35+$rowNo*222,$posText+$i);
			$t2->SetFont(FF_ARIAL,FS_NORMAL,20);
			$t2->SetColor('gray:2.7');
			$t2->Stroke($graph->img);
			
			
			$i=$i+25;
			$sensorNo +=1;
		}
		
	
		
		
		
	if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($fileName);
		
		$utr = time()-$time;
		print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep(300);
	}
	
	if(!isCli())
	{
		// Display the graph
		$graph->Stroke();
	}
	
}while (isCli());
	
?>