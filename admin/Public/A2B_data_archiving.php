<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
**/

$menu_section = 16;
require_once "../../common/lib/admin.defines.php";
/**
 * @var Smarty $smarty
 * @var FormHandler $HD_Form
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

getpost_ifset([
    "posted_search", "posted_archive", "enable_search_start_date", "search_start_date", "enable_search_end_date",
    "search_end_date", "enable_search_start_date2", "search_start_date2", "enable_search_end_date2", "search_end_date2",
    "enable_search_months", "search_months", 
]);
/**
 * @var bool|string $posted_search whether the user has clicked the search button
 * @var bool|string $posted_archive whether the user has clicked the archive button
 * @var bool|string $enable_search_start_date
 * @var string $search_start_date
 * @var bool|string $enable_search_end_date
 * @var string $search_end_date
 * @var bool|string $enable_search_start_date2
 * @var string $search_start_date2
 * @var bool|string $enable_search_end_date2
 * @var string $search_end_date2
 * @var bool|string $enable_search_months
 * @var string $search_months
 */

$HD_Form = new FormHandler("cc_card", "Customer");
$HD_Form->init();

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form->search_session_key = 'entity_archiving_selection';
$language_list = array();
$language_list["0"] = array( gettext("ENGLISH"), "en");
$language_list["1"] = array( gettext("SPANISH"), "es");
$language_list["2"] = array( gettext("FRENCH"),  "fr");

$language_list_r = array();
$language_list_r["0"] = array("en", gettext("ENGLISH"));
$language_list_r["1"] = array("es", gettext("SPANISH"));
$language_list_r["2"] = array("fr", gettext("FRENCH"));

$simultaccess_list = array();
$simultaccess_list["0"] = array( gettext("INDIVIDUAL ACCESS"), "0");
$simultaccess_list["1"] = array( gettext("SIMULTANEOUS ACCESS"), "1");

$simultaccess_list_r = array();
$simultaccess_list_r["0"] = array( "0", gettext("INDIVIDUAL ACCESS"));
$simultaccess_list_r["1"] = array( "1", gettext("SIMULTANEOUS ACCESS"));

$currency_list = array();
$currency_list_r = array();
$indcur=0;

$currencies_list = get_currencies();
foreach ($currencies_list as $key => $cur_value) {
    $currency_list[$key]  = array( $cur_value["name"].' ('.$cur_value["value"].')', $key);
    $currency_list_r[$key]  = array( $key, $cur_value["name"]);
    $currency_list_key[$key][0] = $key;
}

$cardstatus_list = array();
$cardstatus_list["0"]  = array( gettext("CANCELLED"), "0");
$cardstatus_list["1"]  = array( gettext("ACTIVE"), "1");
$cardstatus_list["2"]  = array( gettext("NEW"), "2");
$cardstatus_list["3"]  = array( gettext("WAITING-MAILCONFIRMATION"), "3");
$cardstatus_list["4"]  = array( gettext("RESERVED"), "4");
$cardstatus_list["5"]  = array( gettext("EXPIRED"), "5");

$cardstatus_list_r = array();
$cardstatus_list_r["0"]  = array("0", gettext("CANCELLED"));
$cardstatus_list_r["1"]  = array("1", gettext("ACTIVE"));
$cardstatus_list_r["2"]  = array("2", gettext("NEW"));
$cardstatus_list_r["3"]  = array("3", gettext("WAITING-MAILCONFIRMATION"));
$cardstatus_list_r["4"]  = array("4", gettext("RESERVED"));
$cardstatus_list_r["5"]  = array("5", gettext("EXPIRED"));

$cardstatus_list_acronym = array();
$cardstatus_list_acronym["0"]  = array( gettext("<acronym title=\"CANCELLED\">".gettext("CANCEL")."</acronym>"), "0");
$cardstatus_list_acronym["1"]  = array( gettext("<acronym title=\"ACTIVE\">".gettext("ACTIV")."</acronym>"), "1");
$cardstatus_list_acronym["2"]  = array( gettext("<acronym title=\"NEW\">".gettext("NEW")."</acronym>"), "2");
$cardstatus_list_acronym["3"]  = array( gettext("<acronym title=\"WAITING-MAILCONFIRMATION\">".gettext("WAIT")."</acronym>"), "3");
$cardstatus_list_acronym["4"]  = array( gettext("<acronym title=\"RESERVED\">".gettext("RESERV")."</acronym>"), "4");
$cardstatus_list_acronym["5"]  = array( gettext("<acronym title=\"EXPIRED\">".gettext("EXPIR")."</acronym>"), "5");

