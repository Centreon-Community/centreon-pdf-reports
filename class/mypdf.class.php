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

//============================================================+
// classe de la génération de pdf
//============================================================+
global $centreon_path;

require_once  $centreon_path . "/www/modules/pdfreports/lib/tcpdf/config/lang/eng.php";
require_once  $centreon_path . "/www/modules/pdfreports/lib/tcpdf/tcpdf.php";


// extend TCPF with custom functions
class MYPDF extends TCPDF {


  //ajout de fonctions tcpdf pour la personnalisation du footer à partir de fct de personnalisation header

		/**
	 	 * Set footer data.
		 * @param string $ln footer image logo
		 * @param string $lw foote image logo width in mm
		 * @param string $ht string to print as title on document header
		 * @param string $hs string to print on document header
		 * @access public
		 */
    	public function setFooterData($ln='', $lw=0, $ht='', $hs='') {
			$this->footer_logo = $ln;
			$this->footer_logo_width = $lw;
			$this->footer_title = $ht;
			$this->footer_string = $hs;
		}

    
    // Load table data from file
    public function LoadData($array) {
        $data = $array;
        return $data;
    }


  public  function ColoredTable($header,$data) {

	// Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header

        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;

/*  2.3
 [14] => Array
        (
            [UP_A] => 2
            [UP_T] => 172800
            [DOWN_A] => 0
            [DOWN_T] => 0
            [UNREACHABLE_A] => 0
            [UNREACHABLE_T] => 0
            [UNDETERMINED_T] => 432000
            [MAINTENANCE_T] => 0
            [TOTAL_TIME] => 604800
            [UP_TP] => 28.57
            [DOWN_TP] => 0
            [UNREACHABLE_TP] => 0
            [UNDETERMINED_TP] => 71.43
            [MAINTENANCE_TP] => 0
            [MEAN_TIME] => 172800
            [UP_MP] => 100
            [DOWN_MP] => 0
            [UNREACHABLE_MP] => 0
            [MEAN_TIME_F] => 2d
            [TOTAL_TIME_F] => 1w
            [UP_TF] => 2d
            [DOWN_TF] =>
            [UNREACHABLE_TF] =>
            [UNDETERMINED_TF] => 5d
            [MAINTENANCE_TF] =>
            [TOTAL_ALERTS] => 2
            [NAME] => ITCHY
            [ID] => 14
        )
*/



	// Services group state - récuperation des variables sans crochet ni guillemet pour les passer dans le tableau
	$UP_TP_AV = $data["average"]["UP_TP"];
	$UP_MP_AV = $data["average"]["UP_MP"];
	$UP_A_AV = $data["average"]["UP_A"];

	$DOWN_TP_AV = $data["average"]["DOWN_TP"];
	$DOWN_MP_AV = $data["average"]["DOWN_MP"];
	$DOWN_A_AV = $data["average"]["DOWN_A"];

	$UNREACHABLE_TP_AV = $data["average"]["UNREACHABLE_TP"];
	$UNREACHABLE_MP_AV = $data["average"]["UNREACHABLE_MP"];
	$UNREACHABLE_A_AV = $data["average"]["UNREACHABLE_A"];


	$UNDETERMINED_TP_AV = $data["average"]["UNDETERMINED_TP"];
	if (isset($data["average"]["MAINTENANCE_TP"]) ) {
	  $MAINTENANCE_TP_AV =  (isset($data["average"]["MAINTENANCE_TP"]) && $data["average"]["MAINTENANCE_TP"] != NULL ? $data["average"]["MAINTENANCE_TP"] : 0 );
	  $MAINTENANCE_MP_AV = (isset($data["average"]["MAINTENANCE_MP"]) && $data["average"]["MAINTENANCE_MP"] != NULL ? $data["average"]["MAINTENANCE_MP"] : 0 );
	  $MAINTENANCE_A_AV = (isset($data["average"]["MAINTENANCE_A"]) && $data["average"]["MAINTENANCE_A"] != NULL ? $data["average"]["MAINTENANCE_A"] : 0 );
	  
	  $MAINTENANCE_TR = <<<EOD
<tr style="background-color:#EDF4FF;">
  <th style="background-color:#CC99FF;">Schedule Downtime</th>
  <td>$MAINTENANCE_TP_AV %</td> 
  <td>$MAINTENANCE_MP_AV %</td>
  <td>$MAINTENANCE_A_AV</td>
</tr>
EOD;
 
	} else {
	  
	  $MAINTENANCE_TR = "";
	}

	//calcul du total des alertes
	$TOTAL_A_AV = $UP_A_AV + $DOWN_A_AV + $UNREACHABLE_A_AV;

//creation du tableau pour tcpdf, format html
	
	$tbl = <<<EOD
	
<img src="/usr/local/centreon/www/modules/pdfreports/example.draw3DPie.transparent.png">
EOD;
	
	
	
	$tbl1 = <<<EOD

		  <table border="1" align="center">
			  <tr>
			  <td colspan="4" style="background-color:#D7D6DD;" >Hosts group state</td>
			  </tr>
		  <tr style="background-color:#D5DFEB;">
		    <th>State</th>
		    <th>Total Time</th>
		    <th>Mean Time</th>
		    <th>Alerts</th>
		  </tr>
		  
		  <tr style="background-color:#F7FAFF;">
		    <th style="background-color:#19EE11;">UP</th>
		    <td>$UP_TP_AV %</td> 
		    <td>$UP_MP_AV %</td>
		    <td>$UP_A_AV</td>
		  </tr>
		  
		  <tr style="background-color:#EDF4FF;">
		    <th style="background-color:#F91E05;">DOWN</th>
		    <td>$DOWN_TP_AV %</td>
		    <td>$DOWN_MP_AV %</td>
		    <td>$DOWN_A_AV</td>
		  </tr>
		  
		  
		  <tr style="background-color:#F7FAFF;">
		    <th style="background-color:#82CFD8;">UNREACHABLE</th>
		    <td>$UNREACHABLE_TP_AV %</td> 
		    <td>$UNREACHABLE_MP_AV %</td>
		    <td>$UNREACHABLE_A_AV</td>
		  </tr>
		  
		  $MAINTENANCE_TR
		  
		  <tr style="background-color:#EDF4FF;">
		    <th style="background-color:#F0F0F0;">UNDETERMINED</th>
		    <td>$UNDETERMINED_TP_AV %</td> 
		    <td></td>
		    <td></td>
		  
		  </tr>
		  
		  <tr style="background-color:#CED3ED;">
		    <th>Total</th>
		    <td></td>
		    <td></td>
		    <td>$TOTAL_A_AV</td>
		  </tr>
		  
		  </table>
		  
EOD;




// State Breakdowns For Host  

//init du deuxième tableau

if (isset($MAINTENANCE_TR) && $MAINTENANCE_TR != "") {
  $MAINTENANCE_HEADER = '<td width="110" style="background-color:#CC99FF;">Schedule Downtime</td>';
  $MAINTENANCE_HEADER_LABEL = "<td width='110'>%</td>";
  $HEADER_WIDTH = "700";
} else {
   $MAINTENANCE_HEADER = "";
   $MAINTENANCE_HEADER_LABEL = "";
   $HEADER_WIDTH = "590";
}



$tbl2 = <<<EOD

<table border="1" align="center">
	<tr style="background-color:#D7D6DC;">
	  <td width="$HEADER_WIDTH">State Breakdowns For Hosts</td>
	</tr>
	<tr style="background-color:#D5DFEB;">
	    <td colspan="2" width="150"></td>
	    <td width="110" style="background-color:#19EE11;">Up</td>
	    <td width="110" style="background-color:#F91E05;">Down</td>
	    <td width="110" style="background-color:#82CFD8;">Unreachable</td>
	    $MAINTENANCE_HEADER 
	    <td width="110" style="background-color:#F0F0F0;">Undetermined</td>
	</tr>

	<tr style="background-color:#D5DFEB;">
	    <td width="150">Host</td>
	    <td width="60">%</td>
	    <td width="50">Alert</td>
	    <td width="60">%</td>
	    <td width="50">Alert</td>
	    <td width="60">%</td>
	    <td width="50">Alert</td>
	    $MAINTENANCE_HEADER_LABEL
	    <td width="110">%</td>
	</tr>
		    

EOD;


//parsing des hosts du hostgroup et ajout dans tableau
$i =0;
foreach ($data	 as $key => $tab) {
  if ($key != "average") {

//bug centreon - hostname et service inverses   
$NAME = $tab["NAME"];

//print_r($tab);

$UP_TP = $tab["UP_TP"];
$UP_A = $tab["UP_A"];
$DOWN_TP = $tab["DOWN_TP"];
$DOWN_A = $tab["DOWN_A"];
$UNREACHABLE_TP = $tab["UNREACHABLE_TP"];
$UNREACHABLE_A = $tab["UNREACHABLE_A"];
if (isset ($tab["MAINTENANCE_TP"])) {
  $MAINTENANCE_TP =  "<td width='110'>".$tab["MAINTENANCE_TP"]."</td>";
  
} else {
  $MAINTENANCE_TP = "";
}
$UNDETERMINED_TP = $tab["UNDETERMINED_TP"];

$BACKGROUND_COLOR = ( $i % 2 ? "EDF4FF": "F7FAFF"); 

$tbl2 .= <<<EOD

<tr style="background-color:#$BACKGROUND_COLOR;">
<td width="150">$NAME</td>
<td width="60">$UP_TP</td>
<td width="50">$UP_A</td>
<td width="60">$DOWN_TP</td>
<td width="50">$DOWN_A</td>
<td width="60">$UNREACHABLE_TP</td>
<td width="50">$UNREACHABLE_A</td>
$MAINTENANCE_TP
<td width="110">$UNDETERMINED_TP</td>
</tr>



EOD;
$i++;
  }
}

//fermeture du tableau
$tbl2 .= <<<EOD
</table>

EOD;

//$this->writeHTML($tbl, true, false, false, false, ''); 
$this->writeHTML($tbl1, true, false, false, false, ''); 
$this->writeHTML($tbl2, true, false, false, false, '');

}



/* pour les services groupes */
    
