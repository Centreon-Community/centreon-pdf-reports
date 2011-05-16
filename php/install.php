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
    
/*    require_once("./modules/reporteon/lib/Crontab.php"); 
 
    $cron = new Crontab(get_current_user());

    $cron->addCron("05", "01", "*", "*", "*", "php -q ". $centreon_path . "www/modules/reporteon/reporteon.php  >> " . $centreon_path . "log/reporteon.log 2>&1 ");
    $cron->writeCrontab();
*/

?>