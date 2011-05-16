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
/*
ini_set('display_errors',1);
error_reporting(E_ALL);
*/
	require_once($centreon_path . "www/class/centreonDB.class.php");
	require_once($centreon_path . "www/include/common/common-Func.php");

	## Get centreon version
	/*
	 * Connector to centreon DB
	 */
	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");
	$pearDBO = new CentreonDB("centstorage");

	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();  	
	$centreon_version = substr(str_replace(".", "" ,$version["value"]), 0, 3);
	
	if ( $centreon_version >= 220) { 
		require_once($centreon_path . "www/class/centreonDuration.class.php");   
	} else {
		require_once($centreon_path . "www/class/other.class.php");   
	}


function getCentreonVersion(){	

	## Get centreon version
	global $pearDB;

	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();  	
	$centreon_version = substr(str_replace(".", "" ,$version["value"]), 0, 3);

	return $centreon_version;
}
	
function getAdminUserAlias(){	

	## Get centreon version
	global $pearDB;

	$DBRESULT =& $pearDB->query("SELECT `contact_alias` FROM `contact` WHERE `contact_admin` = '1' AND `contact_activate` = '1' LIMIT 1");
	$contact =& $DBRESULT->fetchRow();  	
	$admin_alias = $contact["contact_alias"];

	return $admin_alias;
}

	
function getGeneralOptInfo($option_name)	{

	global $pearDB;

    $DBRESULT =& $pearDB->query("SELECT value FROM options WHERE options.key like '".$option_name."'");
    if (PEAR::isError($DBRESULT))
        print "DB Error : SELECT value FROM options WHERE options.key like '".$option_name."' : ".$DBRESULT->getMessage()."<br />";
        $gopt = $DBRESULT->fetchRow();
	
	return  $gopt['value'] ;		
}		
	
	//reprise de la fonction getPeriodToReport de www/include/reporting/dashboard/common-Func.php pour retourner un timestamp sans $_POST
function getPeriodToReportFork($arg) {	
		$interval = getDateSelect_predefined($arg);
		$start_date = $interval[0];
		$end_date = $interval[1];
		return(array($start_date,$end_date));
}

//recuperation de tous les groupes de services
function getAllServiceGroups(){

		global $pearDB;
		$req = "SELECT * FROM `servicegroup`";
		$DBRESULT =& $pearDB->query($req);
		unset($req);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($row =& $DBRESULT->fetchRow())			    
			$sdata[] = $row;  
		$DBRESULT->free();
		return $sdata; 

}


//recuperation de tous les groupes d'hosts
function getAllHostGroup(){

		global $pearDB;
		$req = "SELECT * FROM `hostgroup`";
		$DBRESULT =& $pearDB->query($req);
		unset($req);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		while ($row =& $DBRESULT->fetchRow())			    
			$hdata[] = $row;  
		$DBRESULT->free();
		return $hdata; 

}


# Report Logos
function return_logos_list($mode = 0, $rep = NULL, $full = true, $origin_path = NULL) {
	global $oreon;
	$elems = array();
	$images = array();
	if ($full)
		$images = array(NULL=>NULL);

	$is_not_an_image = array(".","..","README","readme","LICENCE","licence");
	$is_a_valid_image = array(
		0 => array('png'),
		1 => array('gif', 'png', 'jpg'),
		2 => array('gif', 'png', 'jpg', 'gd2')
	);

	if ( ! $rep )
		if ($oreon->optGen["nagios_path_img"] && is_dir($oreon->optGen["nagios_path_img"]))
			$rep=$oreon->optGen["nagios_path_img"];
		else
			return ($images);
	$rep .= "/"; // XXX not clean

	if ( ! $origin_path)
		$origin_path = $rep;
	$path_len = strlen($origin_path);

	if (! ($dh = @opendir($rep)) ) {
		// error_log("WARNING: can't open directory '".$rep."'",0);
		return ($images);
	}

	while (false !== ($filename = readdir($dh))) {
		if ( $filename == "." || $filename == "..")
			continue;

		# WARNING: recursive call
		if (is_dir($rep.$filename)) {
			$tmp_images = return_image_list($mode, $rep.$filename, $full, $origin_path);
			$images = array_merge($images,$tmp_images);
			continue;
		}

		if (in_array($filename, $is_not_an_image))
			continue;

		$pinfo = pathinfo($filename);
		if (isset($pinfo["extension"]) && isset($is_a_valid_image[$mode][$pinfo["extension"]]))
			continue;

		$key = substr($rep.$filename, $path_len);
		$images[$key] = $key;
	}

	closedir($dh);
	ksort($images);
	return ($images);
}	