    // Colored table
   public function ServicesColoredTable($header,$data) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        // Header

        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = 0;

	//print_r($data);

	// Services group state - récuperation des variables sans crochet ni guillemet pour les passer dans le tableau
	$OK_TP_AV = $data["average"]["OK_TP"];
	$OK_MP_AV = $data["average"]["OK_MP"];
	$OK_A_AV = $data["average"]["OK_A"];

	$WARNING_TP_AV = $data["average"]["WARNING_TP"];
	$WARNING_MP_AV = $data["average"]["WARNING_MP"];
	$WARNING_A_AV = $data["average"]["WARNING_A"];

	$CRITICAL_TP_AV = $data["average"]["CRITICAL_TP"];
	$CRITICAL_MP_AV = $data["average"]["CRITICAL_MP"];
	$CRITICAL_A_AV = $data["average"]["CRITICAL_A"];

	$UNKNOWN_TP_AV = $data["average"]["UNKNOWN_TP"];
	$UNKNOWN_MP_AV = $data["average"]["UNKNOWN_MP"];
	$UNKNOWN_A_AV = $data["average"]["UNKNOWN_A"];

	$UNDETERMINED_TP_AV = $data["average"]["UNDETERMINED_TP"];

	//calcul du total des alertes
	$TOTAL_A_AV = $OK_A_AV + $WARNING_A_AV + $UNKNOWN_A_AV;

//creation du tableau pour tcpdf, format html
	
