<?php
date_default_timezone_set('Europe/Stockholm');
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
include ("jpgraph/jpgraph_date.php"); 
include ("jpgraph/jpgraph_regstat.php");
include ("homeFunctions.php");



$xmlinfo1 = simplexml_load_file("http://192.168.1.128/details.xml");
$xmlinfo2 = simplexml_load_file("http://192.168.1.87/details.xml");	
$xmlinfo3 = simplexml_load_file("http://192.168.1.84/details.xml");
$out = array ();
$out = xml2array ( $xmlinfo2, $out );
$test = $out[owd_DS18B20];
$test = $out[owd_DS18B20][Temperature];


function xml2array ( $xmlObject, $out = array () )
{
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ||  is_array ( $node ) ) ? xml2array ( $node ) : $node;

        return $out;
}
	
?>