function myDecodeReport($arg)	{
	$arg = html_entity_decode($arg, ENT_QUOTES);
	return($arg);
}


function getActiveReports($period = NULL) {
	global $pearDB; 
	
	$period_filter = "";
	if (isset($period))
		$period_filter = "AND period = '".  $period ."'";
	
	$reports = array();
	$DBRESULT =& $pearDB->query("SELECT report_id, name FROM pdfreports_reports WHERE activate = '1' $period_filter");
	while ($notifCg =& $DBRESULT->fetchRow())
		$reports[$notifCg["report_id"]] = $notifCg["name"];
	$DBRESULT->free();	
	
	return $reports;
	
	
}

function getReportInfo($report_id = NULL) {
	if (!$report_id ) return;	
	global $pearDB; 
	
	//print "SELECT * FROM reporteon_reports WHERE report_id = '".$report_id."' LIMIT 1\n";
	$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_reports WHERE report_id = '".$report_id."' LIMIT 1");
	# Set base value
	$report_info = array_map("myDecodeReport", $DBRESULT->fetchRow());
	$DBRESULT->free();	
	//print_r($report_info);
	return $report_info;
	
	
}


function getReportContactEmail($report_id = NULL) {
	if (!$report_id ) return;	
	global $pearDB; 
	
	//print "SELECT * FROM reporteon_reports_contactgroup_relation WHERE reports_rp_id = '".$report_id."'";
	$DBRESULT =& $pearDB->query("SELECT contact_contact_id FROM pdfreports_reports_contactgroup_relation rrcr, contactgroup_contact_relation ccr WHERE reports_rp_id = '".$report_id."' AND rrcr.contactgroup_cg_id = ccr.contactgroup_cg_id");
	for ($i = 0; $Cg =& $DBRESULT->fetchRow(); $i++)
		$contacts[$i] = $Cg["contact_contact_id"];
	$DBRESULT->free();


	$DBRESULT =& $pearDB->query("SELECT contact_c_id FROM pdfreports_reports_contact_relation  WHERE reports_rp_id = '".$report_id."'");
	for ($j = $i; $C =& $DBRESULT->fetchRow(); $j++)
		$contacts[$j] = $C["contact_c_id"];	
	

	$contacts_email = array();
	foreach ( $contacts as $key => $contact_id ) {
		$contacts_email[$key] = getContactEmail($contact_id);
		}
	
	
	//print_r($contacts_email);
	return $contacts_email;
	
	
}


function getContactEmail($contact_id = NULL) {
	if (!$contact_id ) return;	
	global $pearDB; 
	
	$DBRESULT =& $pearDB->query("SELECT contact_email FROM contact WHERE contact_id = '".$contact_id."' LIMIT 1");
	# Set base value
	$email =  $DBRESULT->fetchRow();
	$DBRESULT->free();	
	return $email['contact_email'];
	
	
}




