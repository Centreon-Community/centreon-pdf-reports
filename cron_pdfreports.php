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

//ini_set('display_errors',1);
//error_reporting(E_ALL);
	/**
	 * Configuration file
	 */
	$centreonConf = "/etc/centreon/centreon.conf.php";


	/* ***********************************************
	 * Test if Centreon configuration file exists
	 */
	if (false === file_exists($centreonConf)) {
		file_put_contents('php://stderr', "The configuration file does not exists.");
		exit(1);
	}



	function programExit($msg) {
	    echo "[".date("Y-m-d H:i:s")."] ".$msg."\n";
	    exit;
	}

	(int)$nbProc = exec("ps -edf | grep cron_pdfreports.php | grep -v grep | wc -l");
	if ($nbProc > 2) {
		programExit("More than one cron_pdfreports.php process currently running. Going to exit...");
	}

	ini_set('max_execution_time', 0);

	try {
		
		require_once $centreonConf;

		$centreonClasspath = $centreon_path . 'www/class';

		/* Include class */
		require_once $centreonClasspath . '/centreonDB.class.php';
	
		
		require_once $centreonClasspath . '/centreonAuth.class.php';
		require_once $centreonClasspath . '/centreonLog.class.php';
	
	
		include_once $centreon_path . "www/include/common/common-Func.php";
		require_once("Mail-Func.php");
		require_once("PDF-Func.php");

	
		require_once("DB-Func.php");
		
		$centreon_version =getCentreonVersion();
		$admin_alias  = getAdminUserAlias();
		
		if ( $centreon_version >= 220) {
			require_once $centreonClasspath . '/centreonUser.class.php';
			require_once $centreonClasspath . '/centreonSession.class.php';   
			require_once $centreonClasspath . '/centreon.class.php';
			
			$CentreonLog = new CentreonUserLog(-1, $pearDB);
			$centreonAuth = new CentreonAuth($admin_alias, "", "", $pearDB, $CentreonLog, 1);
			//$centreon = new Centreon($centreonAuth->userInfos, $generalOptions["nagios_version"]);
			$centreon = new Centreon($centreonAuth->userInfos,getVersion());
			$oreon = $centreon;
	
		} else {
			require_once $centreonClasspath . '/other.class.php';	
			require_once $centreonClasspath . '/User.class.php';
			require_once $centreonClasspath . '/Session.class.php';  
			require_once $centreonClasspath . '/Oreon.class.php';
	
			$CentreonLog = new CentreonUserLog(-1, $pearDB);
			$centreonAuth = new CentreonAuth($admin_alias, "", "", $pearDB, $CentreonLog, 1);
			//$centreonAuth->passwdOk = 1;
	
			$user =& new User($centreonAuth->userInfos, getVersion());
			//$user =& new User($centreonAuth->userInfos, "3");		
			$oreon = new Oreon($user);
			//$nagios_path_img = $oreon->optGen["nagios_path_img"] ;
			
		}
		require_once $centreonClasspath . '/centreonACL.class.php';  
		include_once $centreon_path . "www/include/reporting/dashboard/common-Func.php";
		include_once $centreon_path . "www/include/reporting/dashboard/DB-Func.php";
	
	/*
	*	Main
	*/
	
	global $pearDB, $pearDBndo, $pearDBO, $oreon ;	
	
	 $period_arg = NULL;

	 if (count($argv) > 1)
		$period_arg = $argv[1];
	
	$reports = array();
	$reports = getActiveReports($period_arg);
	
	
	
		foreach ( $reports as $report_id => $name ) {
			//print_r($report_id);
			$hosts = array();
			$reportinfo = array();
			$hosts = getHostReport($report_id);
			//print_r($hosts);
			$reportinfo = getReportInfo($report_id);
			//print_r($reportinfo);
			$services = getServiceGroupReport($report_id);
			//print_r($services);
			$dates = getPeriodToReportFork($reportinfo['period']);
			$start_date = $dates[0] ;
			$end_date = $dates[1];
			
			$reportingTimePeriod = getreportingTimePeriod();
			
			// Generate hostgroup reports			
			
			if (isset($hosts) && count($hosts) > 0) {
			foreach ( $hosts['report_hgs'] as $hgs_id ) {      
		
				$stats = array();
				$stats = getLogInDbForHostGroup($hgs_id , $start_date, $end_date, $reportingTimePeriod);
				
				//print_r($stats);
				//tableau contenant la liste des pdf générés
				$Allfiles[] = pdfGen( getMyHostGroupName($hgs_id), 'hgs', $start_date, $end_date, $stats, $l , $reportinfo["report_title"]  );
				//$Allfiles[] = pdfGen( getMyHostGroupName($hgs_id), 'hgs', $start_date, $end_date, $stats, $l, getGeneralOptInfo("pdfreports_report_header_logo") , $reportinfo["report_title"]  );
				
				//print_r($Allfiles); 
			}
		}
			// Generate servicegroup reports
		if (isset( $services ) && count($services) > 0 ) {			
			foreach ( $services['report_sg'] as $sg_id ) {      

				$sg_stats = array();
				$sg_stats = getLogInDbForServicesGroup($sg_id , $start_date, $end_date, $reportingTimePeriod);
				
				//print_r($stats);				

				//tableau contenant la liste des pdf générés
				$Allfiles[] = pdfGen( getMyServiceGroupName($sg_id), 'sgs', $start_date, $end_date, $sg_stats, $l,  $reportinfo["report_title"]);
				//$Allfiles[] = pdfGen( getMyServiceGroupName($sg_id), 'sgs', $start_date, $end_date, $sg_stats, $l, getGeneralOptInfo("pdfreports_report_header_logo") , $reportinfo["report_title"]  );
				
				//print_r($Allfiles); 
			}
		}

			$emails = getReportContactEmail($report_id);			
			//print_r( $emails );			
			$files = array();
			foreach ( $Allfiles as $file) {				
				$files[basename($file)]["url"] = $file;					
			}
			//print_r($files);
			
			mailer(getGeneralOptInfo("pdfreports_report_author"),getGeneralOptInfo("pdfreports_email_sender"),$emails,$reportinfo['subject'],$reportinfo['mail_body'] , getGeneralOptInfo("pdfreports_smtp_server_address"),$files,$reportinfo['name'] );
			$files = null;
			$Allfiles = null;
			$emails = null;
			$services = null ;
			$hosts = null;
		}

	} catch (Exception $e) {
		programExit($e->getMessage());
	}









