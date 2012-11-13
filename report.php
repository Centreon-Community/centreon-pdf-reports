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

/*
ini_set('display_errors',1);
error_reporting(E_ALL);
*/

	if (!isset($oreon))
		exit ();
	
	isset($_GET["report_id"]) ? $rG = $_GET["report_id"] : $rG = NULL;
	isset($_POST["report_id"]) ? $rP = $_POST["report_id"] : $rP = NULL;
	$rG ? $report_id = $rG : $report_id = $rP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;
	
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the configuration dir
	 */
	global $path;
	$path = "./modules/pdfreports/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "")
			$o = $_POST["o1"];
		if ($_POST["o2"] != "")
			$o = $_POST["o2"];
	}	
	
	switch ($o)	{
		case "a" 	: require_once($path."formReport.php"); break; #Add a host
		case "w" 	: require_once($path."formReport.php"); break; #Watch a host
		case "c" 	: require_once($path."formReport.php"); break; #Modify a host
		case "mc" 	: require_once($path."formReport.php"); break; # Massive Change
		case "s" 	: enableReportInDB($report_id); require_once($path."listReport.php"); break; #Activate a host
		case "ms" 	: enableReportInDB(NULL, isset($select) ? $select : array()); require_once($path."listReport.php"); break;
		case "u" 	: disableReportInDB($report_id); require_once($path."listReport.php"); break; #Desactivate a host
		case "mu" 	: disableReportInDB(NULL, isset($select) ? $select : array()); require_once($path."listReport.php"); break;
		case "m" 	: multipleReportInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listReport.php"); break; #Duplicate n hosts
		case "d" 	: deleteReportInDB(isset($select) ? $select : array()); require_once($path."listReport.php"); break; #Delete n hosts
		default 	: require_once($path."listReport.php"); break;
	}	
	
	
?>
