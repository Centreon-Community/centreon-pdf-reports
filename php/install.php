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
 * Write here you custom installation script
 */

/*
ini_set('display_errors',1);
error_reporting(E_ALL);
*/


    global $pearDB, $centreon_path;

    require_once $centreon_path."www/include/common/common-Func.php";
    require_once $centreon_path."www/include/options/oreon/generalOpt/DB-Func.php";    
    
    updateOption($pearDB, "pdfreports_smtp_server_address",  "127.0.0.1");
    updateOption($pearDB, "pdfreports_email_sender", "pdfreports@local.dom");
    updateOption($pearDB, "pdfreports_report_author",  "Centreon Server");
    updateOption($pearDB, "pdfreports_report_header_logo", "centreon.gif");
	updateOption($pearDB, "pdfreports_path_gen", $centreon_path . "www/modules/pdfreports/generatedFiles");
    
?>
