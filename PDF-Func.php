<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */



require_once("class/mypdf.class.php");


function init_pdf_header() {
  
// First, we define K_TCPDF_EXTERNAL_CONFIG 
//define ('K_TCPDF_EXTERNAL_CONFIG', true);

//define ('PDF_HEADER_LOGO', "../../../img/header/centreon.gif");    
  
}

//function pdfGen($group_name, $start_date, $end_date,$stats,$l,$logo_header, $chart_img){
function pdfGen($group_name, $mode = NULL, $start_date, $end_date,$stats,$l,$logo_header,$title){
	
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
		
		$pdf->SetHeaderData('../../../img/headers/' . $logo_header, PDF_HEADER_LOGO_WIDTH, $header,$string);

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

		//Data loading
		$data = $pdf->LoadData($stats);

		// print colored table
		//$pdf->ColoredTable($header, $data,$chart_img);
		if ($mode == "hgs") {
			$pdf->ColoredTable($header, $data);
		} else if ($mode == "sgs") {
			$pdf->ServicesColoredTable($header, $data);	
		}

		// ---------------------------------------------------------

		//génération d'un nom de pdf
		$endDay = date("d", $time);
		$endYear = date("Y", $time);
		$endMonth = date("m", $time);
		$pdfFileName =
		"/tmp/".$endYear."-".$endMonth."-".$endDay."_".$group_name.".pdf";
		//Close and output PDF document
		$pdf->Output($pdfFileName, 'F'); 

		return $pdfFileName;

}


?>
