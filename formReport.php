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
 * SVN : $URL:  $
 * SVN : $Id:  $
 * 
 */


ini_set('display_errors',1);
error_reporting(E_ALL);


	if (!isset($oreon))
		exit();

	require_once "./include/reporting/dashboard/common-Func.php";
	require_once "DB-Func.php";

	#
	## Database retrieve information for Report
	#
	
	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("name")));
	}
	
	$report = array();

	if (($o == "c" || $o == "w") && $report_id)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_reports WHERE report_id = '".$report_id."' LIMIT 1");
		# Set base value
		$report = array_map("myDecodeReport", $DBRESULT->fetchRow());
		$DBRESULT->free();
		

		/*
		 * Grab hostgroup || host
		 */
		$DBRESULT =& $pearDB->query("SELECT * FROM pdfreports_host_report_relation hrr WHERE hrr.reports_rp_id = '".$report_id."'");
		while ($parent =& $DBRESULT->fetchRow())	{
			if ($parent["host_host_id"])
				$report["report_hs"][$parent["host_host_id"]] = $parent["host_host_id"];
			else if ($parent["hostgroup_hg_id"])
				$report["report_hgs"][$parent["hostgroup_hg_id"]] = $parent["hostgroup_hg_id"];
		}

		/*
		 * Set Services Group
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT servicegroup_sg_id FROM pdfreports_reports_servicegroup_relation WHERE reports_rp_id = '".$report_id."'");
		for ($i = 0; $notifSg =& $DBRESULT->fetchRow(); $i++)
			$report["report_sg"][$i] = $notifSg["servicegroup_sg_id"];
		$DBRESULT->free();

		
		/*
		 * Set Contact Group
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contactgroup_cg_id FROM pdfreports_reports_contactgroup_relation WHERE reports_rp_id = '".$report_id."'");
		for ($i = 0; $notifCg =& $DBRESULT->fetchRow(); $i++)
			$report["report_cgs"][$i] = $notifCg["contactgroup_cg_id"];
		$DBRESULT->free();

		/*
		 * Set Contact
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT contact_c_id FROM pdfreports_reports_contact_relation WHERE reports_rp_id = '".$report_id."'");
		for ($i = 0; $notifC =& $DBRESULT->fetchRow(); $i++)
			$report["report_cs"][$i] = $notifC["contact_c_id"];
		$DBRESULT->free();		
		
	}

	
	# Contact Groups comes from DB -> Store in $notifCcts Array
	$cgs = array();
	$DBRESULT =& $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
	while ($notifCg =& $DBRESULT->fetchRow())
		$cgs[$notifCg["cg_id"]] = $notifCg["cg_name"];
	$DBRESULT->free();
	
	# Contact comes from DB -> Store in $notifCcts Array
	$cs = array();
	$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name FROM contact ORDER BY contact_name");
	while ($notifC =& $DBRESULT->fetchRow())
		$cs[$notifC["contact_id"]] = $notifC["contact_name"];
	$DBRESULT->free();	
	
	
	# HostGroups comes from DB -> Store in $hgs Array
	$hgs = array();
	$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg =& $DBRESULT->fetchRow())
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	$DBRESULT->free();	
	
	# Hosts comes from DB -> Store in $hosts Array
	$hs = array();
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host =& $DBRESULT->fetchRow())
		$hs[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();


	# Servicegroup comes from DB -> Store in $sgs Array
	$sg = array();
	$DBRESULT =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup WHERE sg_activate = '1' ORDER BY sg_name");
	while ($servicegroupe =& $DBRESULT->fetchRow())
		$sg[$servicegroupe["sg_id"]] = $servicegroupe["sg_name"];
	$DBRESULT->free();

	
	/*
	 * Getting period table list to make the form period selection (today, this week etc.)
	 */
	$periodList = getPeriodList();
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"50");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$attrsAdvSelect 	= array("style" => "width: 270px; height: 100px;");
	$attrsAdvSelectsmall= array("style" => "width: 270px; height: 50px;");
	$attrsAdvSelectbig 	= array("style" => "width: 270px; height: 130px;");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";
	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Report definition"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Report definition"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Report definition"));
	else if ($o == "mc")
		$form->addElement('header', 'title', _("Massive Change"));		

	$form->addElement('header', 'report_information', _("Report Information"));
	$form->addElement('header', 'notification', _("Notification"));	
	$form->addElement('header', 'furtherInfos', _("Additional Information"));


	#
	##  
	#
	$form->addElement('text', 'name', _("Report name"), $attrsText);
	$form->addElement('text', 'report_description', _("Report description"), $attrsText);
	$reportActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Enabled"), '1');
	$reportActivation[] = &HTML_QuickForm::createElement('radio', 'activate', null, _("Disabled"), '0');
	$form->addGroup($reportActivation, 'activate', _("Status"), '&nbsp;');
	if ($o != "mc") {
		$form->setDefaults(array('activate' => '1'));
	}
	

	$form->addElement('select', 'period', _("Period"), $periodList);
	$form->addElement('text', 'report_title', _("Report Title"), $attrsText);
	$form->addElement('text', 'subject', _("Mail Subject"), $attrsText);
	$form->addElement('textarea', 'mail_body', _("Mail body"), $attrsTextarea);	
	$form->addElement('textarea', 'report_comment', _("Comments"), $attrsTextarea);

	/*
	 *  Contact groups
	 */
	 $ams3 =& $form->addElement('advmultiselect', 'report_cgs', _("Implied ContactGroups"), $cgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	/*
	 *  Contacts
	 */
	$ams3 =& $form->addElement('advmultiselect', 'report_cs', _("Implied Contacts"), $cs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);
	
	
	/*
	 *  Hosts groups
	 */
	$ams3 =& $form->addElement('advmultiselect', 'report_hgs', _("Linked HostGroups"), $hgs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);	
	
	/*
	 *  Hosts 
	 */
	 $ams3 =& $form->addElement('advmultiselect', 'report_hs', _("Linked Hosts"), $hs, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);	

	
	/*
	 *  Services groups
	 */
	$ams3 =& $form->addElement('advmultiselect', 'report_sg', _("Linked ServiceGroups"), $sg, $attrsAdvSelect);
	$ams3->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams3->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams3->setElementTemplate($template);
	echo $ams3->getElementJs(false);


	#
	## Further informations
	#
	$form->addElement('hidden', 'report_id');
		$form->addElement('hidden', 'id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	#
	## Form Rules
	#

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('name', 'myReplace');
	$form->addRule('name', _("Compulsory Name"), 'required');
	$form->addRule('report_description', _("Compulsory decription"), 'required');
	$form->addRule('period', _("Compulsory Period"), 'required');
	$form->addRule('subject', _("Compulsory Subject"), 'required');
	$form->addRule('report_title', _("Compulsory Title"), 'required');	
	//$form->addRule('report_hgs', _("Compulsory Hostgroup"), 'required');
	$form->addRule('report_cgs', _("Compulsory Contactgroup"), 'required');

	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Command information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&report_id=".$report_id."'"));
	    $form->setDefaults($report);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($report);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$reportObj =& $form->getElement('report_id');
		if ($form->getSubmitValue("submitA")) 
			$reportObj->setValue(insertReportInDB());
		else if ($form->getSubmitValue("submitC"))
			updateReportInDB($reportObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&report_id=".$reportObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action =& $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listReport.php");
	else	{
		##Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		
		
		$tpl->display("formReport.ihtml");
	}
?>