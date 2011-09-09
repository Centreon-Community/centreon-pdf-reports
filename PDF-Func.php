<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * GPL License: http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * Developped by : 
 *   - Christophe Coraboeuf
 *   - Charles Judith 
 *   - Olivier LI KIANG CHEONG
 *   - Linagora
 */

require_once("class/mypdf.class.php");
require_once("DB-Func.php");

/* pChart library inclusions */
include($centreon_path . "/www/modules/pdfreports/lib/pChart/class/pData.class.php");
include($centreon_path . "/www/modules/pdfreports/lib/pChart/class/pDraw.class.php");
include($centreon_path . "/www/modules/pdfreports/lib/pChart/class/pPie.class.php");
include($centreon_path . "/www/modules/pdfreports/lib/pChart/class/pImage.class.php");

function init_pdf_header() {
  
// First, we define K_TCPDF_EXTERNAL_CONFIG 
//define ('K_TCPDF_EXTERNAL_CONFIG', true);

//define ('PDF_HEADER_LOGO', "../../../img/header/centreon.gif");    
  
}

//function pdfGen($group_name, $start_date, $end_date,$stats,$l,$logo_header, $chart_img){
function pdfGen($group_name, $mode = NULL, $start_date, $end_date,$stats,$l,$title){
	
		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator("PDF Reports Module");
		$pdf->SetAuthor(getGeneralOptInfo("pdfreports_report_author"));
		//$pdf->SetAuthor('Fully Automated Nagios');

		$pdfTitle = $title . " " . $group_name;
		//$pdfTitle = "Rapport de supervision du hostgroup ".$group_name; 
		$pdf->SetTitle($pdfTitle);
		//$pdf->SetSubject('TCPDF Tutorial');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// define default header data
		$header = $title . " " .$group_name;
		//$header = "Rapport de supervision du hostgroup ".$group_name;
		//$ip = $_SERVER['HOSTNAME'];
		$startDate = date("d/m/Y", $start_date);
		$time = time();
		$endDate = date("d/m/Y", $time );
		$string = _("From") ." ".strftime("%A",$start_date). " ".$startDate." "._("to") ." ".strftime("%A",$time)." ".$endDate."\n";
		// set default header data
		
		$pdf->SetHeaderData('../../../img/headers/' . getGeneralOptInfo("pdfreports_report_header_logo") , PDF_HEADER_LOGO_WIDTH, $header,$string);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

		//set some language-dependent strings
		$pdf->setLanguageArray($l); 

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('helvetica', '', 12);

		// add a page
		$pdf->AddPage();

		//Column titles
		$header = array('Status', 'Time', 'Total Time', 'Mean Time', 'Alert');

		// Pie chart Generation
		$piechart_img = pieGen($stats,$mode);
		
		//Data loading
		$data = $pdf->LoadData($stats);

		// print colored table
		//$pdf->ColoredTable($header, $data,$chart_img);
		if ($mode == "hgs") { // Hostgroup
			$pdf->ColoredTable($header, $data,$piechart_img);
		} else if ($mode == "sgs") { // Servicegroup
			$pdf->ServicesColoredTable($header, $data,$piechart_img);	
		}

		// ---------------------------------------------------------

		//génération d'un nom de pdf
		$endDay = date("d", $time);
		$endYear = date("Y", $time);
		$endMonth = date("m", $time);
		$pdfDirName = getGeneralOptInfo("pdfreports_path_gen") . $endYear.$endMonth.$endDay . "/";
		$pdfFileName =  $pdfDirName .$endYear."-".$endMonth."-".$endDay."_".$group_name.".pdf";
		
		if (!is_dir($pdfDirName))
			mkdir($pdfDirName);
		
		//Close and output PDF document
		$pdf->Output($pdfFileName, 'F'); 

		return $pdfFileName;

}