$typepaid_list = array();
$typepaid_list["0"]  = array( gettext("PREPAID CARD"), "0");
$typepaid_list["1"]  = array( gettext("POSTPAY CARD"), "1");

$expire_list = array();
$expire_list["0"]  = array( gettext("NO EXPIRY"), "0");
$expire_list["1"]  = array( gettext("EXPIRE DATE"), "1");
$expire_list["2"]  = array( gettext("EXPIRE DAYS SINCE FIRST USE"), "2");
$expire_list["3"]  = array( gettext("EXPIRE DAYS SINCE CREATION"), "3");

$actived_list = array();
$actived_list["t"] = array( gettext("On"), "t");
$actived_list["f"] = array( gettext("Off"), "f");

$yesno = array();
$yesno["1"] = array( gettext("Yes"), "1");
$yesno["0"] = array( gettext("No"), "0");

$invoiceday_list = array();
for ($k=0;$k<=28;$k++)
    $invoiceday_list["$k"]  = array( "$k", "$k");

$HD_Form->AddViewElement(gettext("ID"), "id");
$HD_Form->AddViewElement(gettext("ACCOUNT NUMBER"), "username", true, 30, "display_customer_link");
$HD_Form->AddViewElement("<acronym title=\"" . gettext("BALANCE") . "\">" . gettext("BA") . "</acronym>", "credit", true, "", "display_2dec");
$HD_Form->AddViewElement(gettext("LASTNAME"), "lastname", true, 15);
$HD_Form->AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $cardstatus_list_acronym);
$HD_Form->AddViewElement(gettext("LG"), "language");
$HD_Form->AddViewElement(gettext("USE"), "inuse");
$HD_Form->AddViewElement("<acronym title=\"" . gettext("CURRENCY") . "\">" . gettext("CUR") . "</acronym>", "currency", true, 0, "", "list", $currency_list_key);
$HD_Form->AddViewElement(gettext("SIP"), "sip_buddy", true, 0, "", "list", $yesno);
$HD_Form->AddViewElement(gettext("IAX"), "iax_buddy", true, 0, "", "list", $yesno);
$HD_Form->AddViewElement("<acronym title=\"AMOUNT OF CALL DONE\">" . gettext("ACD") . "</acronym>", "nbused");
$HD_Form->FieldViewElement ('id, username, credit, lastname, status, language, inuse, currency, sip_buddy, iax_buddy, nbused');

$HD_Form->CV_NO_FIELDS  = _("NO CUSTOMER SEARCHED!");
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 30;

$HD_Form->search_form_enabled = true;
$HD_Form->search_form_title = gettext('Define specific criteria to search for cards created.');
$HD_Form->search_date_enabled = true;
$HD_Form->search_date_text = gettext('Creation date');
$HD_Form->search_date_column = 'creationdate';

$HD_Form->search_date2_enabled = true;
$HD_Form->search_date2_text = gettext('FIRST USE DATE');
$HD_Form->search_date2_column = 'firstusedate';

$HD_Form->search_months_ago_enabled = true;
$HD_Form->search_months_ago_text = gettext('Select customer created more than');
$HD_Form->search_months_ago_column = 'creationdate';

//Select card older than : 3 Months, 4 Months, 5.... 12 Months
$HD_Form->AddSearchTextInput(_("Account"), 'username','usernametype');
$HD_Form->AddSearchTextInput(_("Last name"),'lastname','lastnametype');
$HD_Form->AddSearchTextInput(_("Login"),'useralias','useraliastype');
$HD_Form->AddSearchTextInput(_("MAC address"),'mac_addr','macaddresstype');
$HD_Form->AddSearchTextInput(_("Email"),'email','emailtype');
$HD_Form->AddSearchComparisonInput(_("Card"),'id1','id1type','id2','id2type','id');
$HD_Form->AddSearchComparisonInput(_("Credit"),'credit1','credit1type','credit2','credit2type','credit');
$HD_Form->AddSearchComparisonInput(_("In use"),'inuse1','inuse1type','inuse2','inuse2type','inuse');

