<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ('jpgraph/jpgraph_plotline.php');
require_once ("jpgraph/jpgraph_date.php"); 
require_once ("jpgraph/jpgraph_regstat.php");
require_once ("jpgraph/jpgraph_bar.php");
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_pie.php');
require_once ('jpgraph/jpgraph_mgraph.php');
require_once ('jpgraph/jpgraph_utils.inc.php'); 

include ("homeFunctions.php");

include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_bar.php");
include ("../jpgraph_mgraph.php");

$backGroundClr  = 'gray:0.43';


//------------------------------------------------------------------
// Create some random data for the plot. We use the current time for the
// first X-position
//------------------------------------------------------------------

$width = 600;
$hight = $width/2;


//----------------------
// Setup the canvas
//----------------------

// Setup a basic canvas we can work 
$g = new CanvasGraph($width,$hight,'auto');
$g->SetMargin(0,0,0,0);
$g->SetFrame(false,'white',2);

// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.
$g->InitFrame();
// .. and a circle (x,y,diameter)
$g->img->SetColor('gray:0.55');
$g->img->FilledCircle($width/2,$width*0.44,$width*0.283);
$g->img->SetTransparent("white"); 

//----------------------
// add recangle
//----------------------

$p = array( 20,$width*0.42,$width,$width*0.42,
            $width,$hight,20,$hight); 
$g->img->SetColor('gray:0.55');
$g->img->FilledPolygon($p);

//----------------------
// add needle
//----------------------
$deg_max= 180;
$deg_min= 0;
$max    = 20;
$min    = 0 ;
$tics   = $max;

for($XX =0 ; $XX<20;$XX=$XX+1)
{
    $avgMax=$XX;
    $deg = $avgMax*$deg_max/($max-$min)-$min*$deg_max/($max-$min);
    $baseLength=10;
    $ticScaleL=1.09;
    $length = 100;
    $X_start = $width/2;
    $Y_start = $width*0.42;

    $X_B_coord= ($baseLength*cos(deg2rad(90-$deg)));
    $Y_B_coord= (($baseLength)*sin(deg2rad(90-$deg)));   
    $X_coord  = ($ticScaleL*$length*cos(deg2rad($deg)));
    $Y_coord  = (($ticScaleL*$length)*sin(deg2rad($deg)));

    $X_stop_deg= $X_offset + $X_coord;
    $Y_stop_deg= $Y_start - $Y_coord;

    $p = array( $X_start-$X_B_coord,$Y_start-$Y_B_coord, 
                $X_stop_deg,$Y_stop_deg, 
                $X_start+$X_B_coord,$Y_start+$Y_B_coord,
                $X_start,$Y_start); 
    $g->img->SetColor('brown4');
    $g->img->FilledPolygon($p);
    $g->img->FilledCircle($X_start,$Y_start,$baseLength-0);
}


//----------------------
// Setup the pieplot
//----------------------
// Some data
//$data =  array(12.5,12.5,12.5,12.5,50);
//$color = array('red','orange','yellow','green',$backGroundClr);
$data =  array(10,10,10,10,10,50);
$color = array('red','orange','yellow','green','springgreen4',$backGroundClr);

// Create the Pie Graph. 
$pgraph = new PieGraph($width,$hight);
$pgraph->SetColor($backGroundClr);

//$theme_class="DefaultTheme";
//$graph->SetTheme(new $theme_class());

// Create
$p1 = new PiePlot($data);
$p1->value->Show(false); 
$p1->ShowBorder();
$p1->SetColor('black');
$p1->SetSliceColors($color);
$p1->SetSize($width*0.4);
$p1->SetCenter(0.5,0.9);
$pgraph->Add($p1);


//-----------------------
// Create a multigraph
//----------------------
$mgraph = new MGraph();
$mgraph->SetImgFormat('png',60);
$mgraph->SetMargin(0,0,0,0);
$mgraph->SetFrame(true,'darkgray',2);
$mgraph->AddMix($pgraph,0,0,85);
$mgraph->AddMix($g,0,0,100);

$gdImgHandler = $mgraph->Stroke(_IMG_AUTO);



	
?>