/*
* Including pie chart in report (like dashboard)
*/
function pieGen($stats, $mode) {
	
	// Create and populate the pData object 
	 $MyData = new pData();   
	 
	// print_r($stats["average"]);
	
	$arrPoints = array();
	$i=0;
	// Host groups
	if ($mode == "hgs" ) { 
	 
	 $MyData->addPoints(array($stats["average"]["UP_TP"],
				  $stats["average"]["DOWN_TP"],
				  $stats["average"]["UNDETERMINED_TP"],
				  $stats["average"]["UNREACHABLE_TP"],
				  $stats["average"]["MAINTENANCE_TP"]),
				"Hostgroups");  
	 $MyData->setSerieDescription("Hostgroups","Hostgroups");
	

	$arrPoints = array("Up",
				  "Down",
				  "Undeterminded",
				  "Unreachable",
				  "Schedule Downtime");		

	// Define the absissa serie 
	 $MyData->addPoints($arrPoints,	"Labels");
	 $MyData->setAbscissa("Labels");					  
				  
				  
	 // Create the pChart object 
	 $myPicture = new pImage(120,120,$MyData,TRUE);				  
	 // Create the pPie object  
	 $PieChart = new pPie($myPicture,$MyData);			  
				  
	$i = 0;
	/* Define the slice color */ 
	if ($stats["average"]["UP_TP"] > 0 ) { 
		$PieChart->setSliceColor($i,array("R"=>25,"G"=>238,"B"=>17));  // UP
		$i++;
		}
	if ($stats["average"]["DOWN_TP"] > 0 ) {
		$PieChart->setSliceColor($i,array("R"=>249,"G"=>30,"B"=>5));   // DOWN
		$i++;
		}
	if ($stats["average"]["UNDETERMINED_TP"] > 0 ) 	{
		$PieChart->setSliceColor($i,array("R"=>204,"G"=>248,"B"=>255));// UNDETERMINED
		//$PieChart->setSliceColor($i,array("R"=>240,"G"=>240,"B"=>240)); 
		$i++;
		}
	if ($stats["average"]["UNREACHABLE_TP"] > 0 ) 	{
		$PieChart->setSliceColor($i,array("R"=>130,"G"=>207,"B"=>216)); // UNREACHABLE
		$i++;
		}
	if ($stats["average"]["MAINTENANCE_TP"] > 0 ) 	{
		$PieChart->setSliceColor($i,array("R"=>204,"G"=>153,"B"=>255));	// MAINTENANCE
		$i++;
		}
	  
	}

	// Service Groups
	if ($mode == "sgs" ) { 
	 
	 $MyData->addPoints(array($stats["average"]["OK_TP"],
				  $stats["average"]["WARNING_TP"],
				  $stats["average"]["CRITICAL_TP"],
				  $stats["average"]["UNKNOWN_TP"],
				  $stats["average"]["MAINTENANCE_TP"],
				  $stats["average"]["UNDETERMINED_TP"]),
				"Servicegroups");  
	 $MyData->setSerieDescription("Servicegroups","Servicegroups");
	

	$arrPoints = array("Ok",
				  "Warning",
				  "Critical",
				  "Unknown",
				  "Schedule Downtime",
				  "Undeterminded");

		// Define the absissa serie 
	 $MyData->addPoints($arrPoints,	"Labels");
	 $MyData->setAbscissa("Labels");			  
				  
	 // Create the pChart object 
	 $myPicture = new pImage(120,120,$MyData,TRUE);	
	 // Create the pPie object  
	 $PieChart = new pPie($myPicture,$MyData);
	
	
	
	/* Define the slice color */ 
	if ($stats["average"]["OK_TP"] > 0 ) {
		$PieChart->setSliceColor($i,array("R"=>13,"G"=>235,"B"=>58));  // OK
		$i++;
		}
	if ($stats["average"]["WARNING_TP"] > 0 ) { 	
		$PieChart->setSliceColor($i,array("R"=>248,"G"=>199,"B"=>6));   // DOWN 
		$i++;
		}
	if ($stats["average"]["CRITICAL_TP"] > 0 ) 	{ 	
		$PieChart->setSliceColor($i,array("R"=>249,"G"=>30,"B"=>5));   // DOWN
		$i++;
		}
	if ($stats["average"]["UNKNOWN_TP"] > 0 ) {		
		$PieChart->setSliceColor($i,array("R"=>220,"G"=>218,"B"=>218)); // UNKNOWN
		$i++;
		}
	if ($stats["average"]["MAINTENANCE_TP"] > 0 ) { 	
		$PieChart->setSliceColor($i,array("R"=>204,"G"=>153,"B"=>255));	// MAINTENANCE
		$i++;
		}
	if ($stats["average"]["UNDETERMINED_TP"] > 0 ) 	{ 
		$PieChart->setSliceColor($i,array("R"=>204,"G"=>248,"B"=>255)); // UNDETERMINED
		//$PieChart->setSliceColor($i,array("R"=>240,"G"=>240,"B"=>240)); // UNDETERMINED		
		$i++;
		}	  
	}		 
	
	
	$Settings = array("R"=>255, "G"=>255, "B"=>255);
	$myPicture->drawFilledRectangle(0,0,120,120,$Settings);
	
	//  Enable shadow computing  
	// $myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
	
	 // Draw a splitted pie chart 
	 $PieChart->draw3DPie(60,70,array("Radius"=>50,"DataGapAngle"=>8,"DataGapRadius"=>6,"Border"=>TRUE,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));
	// $PieChart->draw2DPie($myPicture,$MyData,60,70,array("DataGapAngle"=>12,"DataGapRadius"=>10,"Border"=>TRUE,"BorderR"=>255,"BorderG"=>255,"BorderB"=>255));
	//$PieChart->draw2DPie(60,70,array("SecondPass"=>FALSE));
	//$PieChart->draw2DPie(60,60,array("SecondPass"=>TRUE,"Border"=>TRUE,"BorderR"=>0,"BorderG"=>0,"BorderB"=>0));
	 /* Render the picture (choose the best way) */
	 
	$pie_file = tempnam( "/tmp" , "reportreon_pie_" ); 
	 
	$myPicture->autoOutput($pie_file . ".png");
	// $centreon_path . "/www/modules/pdfreports/example.draw3DPie.transparent.png"

	return  $pie_file  . ".png" ;
}




?>