function getHostReport($report_id) {
	if (!$report_id ) return;	
	global $pearDB;
	$hosts = array();
	/*
	 * Grab hostgroup || host
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_host_report_relation hrr WHERE hrr.reports_rp_id = '".$report_id."'");
	while ($parent =& $DBRESULT->fetchRow())	{
		if ($parent["host_host_id"])
			$hosts["report_hs"][$parent["host_host_id"]] = $parent["host_host_id"];
		else if ($parent["hostgroup_hg_id"])
			$hosts["report_hgs"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
	}
	
	return $hosts;
	
}

function getServiceGroupReport($report_id) {
	if (!$report_id ) return;	
	global $pearDB;
	$services = array();
	/*
	 * Grab servicegroup
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_reports_servicegroup_relation WHERE reports_rp_id = '".$report_id."'");
	for ($i = 0; $notifSg =& $DBRESULT->fetchRow(); $i++)
		$services["report_sg"][$i] = $notifSg["servicegroup_sg_id"];
	$DBRESULT->free();
	
	return $services;
	
}




	function testReportExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('report_id');
		$DBRESULT =& $pearDB->query("SELECT name, report_id FROM pdfreports_reports WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
		$report =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $report["report_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $report["report_id"] != $id)	
			return false;
		else
			return true;
	}


	function multipleReportInDB ($reports = array(), $nbrDup = array(), $host = NULL, $descKey = 1, $hostgroup = NULL, $hPars = array(), $hgPars = array())	{
		global $pearDB, $oreon;

		/*
		 * $descKey param is a flag.
		 * 	If 1, we know we have to rename description because it's a traditionnal duplication.
		 * 	If 0, we don't have to, beacause we duplicate services for an Host duplication
		 *	Foreach Service
		 */
		$maxId["MAX(report_id)"] = NULL;
		foreach ($reports as $key=>$value)	{
			/*
			 *  Get all information about it
			 */
			$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_reports WHERE report_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["report_id"] = '';
			/*
			 * Loop on the number of Service we want to duplicate
			 */
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = NULL;
				/*
				 * Create a sentence which contains all the value
				 */
				foreach ($row as $key2=>$value2)	{
					if ($key2 == "name" && $descKey) {
						$name = $value2 = $value2."_".$i;
					}
					else if ($key2 == "name")
						$report_description = NULL;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "report_id")
						$fields[$key2] = $value2;
					/*if (isset($service_description))
						$fields["report_description"] = $report_description;*/
				}
				if (!count($hPars))
					$hPars = getMyServiceHosts($key); // todo
				if (!count($hgPars))
					$hgPars = getMyServiceHostGroups($key);  // todo
				if ( testReportExistence($name)) 	{
					$hPars = array();
					$hgPars = array();
					(isset($val) && $val != "NULL" && $val) ? $rq = "INSERT INTO pdfreports_reports VALUES (".$val.")" : $rq = NULL;
					if (isset($rq)) {
						$DBRESULT =& $pearDB->query($rq);
						$DBRESULT =& $pearDB->query("SELECT MAX(report_id) FROM pdfreports_reports");
						$maxId =& $DBRESULT->fetchRow();
						if (isset($maxId["MAX(report_id)"]))	{
							/*
							 * Host duplication case -> Duplicate the Service for the Host we create
							 */
							if ($host)
								$pearDB->query("INSERT INTO pdfreports_host_report_relation VALUES ('', NULL, '".$host."',  '".$maxId["MAX(report_id)"]."')");
							else if ($hostgroup)
								$pearDB->query("INSERT INTO pdfreports_host_report_relation VALUES ('', '".$hostgroup."', NULL, '".$maxId["MAX(report_id)"]."')");
							else	{
							# Service duplication case -> Duplicate the Service for each relation the base Service have
								$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id, hostgroup_hg_id FROM pdfreports_host_report_relation WHERE reports_rp_id = '".$key."'");
								//$fields["service_hPars"] = "";
								//$fields["service_hgPars"] = "";
								while($report =& $DBRESULT->fetchRow())	{
									if ($report["host_host_id"]) {
										//print "INSERT INTO reporteon_host_report_relation VALUES ('', NULL, '".$report["host_host_id"]."',  '".$maxId["MAX(id)"]."')<br/>";
										$DBRESULT2 =& $pearDB->query("INSERT INTO pdfreports_host_report_relation VALUES ('', NULL, '".$report["host_host_id"]."',  '".$maxId["MAX(id)"]."')");
										//$fields["service_hPars"] .= $service["host_host_id"] . ",";
									}
									else if ($report["hostgroup_hg_id"]) {
										//print "INSERT INTO reporteon_host_report_relation VALUES ('', '".$report["hostgroup_hg_id"]."', NULL, '".$maxId["MAX(id)"]."')<br/>";
										$DBRESULT2 =& $pearDB->query("INSERT INTO pdfreports_host_report_relation VALUES ('', '".$report["hostgroup_hg_id"]."', NULL, '".$maxId["MAX(id)"]."')");
										//$fields["service_hgPars"] .= $service["hostgroup_hg_id"] . ",";
									}
								}
								//$fields["service_hPars"] = trim($fields["service_hPars"], ",");
								//$fields["service_hgPars"] = trim($fields["service_hgPars"], ",");
							}

							/*
							 * ServiceGroup duplication
							 */
							$DBRESULT =& $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM pdfreports_reports_servicegroup_relation WHERE reports_rp_id = '".$key."'");
							//$fields["service_cgs"] = "";
							while($Sg =& $DBRESULT->fetchRow()){
								$DBRESULT2 =& $pearDB->query("INSERT INTO pdfreports_reports_servicegroup_relation VALUES ('', '".$maxId["MAX(report_id)"]."', '".$Sg["servicegroup_sg_id"]."')");
								//$fields["service_cgs"] .= $Cg["contactgroup_cg_id"] . ",";
							}
							//$fields["service_cgs"] = trim($fields["service_cgs"], ",");


							/*
							 * Contact duplication
							 */
							$DBRESULT =& $pearDB->query("SELECT DISTINCT contact_c_id FROM pdfreports_reports_contact_relation WHERE reports_rp_id = '".$key."'");
							//$fields["service_cs"] = "";
							while ($C =& $DBRESULT->fetchRow()){
								$DBRESULT2 =& $pearDB->query("INSERT INTO pdfreports_reports_contact_relation VALUES ('', '".$maxId["MAX(report_id)"]."', '".$C["contact_c_id"]."')");
								//$fields["service_cs"] .= $C["contact_c_id"] . ",";
							}
							//$fields["service_cs"] = trim($fields["service_cs"], ",");

							/*
							 * ContactGroup duplication
							 */
							$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM pdfreports_reports_contactgroup_relation WHERE reports_rp_id = '".$key."'");
							//$fields["service_cgs"] = "";
							while($Cg =& $DBRESULT->fetchRow()){
								$DBRESULT2 =& $pearDB->query("INSERT INTO pdfreports_reports_contactgroup_relation VALUES ('', '".$maxId["MAX(report_id)"]."', '".$Cg["contactgroup_cg_id"]."')");
								//$fields["service_cgs"] .= $Cg["contactgroup_cg_id"] . ",";
							}
							//$fields["service_cgs"] = trim($fields["service_cgs"], ",");



							/*
							 *  get svc desc
							 */
							$query = "SELECT report_description FROM pdfreports_reports WHERE report_id = '".$maxId["MAX(report_id)"]."' LIMIT 1";
							$DBRES =& $pearDB->query($query);
							if ($DBRES->numRows()) {
								$row2 =& $DBRES->fetchRow();
								$description = $row2['report_description'];
								$description = str_replace("#S#", "/", $description);
								$description = str_replace("#BS#", "\\", $description);
								//$oreon->CentreonLogAction->insertLog("service", $maxId["MAX(service_id)"], getHostServiceCombo($maxId["MAX(service_id)"], $description), "a", $fields);
							}
						}
					}
				}
			}
		}
		return ($maxId["MAX(report_id)"]);
	}




	function enableReportInDB ($report_id = null, $report_arr = array())	{
		if (!$report_id && !count($report_arr)) return;
		global $pearDB, $oreon;
		if ($report_id)
			$report_arr = array($report_id=>"1");
		foreach($report_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE pdfreports_reports SET activate = '1' WHERE report_id = '".$key."'");
			$DBRESULT2 =& $pearDB->query("SELECT report_description FROM `pdfreports_reports` WHERE report_id = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			//$oreon->CentreonLogAction->insertLog("reporteon_reports", $key, getHostServiceCombo($key, $row['service_description']), "enable");
		}
	}

	function disableReportInDB ($report_id = null, $report_arr = array())	{
		if (!$report_id && !count($report_arr)) return;
		global $pearDB, $oreon;
		if ($report_id)
			$report_arr = array($report_id=>"1");
		foreach($report_arr as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE pdfreports_reports SET activate = '0' WHERE report_id = '".$key."'");

			$DBRESULT2 =& $pearDB->query("SELECT report_description FROM `pdfreports_reports` WHERE report_id = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
		//	$oreon->CentreonLogAction->insertLog("service", $key, getHostServiceCombo($key, $row['service_description']), "disable");
		}
	}
	function deleteReportInDB ($reports = array())	{
		global $pearDB, $oreon;

		foreach ($reports as $key => $value)	{

		/*	$DBRESULT =& $pearDB->query("SELECT id FROM service WHERE service_template_model_stm_id = '".$key."'");
			while ($row =& $DBRESULT->fetchRow())	{
				$DBRESULT2 =& $pearDB->query("UPDATE service SET service_template_model_stm_id = NULL WHERE service_id = '".$row["service_id"]."'");
			}*/

			$DBRESULT3 =& $pearDB->query("SELECT report_description FROM `pdfreports_reports` WHERE `report_id` = '".$key."' LIMIT 1");
			$svcname = $DBRESULT3->fetchRow();
			//$oreon->CentreonLogAction->insertLog("service", $key, getHostServiceCombo($key, $svcname['service_description']), "d");
			$DBRESULT =& $pearDB->query("DELETE FROM pdfreports_reports WHERE report_id = '".$key."'");

			if ($oreon->user->get_version() >= 3) {
				//$DBRESULT =& $pearDB->query("DELETE FROM on_demand_macro_service WHERE svc_svc_id = '".$key."'");
				$DBRESULT =& $pearDB->query("DELETE FROM pdfreports_host_report_relation WHERE reports_rp_id = '".$key."'");
				$DBRESULT =& $pearDB->query("DELETE FROM pdfreports_reports_contact_relation WHERE reports_rp_id = '".$key."'");
				$DBRESULT =& $pearDB->query("DELETE FROM pdfreports_reports_contactgroup_relation WHERE reports_rp_id = '".$key."'");								
			}
		}
	}

	function updateReportInDB ($report_id = NULL, $from_MC = false)	{
		if (!$report_id) return;
		global $form;
		$ret = $form->getSubmitValues();
		if ($from_MC)
			updateReport_MC($report_id);
		else
			updateReport($report_id, $from_MC);
		# Function for updating cg
		# 1 - MC with deletion of existing cg
		# 2 - MC with addition of new cg
		# 3 - Normal update
		if (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && $ret["mc_mod_cgs"]["mc_mod_cgs"]) {
			updateReportContactGroup($report_id);
			updateReportContact($report_id);
		}
		else if (isset($ret["mc_mod_cgs"]["mc_mod_cgs"]) && !$ret["mc_mod_cgs"]["mc_mod_cgs"]) {
			updateReportContactGroup_MC($report_id);
			updateReportContact_MC($report_id);
		}
		else {
			updateReportContactGroup($report_id);
			updateReportContact($report_id);
		}

		# Function for updating host/hg
		# 1 - MC with deletion of existing host/hg
		# 2 - MC with addition of new host/hg
		# 3 - Normal update
		if (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && $ret["mc_mod_Pars"]["mc_mod_Pars"])
			updateReportHost($report_id);
		else if (isset($ret["mc_mod_Pars"]["mc_mod_Pars"]) && !$ret["mc_mod_Pars"]["mc_mod_Pars"])
			updateReportHost_MC($report_id);
		else
			updateReportHost($report_id);
			
			
		# Function for updating sg
		# 1 - MC with deletion of existing host/hg parent
		# 2 - MC with addition of new host/hg parent
		# 3 - Normal update
		if (isset($ret["mc_mod_sg"]["mc_mod_sg"]) && $ret["mc_mod_sg"]["mc_mod_sg"])
			updateReportServiceGroup($report_id);
		else if (isset($ret["mc_mod_sg"]["mc_mod_sg"]) && !$ret["mc_mod_sg"]["mc_mod_sg"])
			updateReportServiceGroup_MC($report_id);
		else
			updateReportServiceGroup($report_id);			


	}

	function insertReportInDB ($ret = array())	{
		global $oreon;

		$tmp_fields = array();
		$tmp_fields = insertReport($ret);
		$report_id = $tmp_fields['report_id'];
		updateReportContactGroup($report_id, $ret);
		updateReportContact($report_id, $ret);
		updateReportHost($report_id, $ret);	
		updateReportServiceGroup($report_id, $ret);
		$oreon->user->access->updateACL();
		$fields = $tmp_fields['fields'];
		//$oreon->CentreonLogAction->insertLog("service", $service_id, getHostServiceCombo($service_id, htmlentities($fields["service_description"], ENT_QUOTES)), "a", $fields);
		return ($report_id);
	}

	function insertReport($ret = array())	{
		global $form, $pearDB, $oreon;

		if (!count($ret))
			$ret = $form->getSubmitValues();
		/*if (isset($ret["command_command_id_arg"]) && $ret["command_command_id_arg"] != NULL)		{
			$ret["command_command_id_arg"] = str_replace("\n", "#BR#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\t", "#T#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace("\r", "#R#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('/', "#S#", $ret["command_command_id_arg"]);
			$ret["command_command_id_arg"] = str_replace('\\', "#BS#", $ret["command_command_id_arg"]);
		}*/
		/*if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != NULL)		{
			$ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('/', "#S#", $ret["command_command_id_arg2"]);
			$ret["command_command_id_arg2"] = str_replace('\\', "#BS#", $ret["command_command_id_arg2"]);
		}*/
		if (isset($ret["report_description"]) && $ret["report_description"] != NULL)		{
			$ret["report_descriptionv"] = str_replace('/', "#S#", $ret["report_description"]);
			$ret["report_description"] = str_replace('\\', "#BS#", $ret["report_description"]);
		}
		/*if (isset($ret["service_alias"]) && $ret["service_alias"] != NULL)		{
			$ret["service_alias"] = str_replace('/', "#S#", $ret["service_alias"]);
			$ret["service_alias"] = str_replace('\\', "#BS#", $ret["service_alias"]);
		}*/
		$rq = "INSERT INTO pdfreports_reports " .
				"(name, report_description, period, report_title, subject, mail_body, report_comment, activate) " .
				"VALUES ( ";
				isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".$ret["name"]."', ": $rq .= "NULL, ";
				isset($ret["report_description"]) && $ret["report_description"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["report_description"], ENT_QUOTES))."', ": $rq .= "NULL, ";
				isset($ret["period"]) && $ret["period"] != NULL ? $rq .= "'".$ret["period"]."', ": $rq .= "NULL, ";
				isset($ret["report_title"]) && $ret["report_title"] != NULL ? $rq .= "'".$ret["report_title"]."', ": $rq .= "NULL, ";				
				isset($ret["subject"]) && $ret["subject"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["subject"], ENT_QUOTES))."', ": $rq .= "NULL, ";
				isset($ret["mail_body"]) && $ret["mail_body"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["mail_body"], ENT_QUOTES))."', ": $rq .= "NULL, ";

				if (isset($ret["report_comment"]) && $ret["report_comment"])	{
					$ret["report_comment"] = str_replace('/', "#S#", $ret["report_comment"]);
					$ret["report_comment"] = str_replace('\\', "#BS#", $ret["report_comment"]);
				}
				isset($ret["report_comment"]) && $ret["report_comment"] != NULL ? $rq .= "'".htmlentities($ret["report_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";

				isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."'" : $rq .= "NULL";
				$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(report_id) FROM pdfreports_reports");
		$report_id = $DBRESULT->fetchRow();


		$fields["name"] = $ret["name"];
		$fields["report_description"] = htmlentities($ret["report_description"], ENT_QUOTES);
		$fields["period"] = $ret["period"];
		$fields["report_title"] = $ret["report_title"];
		$fields["subject"] = htmlentities($ret["subject"], ENT_QUOTES);	
		$fields["mail_body"] = htmlentities($ret["mail_body"], ENT_QUOTES);			
		$fields["report_comment"] = htmlentities($ret["report_comment"], ENT_QUOTES);

		return (array("report_id" => $report_id["MAX(report_id)"], "fields" => $fields));
	}
	
	function updateReport($report_id = null, $from_MC = false)	{
		global $form, $pearDB, $oreon;
		if (!$report_id)
			return;

		$ret = array();
		$ret = $form->getSubmitValues();
		if (isset($ret["report_description"]) && $ret["report_description"] != NULL)		{
			$ret["report_descriptionv"] = str_replace('/', "#S#", $ret["report_description"]);
			$ret["report_description"] = str_replace('\\', "#BS#", $ret["report_description"]);
		}

		$rq = "UPDATE pdfreports_reports SET " ;
		$rq .= "name = ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".$ret["name"]."', ": $rq .= "NULL, ";
		# If we are doing a MC, we don't have to set name and alias field
		if (!$from_MC)	{
			$rq .= "report_description = ";
			isset($ret["report_description"]) && $ret["report_description"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["report_description"], ENT_QUOTES))."', ": $rq .= "NULL, ";
		}
		$rq .= "period = ";
		isset($ret["period"]) && $ret["period"] != NULL ? $rq .= "'".$ret["period"]."', ": $rq .= "NULL, ";
		$rq .= "report_title = ";
		isset($ret["report_title"]) && $ret["report_title"] != NULL ? $rq .= "'".$ret["report_title"]."', ": $rq .= "NULL, ";		
		$rq .= "subject = ";
		isset($ret["subject"]) && $ret["subject"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["subject"], ENT_QUOTES))."', ": $rq .= "NULL, ";		
		$rq .= "mail_body = ";
		isset($ret["mail_body"]) && $ret["mail_body"] != NULL ? $rq .= "'".addslashes(htmlentities($ret["mail_body"], ENT_QUOTES))."', ": $rq .= "NULL, ";

		$rq .= "report_comment = ";
		$ret["report_comment"] = str_replace("/", '#S#', $ret["report_comment"]);
		$ret["report_comment"] = str_replace("\\", '#BS#', $ret["report_comment"]);
		isset($ret["report_comment"]) && $ret["report_comment"] != NULL ? $rq .= "'".htmlentities($ret["report_comment"], ENT_QUOTES)."', " : $rq .= "NULL, ";

		$rq .= "activate = ";
		isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."'" : $rq .= "NULL ";
		$rq .= "WHERE report_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);


		$fields["name"] = $ret["name"];
		$fields["report_description"] = htmlentities($ret["report_description"], ENT_QUOTES);
		$fields["period"] = $ret["period"];
		$fields["report_title"] = $ret["report_title"];		
		$fields["subject"] = htmlentities($ret["subject"], ENT_QUOTES);
		$fields["mail_body"] = htmlentities($ret["mail_body"], ENT_QUOTES);			
		$fields["report_comment"] = htmlentities($ret["report_comment"], ENT_QUOTES);
		//$oreon->CentreonLogAction->insertLog("service", $service_id["MAX(service_id)"], getHostServiceCombo($service_id, htmlentities($ret["service_description"], ENT_QUOTES)), "c", $fields);
		//$oreon->user->access->updateACL();
	}

	function updateReport_MC($report_id = null)	{
		if (!$report_id)
			return;
		global $form;
		global $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();


		$rq = "UPDATE pdfreports_reports SET ";
		if (isset($ret["name"]) && $ret["name"] != NULL) {
			$rq .= "name = '".$ret["name"]."', ";
			$fields["name"] = $ret["name"];
		}
		if (isset($ret["period"]) && $ret["period"] != NULL) {
			$rq .= "period = '".$ret["period"]."', ";
			$fields["period"] = $ret["period"];
		}
		if (isset($ret["report_title"]) && $ret["report_title"] != NULL) {
			$rq .= "report_title = '".$ret["report_title"]."', ";
			$fields["report_title"] = $ret["report_title"];
		}
		if (isset($ret["subject"]) && $ret["subject"] != NULL) {
			$rq .= "subject = '".htmlentities($ret["subject"], ENT_QUOTES)."', ";
			$fields["subject"] = htmlentities($ret["subject"], ENT_QUOTES);
		}

		if (isset($ret["mail_body"]) && $ret["mail_body"] != NULL) {
			$rq .= "mail_body = '".htmlentities($ret["mail_body"], ENT_QUOTES)."', ";
			$fields["mail_body"] = htmlentities($ret["mail_body"], ENT_QUOTES);
		}

		if (isset($ret["report_activate"]["report_activate"]) && $ret["report_activate"]["report_activate"] != NULL) {
			$rq .= "report_activate = '".$ret["report_activate"]["report_activate"]."', ";
			$fields["report_activate"] = $ret["report_activate"]["report_activate"];
		}



		if (strcmp("UPDATE pdfreports_reports SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE report_id = '".$report_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}

		//$oreon->CentreonLogAction->insertLog("service", $service_id, getHostServiceCombo($service_id, getMyServiceName($service_id), ENT_QUOTES), "mc", $fields);
	}
	
	
	function updateReportContact($report_id = null, $ret = array())	{
		if (!$report_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM pdfreports_reports_contact_relation ";
		$rq .= "WHERE reports_rp_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (isset($ret["report_cs"]))
			$ret = $ret["report_cs"];
		else
			$ret = $form->getSubmitValue("report_cs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO pdfreports_reports_contact_relation ";
			$rq .= "(contact_c_id, reports_rp_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$report_id."')";
			$DBRESULT =& $pearDB->query($rq);
		}
	}

	function updateReportContactGroup($report_id = null, $ret = array())	{
		if (!$report_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM pdfreports_reports_contactgroup_relation ";
		$rq .= "WHERE reports_rp_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (isset($ret["report_cgs"]))
			$ret = $ret["report_cgs"];
		else
			$ret = $form->getSubmitValue("report_cgs");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO pdfreports_reports_contactgroup_relation ";
			$rq .= "(contactgroup_cg_id, reports_rp_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$report_id."')";
			$DBRESULT =& $pearDB->query($rq);
		}
	}

	function updateReportServiceGroup($report_id = null, $ret = array())	{
		if (!$report_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM pdfreports_reports_servicegroup_relation ";
		$rq .= "WHERE reports_rp_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);

		if (isset($ret["report_sg"]))
			$ret = $ret["report_sg"];
		else
			$ret = $form->getSubmitValue("report_sg");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO pdfreports_reports_servicegroup_relation ";
			$rq .= "(servicegroup_sg_id, reports_rp_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$report_id."')";
			$DBRESULT =& $pearDB->query($rq);

		}
	}
	
	function updateReportHost($report_id = null, $ret = array())	{
		if (!$report_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM pdfreports_host_report_relation ";
		$rq .= "WHERE reports_rp_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$ret1 = array();
		$ret2 = array();
		if (isset($ret["report_hs"]))
			$ret1 = $ret["report_hs"];
		else
			$ret1 = $form->getSubmitValue("report_hs");
		if (isset($ret["report_hgs"]))
			$ret2 = $ret["report_hgs"];
		else
			$ret2 = $form->getSubmitValue("report_hgs");
		 if (count($ret2))
			for($i = 0; $i < count($ret2); $i++)	{
				$rq = "INSERT INTO pdfreports_host_report_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id, reports_rp_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$ret2[$i]."', NULL, '".$report_id."')";
				//print "$rq<br/>";
				$DBRESULT =& $pearDB->query($rq);
			}
		else if (count($ret1))
			for($i = 0; $i < count($ret1); $i++)	{
				$rq = "INSERT INTO pdfreports_host_report_relation ";
				$rq .= "(hostgroup_hg_id, host_host_id,  reports_rp_id) ";
				$rq .= "VALUES ";
				$rq .= "(NULL, '".$ret1[$i]."', '".$report_id."')";
				//print "$rq<br/>";
				$DBRESULT =& $pearDB->query($rq);
			}
	}

	# For massive change. We just add the new list if the elem doesn't exist yet
	function updateReportHost_MC($report_id = null)	{
		if (!$report_id) return;
		global $form, $pearDB;
		$rq = "SELECT * FROM pdfreports_host_report_relation ";
		$rq .= "WHERE reports_rp_id = '".$report_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$hsvs = array();
		$hgsvs = array();
		while($arr =& $DBRESULT->fetchRow())	{
			if ($arr["host_host_id"])
				$hsvs[$arr["host_host_id"]] = $arr["host_host_id"];
			if ($arr["hostgroup_hg_id"])
				$hgsvs[$arr["hostgroup_hg_id"]] = $arr["hostgroup_hg_id"];
		}
		$ret1 = array();
		$ret2 = array();
		$ret1 = $form->getSubmitValue("report_hs");
		$ret2 = $form->getSubmitValue("report_hgs");
		 if (count($ret2))
			for($i = 0; $i < count($ret2); $i++)	{
				if (!isset($hgsvs[$ret2[$i]]))	{
					$rq = "DELETE FROM pdfreports_host_report_relation ";
					$rq .= "WHERE reports_rp_id = '".$report_id."' AND host_host_id IS NOT NULL";
					$DBRESULT =& $pearDB->query($rq);
					$rq = "INSERT INTO pdfreports_host_report_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id,  reports_rp_id) ";
					$rq .= "VALUES ";
					$rq .= "('".$ret2[$i]."', NULL,  '".$report_id."')";
					$DBRESULT =& $pearDB->query($rq);
				}
			}
		else if (count($ret1))
			for($i = 0; $i < count($ret1); $i++)	{
				if (!isset($hsvs[$ret1[$i]]))	{
					$rq = "DELETE FROM pdfreports_host_report_relation ";
					$rq .= "WHERE reports_rp_id = '".$report_id."' AND hostgroup_hg_id IS NOT NULL";
					$DBRESULT =& $pearDB->query($rq);
					$rq = "INSERT INTO pdfreports_host_report_relation ";
					$rq .= "(hostgroup_hg_id, host_host_id,  reports_rp_id) ";
					$rq .= "VALUES ";
					$rq .= "(NULL, '".$ret1[$i]."',  '".$report_id."')";
					$DBRESULT =& $pearDB->query($rq);
				}
			}
	}
	




















	
?>