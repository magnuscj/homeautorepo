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

$fileName = "pictures\homeAuto_report.png";
$sleepTime = 180;
if(isCli())
{
	print "Sleeping ".$sleepTime."s. \n";
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
	$txt2			="";
	$i				= 0;	//General counter/index variable
	$textColor		= "white";
	$backgroundColor= "gray:0.43";
	$infoStart_Y	= 70;
	
	$senNo 			= 0;
	
	$tdate 			= date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	
	$graph = new CanvasGraph(405,425,'auto');
	$graph->SetMargin(5,11,6,11);
	$graph->SetMarginColor('black:1.1');
	$graph->SetColor($backgroundColor);
	
	$t2 = new Text($tdate.", ".date("H:i"),333,402);
	$t2->SetFont(FF_ARIAL,FS_BOLD,10);
	$t2->SetColor('gray:0.63');
	$t2->Align('center','top');// How should the text box interpret the coordinates?
	$t2->ParagraphAlign('center');// How should the paragraph be aligned?
	$graph->AddText($t2);
	//Determin which sensors that are valid for this view.
	//The information comes either from the web page or the
	//db.
	foreach($sensors[$colName] as $sensorName)
	{		
		if($sensors[$colVisible][$i] == 'True')
			$sensorShow[$i] 	= "on";
		else
			$sensorShow[$i] 	= "off";
		
		/*if((getCurr("0D000002D6550E28", $username, $password, $serverHostName, $database)>32) && $sensorName == "Skorst.")
		{
			$sensorShow[$i] 	= "on";
		}	*/
		$i++;
		
	}
	
	

	$i=0;
	foreach($sensors[$colID] as $sensorId)
	{
		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "temp")
		{			
			//degree sign &#176; &deg;
			$graph->InitFrame();
			$txt= $sensors[$colName][$senNo];//"This\nis\na TEXT!!!";
			$t = new Text($txt,170,$infoStart_Y + $i*70);
			$t->SetFont(FF_ARIAL,FS_BOLD,15);
			$t->SetColor($textColor);
			$t->Align('left','top');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
			
			$txt= number_format(getCurr($sensorId, $username, $password, $serverHostName, $database),1).'°';//"This\nis\na TEXT!!!";
			
			
			$t = new Text($txt,180,$infoStart_Y-32 + $i*70);
			$t->SetFont(FF_ARIAL,FS_BOLD,50);
			$t->SetColor($textColor);
			$t->Align('right','top');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
		
			//$t2->Stroke($graph->img);	// Stroke the text		
		
			$i++;
		}
		
	if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "power")
		{			
			$graph->InitFrame();
			$time = time();
		    $frdate = date('Y-m-d H:i:s',$time-180);
			$todate = date('Y-m-d H:i:s',$time);
			$avg = strval(60*60*getPowerAvg($frdate,$todate,$sensorId,$username,$password,$serverHostName,$database)/1000);			
			$txt= number_format($avg,1);
			$txt2= "kwh";
						
			$t = new Text($txt,360,$infoStart_Y+227);
			$t->SetFont(FF_ARIAL,FS_BOLD,30);
			$t->SetColor($textColor);
			$t->Align('right','bottom');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
			
			
			$t = new Text($txt2,365,$infoStart_Y+227);
			$t->SetFont(FF_ARIAL,FS_BOLD,12);
			$t->SetColor($textColor);
			$t->Align('left','bottom');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
			
			//$t2->Stroke($graph->img);	// Stroke the text			
		}
		
		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "wind")
		{
			$graph->InitFrame();
			$time = time();
			$frdate = date('Y-m-d H:i:s',$time-180);
			$todate = date('Y-m-d H:i:s',$time);
			$avg = strval(2.5*0.44704*getPowerAvg($frdate,$todate,$sensorId,$username,$password,$serverHostName,$database));
			$txt= number_format($avg,1);
			$txt2= "m/s";
		
			$t = new Text($txt,360,$infoStart_Y+227+40);
			$t->SetFont(FF_ARIAL,FS_BOLD,30);
			$t->SetColor($textColor);
			$t->Align('right','bottom');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
				
				
			$t = new Text($txt2,365,$infoStart_Y+227+40);
			$t->SetFont(FF_ARIAL,FS_BOLD,12);
			$t->SetColor($textColor);
			$t->Align('left','bottom');	// How should the text box interpret the coordinates?
			$t->ParagraphAlign('left');	// How should the paragraph be aligned?
			$graph->AddText($t);	// Stroke the text
				
			//$t2->Stroke($graph->img);	// Stroke the text
		}
		
		$senNo++;
	}
	
	
	
	
	$graph->img->SetColor('black');
	$graph->img->Line(250,$infoStart_Y-42,250,350);
	$graph->img->Line(251,$infoStart_Y-42,251,350);
	$graph->img->Line(252,$infoStart_Y-42,252,350);
	$graph->img->Line(253,$infoStart_Y-42,253,350);
	$graph->img->Line(254,$infoStart_Y-42,254,350);
	
	if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($fileName);
		
		$utr = time()-$time;
		print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep($sleepTime);
	}
	
	if(!isCli())
	{
		// Display the graph
		$graph->Stroke();
	}
	
}while (isCli());
	
?>