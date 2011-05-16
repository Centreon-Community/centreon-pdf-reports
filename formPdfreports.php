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

	if (!isset($oreon))
		exit();

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	global $path;
	$path = "./modules/pdfreports/";

	#PHP functions
	require_once $path."DB-Func.php";		
	require_once $centreon_path."www/include/common/common-Func.php";
	require_once $centreon_path."www/include/options/oreon/generalOpt/DB-Func.php";
	require_once $centreon_path."www/class/centreonDB.class.php";
	require_once "./include/reporting/dashboard/common-Func.php";
		
		
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	/*
	 * IMG comes from DB -> Store in $logoImg Array
	 */
	$logoImg = array();
	$logoImg = return_logos_list(1,$path. "img/headers" );

	#
	## pdfreports information
	#
	$form->addElement('header', 'pdfreports', _("PDF Reports configuration"));
	$form->addElement('header', 'pdfreports_email_config', _("Email Configuration"));
	$form->addElement('header', 'pdfreports_report_config', _("Reports Configuration"));
	$form->addElement('text', 'pdfreports_smtp_server_address', _("SMTP server address"), $attrsText);
	$form->addElement('text', 'pdfreports_email_sender', _("Email Sender"), $attrsText);	
	$form->addElement('text', 'pdfreports_report_author', _("Report Author"), $attrsText);
//	$form->addElement('select', 'reporteon_report_header_logo', _("Header Logo"), $logoImg, array("id"=>"reporteon_report_header_logo", "onChange"=>"showLogo('reporteon_report_header_logo',this.value)"));
	$form->addElement('select', 'pdfreports_report_header_logo', _("Header Logo"), $logoImg, array("id"=>"pdfreports_report_header_logo"));
	
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('pdfreports_smtp_server_address', _("Required Field"), 'required');
	$form->addRule('pdfreports_email_sender', _("Required Field"), 'required');
	$form->addRule('pdfreports_report_author', _("Required Field"), 'required');


	#
	##End of form definition
	#

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path , $tpl);

	$form->setDefaults($oreon->optGen);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		
		$ret = array();
		$ret = $form->getSubmitValues();
		
		updateOption($pearDB, "pdfreports_smtp_server_address", isset($ret["pdfreports_smtp_server_address"]) && $ret["pdfreports_smtp_server_address"] != NULL ? $ret["pdfreports_smtp_server_address"] : "127.0.0.1");
		updateOption($pearDB, "pdfreports_email_sender", isset($ret["pdfreports_email_sender"]) && $ret["pdfreports_email_sender"] != NULL ? $ret["pdfreports_email_sender"] : "pdfreports@local.loc");
		updateOption($pearDB, "pdfreports_report_author", isset($ret["pdfreports_report_author"]) && $ret["pdfreports_report_author"] != NULL ? $ret["pdfreports_report_author"] : "");
		updateOption($pearDB, "pdfreports_report_header_logo", isset($ret["pdfreports_report_header_logo"]) && $ret["pdfreports_report_header_logo"] != NULL ? $ret["pdfreports_report_header_logo"] : "");	
				
		# Update in Oreon Object
		$oreon->initOptGen($pearDB);

		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=pdfreports'"));

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('javascript', '<script type="text/javascript" src="./include/common/javascript/showLogo.js"></script>' );
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("formPdfreports.ihtml");
?>
<!-- <script type="text/javascript">
		showLogo('reporteon_report_header_logo', document.getElementById('reporteon_report_header_logo').value);
</script> -->