<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_date.php"); 
include ("jpgraph/jpgraph_regstat.php");
include ("homeFunctions.php");

$fileName = "pictures\homeAutoGraphMob.png";

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


	// Create the graph.
	$graph = new Graph(395,219);
	$graph->SetMargin(23,23,10,25);
	$graph->SetScale("datint");
	$graph->xaxis->SetPos("min");
	$graph->xgrid->Show(true);
	$graph->xaxis->scale-> SetDateFormat('H'); 
	$graph->xaxis->SetLabelAngle(0);
	$graph->yaxis->SetTitleMargin(20);
	$graph->yaxis->SetColor('black:1.5','gray'); 
	$graph->xaxis->SetColor('black:1.5','gray'); 
	$graph->xaxis->SetTitleMargin(70);
	$graph->xaxis->SetFont(FF_VERDANA, FS_BOLD);	
	$graph->yaxis->SetFont(FF_VERDANA, FS_BOLD);
	$graph->xgrid->SetColor('black:1.5');
	$graph->ygrid->SetColor('black:1.5');
	$graph->SetColor('gray:0.43');
	$graph->SetBackgroundGradient('black:1.1','black:1.1',GRAD_HOR,BGRAD_MARGIN);
	
	
	//Default values
	$past = mktime(0,0,0,date("m")-0,date("d")-1,date("Y"));
	$fdate = date("Y-m-d", $past);
	$tdate = date("Y-m-d");
		
	$color 			= array("black", "blue","red","green","brown");
	$max			= array("-",  "-","-","-","-");
	$min			= array("-",  "-","-","-","-");
	$curr			= array("-",  "-","-","-","-");
	$username		= getConfig("DBUSN");
	$password		= getConfig('DBPSW');
	$database		= getConfig('DBNAME');
	$serverHostName	= getConfig('DBIP');
	$sensors 		= getSensorNames($username,$password,$database,$serverHostName);
	$sensorToSow	= "Ute"; //make all sensors invisible but one
	
	
	$i=0;
	foreach($sensors[1] as $sensorName) //make all sensors invisible but one
	{
		$sensorShow[$i] 	= "off";	
		if($sensorName == $sensorToSow)
			$sensorShow[$i] = "on"; //Make this one visible
		$i++;
	}
	
	$fdate 		= date("Y-m-d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$tdate 		= date("Y-m-d", mktime(0,0,0,date("m")+0,date("d"),date("Y")));		
	$Nowtime 	= date('H:i:s',time());
	
	
	$fsplited 	= preg_split ( '/-/' ,$fdate  );
	$tsplited 	= preg_split ( '/-/' ,$tdate  );
	
	$frommonth = (int)$fsplited[1];
	$tomonth   = (int)$tsplited[1];
	$fromyear  = (int)$fsplited[0];
	$toyear	   = (int)$tsplited[0];
	
	$i = 0;	
	foreach($sensors[0] as $sensorId)
	{
		if($sensorShow[$i] == "on")
		{
			$ydata_temptot = array();
			$xdata_timeTot = array();
			$ttime = $ftime = date('H:i',time());
			//$retXY = getTimeDate($fromyear,$toyear, $frommonth, $tomonth,$username,$password,$database, $fdate,$tdate,$sensorId,$Nowtime,$Nowtime,$serverHostName);
			$retXY = getDataFromDb($username, $password, $database, $fdate." ".$ftime, $tdate." ".$ttime, $sensorId, $serverHostName)	;
			 		
			$ydata_temptot  = 		$retXY[0];
			$xdata_timeTot  = 		$retXY[1];
				
			if(($ydata_temptot && $xdata_timeTot) && (($ydata_temptot[0] && $xdata_timeTot[0])))
			{
				$ydata_temptot = floatAvg(10, $ydata_temptot);
				$lineplot=new LinePlot($ydata_temptot, $xdata_timeTot);
				$sensors[2][$i] ='orange';
				$lineplot->SetColor($sensors[2][$i]);
				$lineplot->SetWeight(3);
				$graph->Add($lineplot);

				$graph->SetY2Scale("int");
				$graph->AddY2($lineplot);
				$graph->y2axis->SetColor('black:1.5','gray'); 
				$graph->y2axis->SetFont(FF_VERDANA, FS_BOLD);
			}
		}
		$i++;
	}

	
	if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($fileName);
	
		$utr = time()-$time;
		print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep(60*30);
	}
	
	if(!isCli())
	{
		// Display the graph
		$graph->Stroke();
	}
}while (isCli());
?>
