<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php");
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
include ("homeFunctions.php");


$file = explode('.', __FILE__);
$file = explode('\\', $file[0]);
$fileName = $file[sizeof($file)-1].".png";

if(isCli())
{
    $path = "pictures\\".$fileName;
    $sleepTime = getConfig("SLEEP")+20;
}
else
{
    $path = "pictures\\".$fileName;
    $sleepTime = 60;
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
    waitDbAlive($serverHostName,$username,$password,$database);
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
	$lineColor      = array("orange", "green","red", "blue");
	$flowPreArray			= array(1);
	$flowXArray				= array();
	$plotArray				= array();
	$daysInMonth 			= array(31,29,31,30,31,30,31,31,30,31,30,31);
	$months 				= array('J','F','M','A','M','J','J','A','S','O','N','D');
	$td 					= date("d", mktime(0,0,0,date("d")));
	$thisMonth 				= date("m", mktime(0,0,0,date("m")));
	$maxYearsinGraph		= 1;

	$graph = new Graph(320,187,'auto');
	$graph->SetMargin(45,15,20,30);
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

    $graph->xaxis->SetPos("min");
    $graph->xgrid->Show(true);

    $graph->xaxis->SetLabelAngle(0);
    $graph->yaxis->SetTitleMargin(18);
    $graph->yaxis->title->SetColor('gray');
    $graph->yaxis->SetColor('black:1.5','gray');
    $graph->xaxis->SetColor('black:1.5','gray');
    $graph->xaxis->SetTitleMargin(1);
    $graph->xaxis->SetFont(FF_VERDANA, FS_BOLD);
    $graph->yaxis->SetFont(FF_VERDANA, FS_BOLD);
    $graph->xgrid->SetColor('black:1.5');
    $graph->ygrid->SetColor('black:1.5');
    $graph->SetColor('gray:0.43');
    $graph->SetBackgroundGradient('black:1.1','black:1.1',GRAD_HOR,BGRAD_MARGIN);


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
		if($sensors[$colType][$senNo] == "power"  && $sensorId == "C90000000DB76D1D")
		{
			for($j=0; $j<4 ;$j++)
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

				$b1plot = new LinePlot($flowSumArray);
				$b1plot->value->SetColor('black');
				$b1plot->SetColor($lineColor[$j]);
                $b1plot->SetWeight(3);
                $b1plot->SetLegend("$year");
				$graph->title->Set("kwh/month");
                $graph->title->SetColor('gray');
			    $graph->Add($b1plot);

				$year = $year - 1;
			}

            $graph->legend->SetPos(0.7,0.35,'left','bottom');
            $graph->legend->SetLayout(LEGEND_HOR);			// Adjust the legend position
            $graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
            $graph->legend->SetFillColor('#FFFFCC');
            $graph->legend->SetColumns(1);					// Set number of colums for legend
            $graph->legend->Hide(false);
			$graph->legend->SetFrameWeight(2);
		}
		//Plot power Stop
		$senNo++;
	}

	if(isCli())
	{
		$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($path);
		$utr = time()-$time;
		print date('H:i:s',time()).", finished, it took "."$utr"." seconds.\n\n";
		sleep(3600);
	}
    else
    {
        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
		$graph->img->Stream($path);
		sleep($sleepTime);
    }


}while (true);
?>