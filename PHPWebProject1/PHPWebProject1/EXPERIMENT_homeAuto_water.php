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
    $path = "..\\pictures\\".$fileName;
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
	$senNo = 0;
	
	foreach($sensors[$colID] as $sensorId)
	{
		if($sensors[$colType][$senNo] == "water"  && $sensors[$colName][$senNo] == "water")
		{		
			$flowSumArray			= array(0,0,0,0,0,0,0,0,0,0,0,0);
			$flowXArray				= array();
			$plotArray				= array();
			$daysInMonth 			= array(31,29,31,30,31,30,31,31,30,31,30,31);
			$months 				= array('J','F','M','A','M','J','J','A','S','O','N','D');
						
			$graph = new Graph(300,187,'auto');
			$graph->SetMargin(43,15,20,30);
			$graph->SetScale("textlin", 0, 3500);
			$graph->SetBox(false);
			$graph->ygrid->SetFill(false);
			$graph->xaxis->SetTickLabels($months);
			$graph->yaxis->HideLine(false);
			$graph->yaxis->HideTicks(false,false);
			
            
            $graph->xaxis->SetLabelAngle(0);
            $graph->yaxis->SetTitleMargin(18);
            $graph->yaxis->SetColor('black:1.5','gray'); 
            $graph->xaxis->SetColor('black:1.5','gray'); 
            $graph->xaxis->SetTitleMargin(1);
            $graph->xaxis->SetFont(FF_VERDANA, FS_BOLD);	
            $graph->yaxis->SetFont(FF_VERDANA, FS_BOLD);
            $graph->xgrid->SetColor('black:1.5');
            $graph->ygrid->SetColor('black:1.5');
            $graph->SetColor('gray:0.43');
            $graph->SetBackgroundGradient('black:1.1','black:1.1',GRAD_HOR,BGRAD_MARGIN);
            
            
            
			$year = date("Y");
						
			for($j=0; $j<1 ;$j++)
			{		
				for($i=0; $i<12 ;$i++)
				{
					$fdate = "$year-".zeroAdjust($i+1)."-01";
					$tdate = "$year-".zeroAdjust($i+1)."-".$daysInMonth[$i];
					$flowSumArray[$i] =  getMax($fdate, $tdate, $sensorId, $username, $password, $serverHostName, $database);
                    
                    
					if($flowSumArray[$i]==null)
						$flowSumArray[$i] = 0;
                    else
                        $flowSumArray[$i] =  getMax($fdate, $tdate, $sensorId, $username, $password, $serverHostName, $database)-getMin($fdate, $tdate, $sensorId, $username, $password, $serverHostName, $database);
			
					$flowXArray[$i]   = $tdate;	
				}
			
				$b1plot= new BarPlot($flowSumArray);							
				$b1plot->value->SetFormat('%d');
				$b1plot->value->Show();
				$b1plot->value->SetColor('gray');
				$b1plot->SetColor('black:1.3');
				$b1plot->SetFillColor('blue');
				$plotArray[] =$b1plot;
                $graph->title->SetColor('gray');
				$graph->title->Set("Liter/month");
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