	$tbl1 = <<<EOD
<table border="1" align="center">
	<tr>
	<td colspan="4"  style="background-color:#D7D7DD;" >Services group state</td>
	</tr>
<tr>
  <th style="background-color:#D5DFEB;">State</th>
  <th style="background-color:#D5DFEB;">Total Time</th>
  <th style="background-color:#D5DFEB;">Mean Time</th>
  <th style="background-color:#D5DFEB;">Alerts</th>
</tr>

<tr>
  <th style="background-color:#13EB3A;">Ok</th>
  <td style="background-color:#F7FAFF;">$OK_TP_AV %</td> 
  <td style="background-color:#F7FAFF;">$OK_MP_AV %</td>
  <td style="background-color:#F7FAFF;">$OK_A_AV %</td>
</tr>

<tr>
  <th style="background-color:#F8C706;">Warning</th>
  <td style="background-color:#EDF4FF;">$WARNING_TP_AV %</td> 
  <td style="background-color:#EDF4FF;">$WARNING_MP_AV %</td>
  <td style="background-color:#EDF4FF;">$WARNING_A_AV</td>
</tr>


<tr>
  <th style="background-color:#F91E05;">Critical</th>
  <td style="background-color:#F7FAFF;">$CRITICAL_TP_AV %</td> 
  <td style="background-color:#F7FAFF;">$CRITICAL_MP_AV %</td>
  <td style="background-color:#F7FAFF;">$CRITICAL_A_AV</td>
</tr>


<tr>
  <th style="background-color:#DCDADA;">Unknown</th>
  <td style="background-color:#EDF4FF;">$UNKNOWN_TP_AV %</td> 
  <td style="background-color:#EDF4FF;">$UNKNOWN_MP_AV %</td>
  <td style="background-color:#EDF4FF;">$UNKNOWN_A_AV</td>
</tr>



<tr>
  <th style="background-color:#F0F0F0;">Undertermined</th>
  <td style="background-color:#F7FAFF;">$UNDETERMINED_TP_AV %</td> 
  <td style="background-color:#F7FAFF;"></td>
  <td style="background-color:#F7FAFF;"></td>

</tr>

<tr>