$HD_Form->AddSearchSelectInput(_("Language"), "language", $language_list_r);
$HD_Form->AddSearchSqlSelectInput(_("Rate plan"), "cc_tariffgroup", "id, tariffgroupname, id", "", "tariffgroupname", "ASC", "tariff");
$HD_Form->AddSearchSelectInput(_("Status"), "status", $cardstatus_list_r);
$HD_Form->AddSearchSelectInput(_("Access"), "simultaccess", $simultaccess_list_r);
$HD_Form->AddSearchSqlSelectInput(_("Group"), "cc_card_group", "id, name", "", "name", "ASC", "id_group");
$HD_Form->AddSearchSelectInput(_("Currency"), "currency", $currency_list_r);
$HD_Form->AddSearchSelectInput(_("Language"), "language", $language_list_r);

if ($posted_search === true && $posted_archive === false) {
    $HD_Form->AddSearchButton(
        "posted_archive",
        "Archive Displayed Calls",
        "true", "btn-secondary",
        "return confirm('This action will archive the selected customers. Are you sure?')"
    );
}

$HD_Form->prepare_list_subselection('list');
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

$nb_customer = 0;

/***********************************************************************************/
getpost_ifset(array('archive', 'id'));

if (isset($archive) && !empty($archive)) {
    $condition = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $params);
    $condition = " WHERE $condition";
    echo "condition : $condition";
    $rec = archive_data($condition, $params);
    if($rec > 0)
        $archive_message = "The data has been successfully archived";
}

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');
echo $CC_help_data_archive;

if (!isset($submit)) {
    $HD_Form->create_search_form();
}

?>
<center>
<FORM name="frm_archive" id="frm_archive" method="post" action="A2B_call_archiving.php">
    <?= $HD_Form->csrf_inputs() ?>
    <table class="bar-status" width="50%" border="0" cellspacing="1" cellpadding="2" align="center">
        <tbody>
        <tr>
            <td width="30%" align="left" valign="top" class="bgcolor_004">
                <font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("ARCHIVING OPTIONS");?></font>
            </td>
            <td width="70%" align="CENTER" class="bgcolor_005">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr>
              <td class="fontstyle_searchoptions">
            <select name="archiveselect" class="form_input_select" onchange="form.submit();">
            <option value="" ><?php echo gettext("Customer Archiving");?></option>
            <option value="" ><?php echo gettext("Calls Archiving");?></option>
            </select>
                </td>
            </tr></table></td>
        </tr>
        </tbody>
    </table>
</FORM>
</center>

<div class="row pb-3">
    <div class="col">
        <form name="theFormFilter" action="">
            <input type="hidden" name="popup_select" value="<?= $popup_select ?>"/>
            <input type="hidden" name="popup_formname" value="<?= $popup_formname ?>"/>
            <input type="hidden" name="popup_fieldname" value="<?= $popup_fieldname ?>"/>
            <input type="hidden" name="archive" value="true"/>
            <button type="submit" class="btn btn-primary" onclick="return confirm('This action will archive the data, Are you sure?')">
                <?= gettext("Archiving All");?>
            </button>
        </form>
    </div>
</div>
<?php

if (isset($archive) && !empty($archive)) {
    $HD_Form->CV_NO_FIELDS = "";
    print "<div align=\"center\">".$archive_message."</div>";
}
$HD_Form->create_form($form_action, $list);

$smarty->display('footer.tpl');

function archive_data(string $where, array $params = []): bool
{
    $handle = DbConnect();
    $handle->Execute("INSERT INTO cc_card_archive SELECT id, creationdate, firstusedate, expirationdate, enableexpire, expiredays, username, useralias, uipass, credit, tariff, id_didgroup, activated, status, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, inuse, simultaccess, currency, lastuse, nbused, typepaid, creditlimit, voipcall, sip_buddy, iax_buddy, language, redial, runservice, nbservice, id_campaign, num_trials_done, vat, servicelastrun, initialbalance, invoiceday, autorefill, loginkey, mac_addr, id_timezone, tag, voicemail_permitted, voicemail_activated, last_notification, email_notification, notify_email, credit_notification, id_group, company_name, company_website, VAT_RN, traffic, traffic_target, discount, restriction FROM cc_card $where", $params);
    $handle->Execute("DELETE FROM cc_call $where", $params);

    return $handle->CommitTrans();
}
