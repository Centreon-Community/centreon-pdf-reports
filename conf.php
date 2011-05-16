<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
// Be Carefull with internal_name, it's case sensitive (with directory module name)
$module_conf['pdfreports']["name"] = "pdfreports";
$module_conf['pdfreports']["rname"] = "PDF Reports module";
$module_conf['pdfreports']["mod_release"] = "1.0";
$module_conf['pdfreports']["infos"] = "Generate and email PDF reports for hostgroups and servicegroups";
$module_conf['pdfreports']["is_removeable"] = "1";
$module_conf['pdfreports']["author"] = "Linagora / Charles Judith / LKCO / Wistof";
$module_conf['pdfreports']["lang_files"] = "0";
$module_conf['pdfreports']["sql_files"] = "1";
$module_conf['pdfreports']["php_files"] = "1";
$module_conf['pdfreports']["svc_tools"] = "0";  
$module_conf['pdfreports']["host_tools"] = "0";
?>
