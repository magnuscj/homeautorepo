<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php");
require_once ("jpgraph/jpgraph_regstat.php");
include ("homeFunctions.php");


$file = explode('.', __FILE__);
$file = explode('\\', $file[0]);
$fileName = $file[sizeof($file)-1].".png";

if(isCli())
{
    $path = "pictures\\".$fileName;
    $path2 = "J:\\www\\pictures\\".$fileName;
    $sleepTime = getConfig("SLEEP")+20;
}
else
{
    $path = "..\\pictures\\".$fileName;
    $path2 = "J:\\www\\pictures\\".$fileName;
    $sleepTime = 60;
}


do
{
	if(isCli())
	{
		$time = time();
		print "\n".date('H:i:s',$time).", Working \n";
	}
	// Create the graph.
	$graph = new Graph(998,700);
	$graph->SetMargin(35,100,80,110);				//SetMargin($lm, $rm, $tm, $bm)
	$graph->SetScale("datint");

	$graph->legend->SetLayout(LEGEND_HOR);			// Adjust the legend position
	$graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
	$graph->legend->SetFillColor('gray:0.43');
	$graph->legend->SetColumns(2);					// Set number of colums for legend
	$graph->legend->Pos(0.5,0.05,"center","top");
    $graph->legend->SetColor('gray', 'gray');

	$graph->xaxis->SetPos("min");
	$graph->xaxis->title->Set("Datum" );
	$graph->xaxis->scale-> SetDateFormat('d/m H:i');
	$graph->xaxis->SetLabelAngle(80);
	$graph->xaxis->SetTitleMargin(70);
    $graph->xaxis->SetColor('black:1.5','gray');
    $graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL);
    $graph->xgrid->SetColor('black:1.5');
	$graph->xgrid->Show(true);

    $graph->yaxis->title->SetColor('gray');
    $graph->yaxis->SetColor('black:1.5','gray');
    $graph->yaxis->SetFont(FF_VERDANA, FS_NORMAL);
    $graph->ygrid->SetColor('black:1.5');

    $graph->SetColor('gray:0.43');
    $graph->SetBackgroundGradient('black:1.1','black:1.1',GRAD_HOR,BGRAD_MARGIN);

	$username		= getConfig("DBUSN");
	$password		= getConfig('DBPSW');
	$database		= getConfig('DBNAME');
	$serverHostName	= getConfig('DBIP');
    waitDbAlive($serverHostName,$username,$password,$database);
	$sensors 		= getSensorNames($username,$password,$database, $serverHostName); //From sensor configuration

	//Index names for the sensor configuration db table
	$colID			= 0;
	$colName		= 1;
	$colColor		= 2;
	$colVisible		= 3;
	$colType		= 4;
	$noOfFlowGraphs = 0;
	$noOf_Y_FlowGraphs = 0;
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

	$ttime = $ftime = date('H:i',time());;

	if(isset($_GET['fromdate']))
	{
		$ftmp  = split(" ", $_GET['fromdate']);
		$fdate = $ftmp[0];
		if(sizeof($ftmp)== 2)
			$ftime = $ftmp[1];

		$ttmp  = split(" ", $_GET['todate']);
		$tdate = $ttmp[0];
		if(sizeof($ttmp)== 2)
			$ttime = $ttmp[1];

	}
	else
	{
		$fdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
		$tdate = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
	}


	$graph->title->Set($fdate." ".$ftime." ---> ".$tdate."  ".$ttime);
    $graph->title->SetColor('gray');

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

			mysql_close();
		}
		$sensors 		= getSensorNames($username,$password,$database,$serverHostName);
	}

	$graphArray = array();
	$senNo = 0;

	foreach($sensors[$colID] as $sensorId)
	{
		$ydata_temptot = array();
		$xdata_timeTot = array();

		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "temp")
		{
			$retXY = getDataFromDb($username, $password, $database, $fdate." ".$ftime, $tdate." ".$ttime, $sensorId, $serverHostName);
            /*EXPERIMENT*/
            $retXY = reduceData(sizeof($retXY[0])/1500, $retXY);

			$minimum = number_format(getMin($fdate,$tdate,$sensorId,$username,$password,$serverHostName,$database),1);
			$maximum = number_format(getMax($fdate,$tdate,$sensorId,$username,$password,$serverHostName,$database),1);
			$retXY[0] = floatAvg(10, $retXY[0]);
			$graph->yaxis->title->Set("Grader");

            if(sizeof($retXY[0])>0 && sizeof($retXY[1])>0 && (sizeof($retXY[0])== sizeof($retXY[1])))
			{
                $lineplot2=new LinePlot($retXY[0], $retXY[1]);
			    $lineplot2->SetColor($sensors[$colColor][$senNo]);
			    $lineplot2->SetWeight(2);
			    $lineplot2->SetLegend($sensors[$colName][$senNo].", Max/Min/Cur :  ".$maximum."/".$minimum."/".number_format(getCurr($sensorId, $username, $password, $serverHostName, $database),1));

			    $graph->Add($lineplot2);
			    $noOfFlowGraphs 		+= 1;
            }
		}

		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "wind")
		{
			$retXY = deltaChange(windAddMissingTime(getDataFromDb($username, $password, $database, $fdate." ".$ftime, $tdate." ".$ttime, $sensorId, $serverHostName)))	;
			$retXY = windMilesTometers($retXY);
			$retXY[0] = floatAvg(15, $retXY[0]);

			$lineplot2=new LinePlot($retXY[0], $retXY[1]);
			$lineplot2->SetColor($sensors[$colColor][$senNo]);
			$lineplot2->SetWeight(4);

			$graph->AddY($noOf_Y_FlowGraphs,$lineplot2);
			$graph->SetYScale($noOf_Y_FlowGraphs,'lin',0,10);
			$graph->ynaxis[$noOf_Y_FlowGraphs]->SetColor('teal');
			$graph->ynaxis[$noOf_Y_FlowGraphs]->title->Set('mph');
			$graph->ynaxis[$noOf_Y_FlowGraphs]->title->SetMargin(11);
			$graph->ynaxis[$noOf_Y_FlowGraphs]->scale->ticks->Set(1,0.5);
			$graph->ynaxis[$noOf_Y_FlowGraphs]->SetColor('navy');
			$graph->ynaxis[$noOf_Y_FlowGraphs]->SetTitleSide('right');
			$noOf_Y_FlowGraphs 		+= 1;
		}


		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "power")
		{
			$retXY = deltaChange(addMissingTime(getDataFromDb($username, $password, $database, $fdate." ".$ftime, $tdate." ".$ttime, $sensorId, $serverHostName)));
            $sum = sum($retXY[0], TRUE);
            /*EXPERIMENT*/
            $retXY = reduceData(sizeof($retXY[0])/1000, $retXY);
			$lineplot2=new LinePlot(floatAvg(5, $retXY[0]),$retXY[1] );
			$lineplot2->SetColor($sensors[$colColor][$senNo]);
			$lineplot2->SetFillGradient('red@0.4','orange@0.4');
			$lineplot2->SetLegend($sensors[$colName][$senNo]." :  ".strval(number_format($sum/1000,1))." kwh" );

			if(!onlyPowerType($sensors))
			{
				$graph->AddY($noOf_Y_FlowGraphs,$lineplot2);
				$graph->SetYScale($noOf_Y_FlowGraphs,'lin',0,300);
				$graph->ynaxis[$noOf_Y_FlowGraphs]->SetColor('teal');
				$graph->ynaxis[$noOf_Y_FlowGraphs]->title->Set('wh');
                $graph->ynaxis[$noOf_Y_FlowGraphs]->title->SetColor('orange');
				$graph->ynaxis[$noOf_Y_FlowGraphs]->title->SetMargin(11);
				$graph->ynaxis[$noOf_Y_FlowGraphs]->scale->ticks->Set(20,10);
				$graph->ynaxis[$noOf_Y_FlowGraphs]->SetColor('orange');
				$graph->ynaxis[$noOf_Y_FlowGraphs]->SetPos('max');
				$graph->ynaxis[$noOf_Y_FlowGraphs]->SetTitleSide('right');
				$noOf_Y_FlowGraphs 		+= 1;
			}
			else
			{
				$graph->Add($lineplot2);
				$graph->SetScale('lin',0,300);
				$graph->title->Set('wh');
				$graph->title->SetMargin(9);
				$graph->SetColor('khaki:1.5');
				$graph->yaxis->title->Set("kwh" );
				$noOfFlowGraphs 		+= 1;
			}
		}

		if($sensorShow[$senNo] == "on" && $sensors[$colType][$senNo] == "rain")
		{
			$retXY = deltaChange(addMissingTime(getDataFromDb($username, $password, $database, $fdate." ".$ftime, $tdate." ".$ttime, $sensorId, $serverHostName)));
			//$lineplot2=new LinePlot(scaleChange(0.25 , $retXY[0]),$retXY[1] );
            $lineplot2=new LinePlot($retXY[0],$retXY[1] );
			$lineplot2->SetColor($sensors[$colColor][$senNo]);
			$lineplot2->SetFillGradient('royalblue4','royalblue4');
			$lineplot2->SetLegend($sensors[$colName][$senNo]." :   ".strval(number_format(sum($retXY[0], TRUE)*0.254,1))." mm" );

			if($noOfFlowGraphs>=0)
			{
				$graph->AddY(1,$lineplot2);
				$graph->SetYScale(1,'lin',0,5);
				$graph->ynaxis[1]->SetColor('teal');
				$graph->ynaxis[1]->title->Set('mm');
				$graph->ynaxis[1]->title->SetMargin(10);
				$noOfFlowGraphs 		+= 1;
			}
			else
			{
				$graph->Add($lineplot);
				$graph->yaxis->title->Set("mm" );
				$graph->SetScale('lin',0,2);
				$graph->title->SetMargin(11);
				$graph->SetColor('khaki:1.5');
				$noOfFlowGraphs 		+= 1;
			}
		}
		$senNo++;
	}


	if(isCli())
	{

		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		//$graph->img->Stream($path);
		$graph->img->Stream($path2);
		$utr = time()-$time;
		//print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep($sleepTime);
	}
	else
	{

        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		//$graph->img->Stream($path);
        $graph->img->Stream($path2);
		sleep($sleepTime);
    }

}while(true);

?>