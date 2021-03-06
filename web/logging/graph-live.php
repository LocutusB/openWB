<?php
session_start();
require_once "/var/www/html/openWB/web/class/pDraw.class.php";
require_once "/var/www/html/openWB/web/class/pImage.class.php";
require_once "/var/www/html/openWB/web/class/pData.class.php";
	$speichervorhanden = file_get_contents('/var/www/html/openWB/ramdisk/speichervorhanden');
$evufile = '/var/www/html/openWB/ramdisk/evu.graph';
$pvfile = '/var/www/html/openWB/ramdisk/pv.graph';
$evfile = '/var/www/html/openWB/ramdisk/ev.graph';
$timefile = '/var/www/html/openWB/ramdisk/time.graph';
$socfile = '/var/www/html/openWB/ramdisk/soc.graph';

$EV = file($evfile, FILE_IGNORE_NEW_LINES);
$EVU = file($evufile, FILE_IGNORE_NEW_LINES);
$PV = file($pvfile, FILE_IGNORE_NEW_LINES);
$timef = file($timefile, FILE_IGNORE_NEW_LINES);
$SOC = file($socfile, FILE_IGNORE_NEW_LINES);
if ($speichervorhanden == 1) {
	$speicherfile = '/var/www/html/openWB/ramdisk/speicher.graph';
}
$myData = new pData();
$myData->addPoints($EV,"EV");
$myData->addPoints($EVU,"EVU");
$myData->addPoints($PV,"PV");
$myData->addPoints($SOC, "SoC");
if ($speichervorhanden == 1) {
	$SPEICHER = file($speicherfile, FILE_IGNORE_NEW_LINES);
	$myData->addPoints($SPEICHER, "Speicher");
}
$highest1 = max($EVU);
$highest = max($EV);
$highest2 = max($PV);
$highest = max($highest,$highest1,$highest2);
$lowestu = min($EVU);
$lowest = min($PV);
if ($speichervorhanden == 1) {
	$lowest = min($SPEICHER);
}
$lowestg = min($lowest,$lowestu);
$socl = (min($SOC) - 5);
if ($socl < "0" ){
	$minsoc = 0;
} else {
	$minsoc = $socl;
}
$myData->setSerieOnAxis("EV",0);
$myData->setSerieOnAxis("EVU",0);
$myData->setSerieOnAxis("PV",0);
$myData->setSerieOnAxis("SoC",1);
$myData->setPalette("EV",array("R"=>0,"G"=>0,"B"=>254));
$myData->setPalette("EVU",array("R"=>254,"G"=>0,"B"=>0));
$myData->setPalette("PV",array("R"=>0,"G"=>254,"B"=>0));
if ($speichervorhanden == 1) {
	$myData->setSerieOnAxis("Speicher",0);
	$myData->setPalette("Speicher",array("R"=>252,"G"=>190,"B"=>50));
}
$myData->addPoints($timef,"Labels");
$myData->setSerieOnAxis("Labels",0);
$myData->setSerieDescription("Labels","Uhrzeit");
$myData->setAbscissa("Labels");
$myData->setAxisPosition(1,AXIS_POSITION_RIGHT);
$myData->setAxisName(0,"Watt");
$AxisBoundaries = array(0=>array("Min"=>$lowestg,"Max"=>$highest),1=>array("Min"=>$minsoc,"Max"=>(max($SOC) + 5)));
$ScaleSettings  = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"LabelSkip"=>100);



$myImage = new pImage(950, 400, $myData);


$myImage->setFontProperties(array(
    "FontName" => "/var/www/html/openWB/web/fonts/GeosansLight.ttf",
    "FontSize" => 16));
$myImage->setGraphArea(75,25, 895,375);
$myImage->drawScale($ScaleSettings);


$myData->setSerieDrawable("PV",false);
$myData->setSerieDrawable("EVU",false);
if ($speichervorhanden == 1) {
	$myData->setSerieDrawable("Speicher",true);
}
$myImage->drawLegend(460,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL, "Family"=>LEGEND_FAMILY_LINE));



$myImage->drawLineChart();
if ($speichervorhanden == 1) {
	$myData->setSerieDrawable("Speicher",false);
}
$myData->setSerieDrawable("SoC",false);
$myData->setSerieDrawable("PV",true);
$myData->setSerieDrawable("EV",false);
$myData->setSerieDrawable("EVU",true);
$myImage->drawAreaChart();


$myImage->drawLegend(360,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));



header("Content-Type: image/png");
$myImage->autoOutput('/var/www/html/openWB/ramdisk/chart-m.png');