  <th style="background-color:#CED3ED;">Total</th>
  <td style="background-color:#CED3ED;"></td>
  <td style="background-color:#CED3ED;"></td>
  <td style="background-color:#CED3ED;">$TOTAL_A_AV</td>
</tr>

</table>
EOD;




// State Breakdowns For Host Services 

//init du deuxième tableau

$tbl2 = <<<EOD

<table border="1" align="center">
	<tr>
	<td width="750" style="background-color:#D7D6DD;" >State Breakdowns For Host Services</td>
	</tr>
	<tr style="background-color:#D5DFEB;">
		<td colspan="2" width="200"  ></td>

		<td width="110" >OK</td>
		<td width="110" >Warning</td>
		<td width="110" >Critical</td>
		<td width="110" >Unknown</td>
		<td width="110" >Undetermined</td>
	</tr>

	<tr style="background-color:#D5DFEB;">
		<td width="90" >Host Name</td>
		<td width="110">Service</td>
		<td width="60">%</td>
		<td width="50">Alert</td>
		<td width="60">%</td>

		<td width="50">Alert</td>
		<td width="60">%</td>
		<td width="50">Alert</td>
		<td width="60">%</td>
		<td width="50">Alert</td>
		<td width="110">%</td>
	</tr>
		    

EOD;


//parsing des services du service group et ajout dans tableau
$i =0;
foreach ($data	 as $key => $tab) {
  if ($key != "average") {

//bug centreon - hostname et service inverses   
$HOST_NAME = $tab["HOST_NAME"];
$SERVICE_DESC = $tab["SERVICE_DESC"];

$OK_TP = $tab["OK_TP"];
$OK_A = $tab["OK_A"];
$WARNING_TP = $tab["WARNING_TP"];
$WARNING_A = $tab["WARNING_A"];
$CRITICAL_TP = $tab["CRITICAL_TP"];
$CRITICAL_A = $tab["CRITICAL_A"];
$UNKNOWN_TP = $tab["UNKNOWN_TP"];
$UNKNOWN_A = $tab["UNKNOWN_A"];
$UNDETERMINED_TP = $tab["UNDETERMINED_TP"];


$BACKGROUND_COLOR = ( $i % 2 ? "EDF4FF": "F7FAFF"); 

$tbl2 .= <<<EOD

<tr style="background-color:#$BACKGROUND_COLOR;" >
<td width="90">$HOST_NAME</td>
<td width="110">$SERVICE_DESC</td>
<td width="60" style="background-color:#13EB3A;" >$OK_TP</td>
<td width="50" style="background-color:#13EB3A;">$OK_A</td>
<td width="60" style="background-color:#F8C706;">$WARNING_TP</td>
<td width="50" style="background-color:#F8C706;">$WARNING_A</td>
<td width="60" style="background-color:#F91D05;">$CRITICAL_TP</td>
<td width="50" style="background-color:#F91D05;">$CRITICAL_A</td>
<td width="60" style="background-color:#DCDADA;">$UNKNOWN_TP</td>
<td width="50" style="background-color:#DCDADA;">$UNKNOWN_A</td>
<td width="110" style="background-color:#F0F0F0;">$UNDETERMINED_TP</td>
</tr>



EOD;
$i++;
  }
}

//fermeture du tableau
$tbl2 .= <<<EOD
</table>

EOD;


//écriture des tableaux
$this->writeHTML($tbl1, true, false, false, false, ''); 
$this->writeHTML($tbl2, true, false, false, false, ''); 




    } 






}


