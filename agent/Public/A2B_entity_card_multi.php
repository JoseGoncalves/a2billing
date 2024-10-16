<?php

use A2billing\Agent;
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

require_once "../../common/lib/agent.defines.php";
include './form_data/FG_var_card.inc';

if (! has_rights (Agent::ACX_CUSTOMER)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('nb_to_create', 'creditlimit', 'cardnum', 'choose_tariff', 'gen_id', 'cardnum', 'choose_simultaccess',
    'choose_currency', 'choose_typepaid', 'creditlimit', 'enableexpire', 'expirationdate', 'expiredays', 'runservice', 'sip', 'iax',
    'cardnumberlength_list', 'tag', 'id_group', 'discount', 'id_seria'));

$nb_error = 0;
$msg_error = '';
$group_error = false;
$tariff_error=false;
$number_error=false;
$expdate_error=false;
$expday_error=false;

if ($action=="generate") {
    if (!is_numeric($id_group) || $id_group<1) {
        $nb_error++;
        $group_error=true;
        $msg_error = gettext("- Choose a GROUP for the customers!");
    }
    if (!is_numeric($choose_tariff) || $choose_tariff<1) {
        $nb_error++;
        $tariff_error=true;
        if(!empty($msg_error)) $msg_error .= "<br/>";
        $msg_error .= gettext("- Choose a CALL PLAN for the customers!");
    }
    if (!is_numeric($expiredays) || $expiredays<0) {
        $nb_error++;
        $expday_error=true;
        if(!empty($msg_error)) $msg_error .= "<br/>";
        $msg_error .= gettext("- Choose an EXPIRATIONS DAYS  equal or higher than 0 for the customers!");
    }
    if (empty($expirationdate) || strtotime($expirationdate)===FALSE) {
        $nb_error++;
        $expdate_error=true;
        if(!empty($msg_error)) $msg_error .= "<br/>";
        $msg_error .= gettext("- EXPIRATION DAY inserted is invalid, it must respect the date format YYYY-MM-DD HH:MM:SS (time is optional) !");
    }
    if (!is_numeric($nb_to_create) || $nb_to_create < 1 || $nb_to_create > 100) {
        $nb_error++;
        $number_error = true;
        if (!empty ($msg_error))
            $msg_error .= "<br/>";
        $msg_error .= gettext("- Choose the number of customers that you want generate!");
    }
}
$nbcard = $nb_to_create;
if ($nbcard>0 && $action=="generate" && $nb_error==0) {

    $FG_ADITION_SECOND_ADD_TABLE  = "cc_card";
    $FG_ADITION_SECOND_ADD_FIELDS = "username, useralias, credit, tariff, activated, lastname, firstname, email, address, city, state, country, zipcode, phone, simultaccess, currency, typepaid , creditlimit, enableexpire, expirationdate, expiredays, uipass, runservice, tag,id_group, discount, id_seria";

    if (DB_TYPE != "postgres") {
        $FG_ADITION_SECOND_ADD_FIELDS .= ",creationdate ";
    }

    $FG_TABLE_SIP_NAME="cc_sip_buddies";
    $FG_TABLE_IAX_NAME="cc_iax_buddies";

    $FG_QUERY_ADITION_SIP_IAX_FIELDS = "name, accountcode, regexten, amaflags, callerid, context, dtmfmode, host, type, username, allow, secret, id_cc_card, nat,  qualify";
    if (isset($sip)) {
        $FG_ADITION_SECOND_ADD_FIELDS .= ", sip_buddy";
        $instance_sip_table = new Table($FG_TABLE_SIP_NAME, $FG_QUERY_ADITION_SIP_IAX_FIELDS);
    }

    if (isset($iax)) {
        $FG_ADITION_SECOND_ADD_FIELDS .= ", iax_buddy";
        $instance_iax_table = new Table($FG_TABLE_IAX_NAME, $FG_QUERY_ADITION_SIP_IAX_FIELDS);
    }

    if (isset($sip) ||  isset($iax)) {
        $list_names = explode(",",$FG_QUERY_ADITION_SIP_IAX);
        $type = FRIEND_TYPE;
        $allow = FRIEND_ALLOW;
        $context = FRIEND_CONTEXT;
        $nat = FRIEND_NAT;
        $amaflags = FRIEND_AMAFLAGS;
        $qualify = FRIEND_QUALIFY;
        $host = FRIEND_HOST;
        $dtmfmode = FRIEND_DTMFMODE;
    }

    $instance_sub_table = new Table($FG_ADITION_SECOND_ADD_TABLE, $FG_ADITION_SECOND_ADD_FIELDS);
    $gen_id = time();
    $_SESSION["IDfilter"]=$gen_id;

    $creditlimit = is_numeric($creditlimit) ? $creditlimit : 0;
    //initialize refill parameter
    $description_refill = gettext("CREATION CARD REFILL");
    $field_insert_refill = " credit,card_id, description";
    $instance_refill_table = new Table("cc_logrefill", $field_insert_refill);

    for ($k=0; $k<$nbcard; $k++) {
        $arr_card_alias = gen_card_with_alias($cardnumberlength_list);
        $cardnum = $arr_card_alias[0];
        $useralias = $arr_card_alias[1];
        $addcredit=0;
        $passui_secret = MDP_NUMERIC(5).MDP_STRING(10).MDP_NUMERIC(5);
        $FG_ADITION_SECOND_ADD_VALUE  = "'$cardnum', '$useralias', '$addcredit', '$choose_tariff', 't', '$gen_id', '', '', '', '', '', '', '', '', $choose_simultaccess, '$choose_currency', $choose_typepaid, $creditlimit, $enableexpire, '$expirationdate', $expiredays, '$passui_secret', '$runservice', '$tag', '$id_group', '$discount', '$id_seria'";

        if (DB_TYPE != "postgres") {
            $FG_ADITION_SECOND_ADD_VALUE .= ",now() ";
        }
        if (isset($sip)) $FG_ADITION_SECOND_ADD_VALUE .= ", 1";
        if (isset($iax)) $FG_ADITION_SECOND_ADD_VALUE .= ", 1";

        $id_cc_card = $instance_sub_table -> Add_table ($HD_Form -> DBHandle, $FG_ADITION_SECOND_ADD_VALUE, null, null, $HD_Form -> FG_QUERY_PRIMARY_KEY);
        //create refill for each cards

        if ($addcredit > 0) {
            $value_insert_refill = "'$addcredit', '$id_cc_card', '$description_refill' ";
            $instance_refill_table -> Add_table ($HD_Form -> DBHandle, $value_insert_refill, null, null);
        }

        // Insert data for sip_buddy
        if (isset($sip)) {
            $FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflags', '$cardnum', '$context', '$dtmfmode','$host', '$type', '$cardnum', '$allow', '".$passui_secret."', '$id_cc_card', '$nat', '$qualify'";
            $result_query1 = $instance_sip_table -> Add_table ($HD_Form ->DBHandle, $FG_QUERY_ADITION_SIP_IAX_VALUE, null, null, null);
            if (USE_REALTIME) {
                  $_SESSION["is_sip_iax_change"]=1;
                  $_SESSION["is_sip_changed"]=1;
            }
        }

        // Insert data for iax_buddy
        if (isset($iax)) {
            //$FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflag', '$cardnum', '$context', 'RFC2833','dynamic', 'friend', '$cardnum', 'g729,ulaw,alaw,gsm','".$passui_secret."'";
            $FG_QUERY_ADITION_SIP_IAX_VALUE = "'$cardnum', '$cardnum', '$cardnum', '$amaflags', '$cardnum', '$context', '$dtmfmode','$host', '$type', '$cardnum', '$allow', '".$passui_secret."', '$id_cc_card', '$nat', '$qualify'";
            $result_query2 = $instance_iax_table -> Add_table ($HD_Form ->DBHandle, $FG_QUERY_ADITION_SIP_IAX_VALUE, null, null, null);
            if (USE_REALTIME) {
                $_SESSION["is_sip_iax_change"]=1;
                $_SESSION["is_iax_changed"]=1;
            }
        }
    }

    // Save Sip accounts to file
    if (isset($sip)) {
        $buddyfile = BUDDY_SIP_FILE;

        $instance_table_friend = new Table($FG_TABLE_SIP_NAME, 'id, ' . $FG_QUERY_ADITION_SIP_IAX);
        $list_friend = $instance_table_friend -> get_list ($HD_Form ->DBHandle);
        if (is_array($list_friend)) {
            $fd=fopen($buddyfile,"w");
            if (!$fd) {
                $error_msg= "<br><center><b><font color=red>".gettext("Could not open buddy file")." ". $buddyfile."</font></b></center>";
            } else {
                foreach ($list_friend as $data) {
                    $line="\n\n[".$data[1]."]\n";
                    if (fwrite($fd, $line) === FALSE) {
                        echo "Impossible to write to the file ($buddyfile)";
                        break;
                    } else {
                        for ($i=1;$i<count($data)-1;$i++) {
                            if (strlen($data[$i+1])>0) {
                                if (trim($list_names[$i]) == 'allow') {
                                    $codecs = explode(",",$data[$i+1]);
                                    $line = "";
                                    foreach ($codecs as $value)
                                        $line .= trim($list_names[$i]).'='.$value."\n";
                                } else {
                                    $line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
                                }
                                if (fwrite($fd, $line) === FALSE) {
                                    echo gettext("Impossible to write to the file")." ($buddyfile)";
                                    break;
                                }
                            }
                        }
                    }
                }
                fclose($fd);
            }
        }//end if is_array
    } // END SAVE SIP ACCOUNTS

    // Save IAX accounts to file
    if (isset($iax)) {
        $buddyfile = BUDDY_IAX_FILE;

        $instance_table_friend = new Table($FG_TABLE_IAX_NAME, 'id, ' . $FG_QUERY_ADITION_SIP_IAX);
        $list_friend = $instance_table_friend -> get_list ($HD_Form ->DBHandle);

        if (is_array($list_friend)) {
            $fd=fopen($buddyfile,"w");
            if (!$fd) {
                $error_msg= "<br><center><b><font color=red>".gettext("Could not open buddy file"). $buddyfile."</font></b></center>";
            } else {
                foreach ($list_friend as $data) {
                    $line="\n\n[".$data[1]."]\n";
                     if (fwrite($fd, $line) === FALSE) {
                        echo "Impossible to write to the file ($buddyfile)";
                        break;
                    } else {
                        for ($i=1;$i<count($data)-1;$i++) {
                            if (strlen($data[$i+1])>0) {
                                if (trim($list_names[$i]) == 'allow') {
                                    $codecs = explode(",",$data[$i+1]);
                                    $line = "";
                                    foreach ($codecs as $value)
                                        $line .= trim($list_names[$i]).'='.$value."\n";
                                } else {
                                    $line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
                                }
                                if (fwrite($fd, $line) === FALSE) {
                                    echo gettext("Impossible to write to the file")." ($buddyfile)";
                                    break;
                                }
                            }
                        }
                    }
                }
                fclose($fd);
            }
        }// end if is_array
    } // END SAVE IAX ACCOUNTS

}
if (!isset($_SESSION["IDfilter"])) $_SESSION["IDfilter"]='NODEFINED';

$HD_Form -> FG_QUERY_WHERE_CLAUSE = " lastname='".$_SESSION["IDfilter"]."'";
$HD_Form->list_query_conditions["lastname"] = $_SESSION["IDfilter"];

// END GENERATE CARDS

$HD_Form -> init();

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_generate_customer;

$instance_table_tariff = new Table("cc_tariffgroup LEFT JOIN cc_agent_tariffgroup ON cc_agent_tariffgroup.id_tariffgroup = cc_tariffgroup.id ", "id, tariffgroupname");
$FG_TABLE_CLAUSE = "cc_agent_tariffgroup.id_agent = ".$_SESSION['agent_id'];
$list_tariff = $instance_table_tariff -> get_list ($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "tariffgroupname");
$nb_tariff = count($list_tariff);
$FG_TABLE_CLAUSE =  "cc_card_group.id_agent=".$_SESSION['agent_id'] ;
$instance_table_group=  new Table("cc_card_group", " id, name ");
$list_group = $instance_table_group  -> get_list ($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "name");

// FORM FOR THE GENERATION
?>
<div align="center">
<?php if (!empty($msg_error) && $nb_error>0 ) { ?>
    <div class="msg_error" style="width:70%;text-align:left;">
        <?php echo $msg_error ?>
    </div>
<?php } else { ?>
    <br/>
    <br/>
    <br/>
    <br/>
<?php }?>
<table align="center"  class="bgcolor_001" border="0" width="65%">
<form name="theForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) ?>" method="POST">
<?= $HD_Form->csrf_inputs() ?>
<tr>
    <td align="left" width="100%">
    <strong>1)</strong> <?php echo gettext("Length of card number :");?>
    <select name="cardnumberlength_list" size="1" class="form_input_select">
    <?php
    foreach ($A2B -> cardnumber_range as $value) {
    ?>
        <option value='<?php echo $value ?>'
        <?php if ($value == $cardnumberlength_list) echo "selected";
        ?>> <?php echo $value." ".gettext("Digits");?> </option>

    <?php
    }
    ?>
    </select><br>
    <strong>2)</strong>
     <?php echo gettext("Number of customers to create")?> :
        <input class="form_input_text" name="nb_to_create" size="5" maxlength="5" value="<?php echo $nb_to_create; ?>" >
        <?php echo gettext("(max 100)");?>
    <br/>

    <strong>3)</strong>
    <?php echo gettext("Call plan");?> :
    <select NAME="choose_tariff" size="1" class="form_input_select" >
        <option value=''><?php echo gettext("Choose a Call Plan");?></option>
    <?php foreach ($list_tariff as $recordset) { ?>
        <option class=input value='<?php echo $recordset[0]?>' <?php if($recordset[0]==$choose_tariff) echo "selected"; ?> ><?php echo $recordset[1]?></option>
    <?php } ?>
    </select>
    <?php if ($tariff_error) { ?>
        <img style="vertical-align:middle;" src="<?php echo Images_Path;?>/exclamation.png" />
    <?php } ?>
    <br/>

    <strong>4)</strong>
    <?php echo gettext("Initial amount of credit");?> : 0
    <br/>

    <strong>5)</strong>
    <?php echo gettext("Simultaneous access");?> :
    <select NAME="choose_simultaccess" size="1" class="form_input_select" >
        <option value='0' <?php if($choose_simultaccess== 0 || empty($choose_simultaccess)) echo "selected"; ?>><?php echo gettext("INDIVIDUAL ACCESS");?></option>
        <option value='1' <?php if($choose_simultaccess== 1) echo "selected"; ?>><?php echo gettext("SIMULTANEOUS ACCESS");?></option>
       </select>
    <br/>

    <strong>6)</strong>
    <?php echo gettext("Currency");?> :
    <select NAME="choose_currency" size="1" class="form_input_select" >
    <?php foreach (get_currencies() as $key => $cur_value) { ?>
        <option value='<?php echo $key ?>' <?php if($choose_currency== $key) echo "selected"; ?>><?php echo $cur_value["name"].' ('.$cur_value["value"].')' ?></option>
    <?php } ?>
    </select>
    <br/>

    <strong>7)</strong>
    <?php echo gettext("Card type");?> :
    <select NAME="choose_typepaid" size="1" class="form_input_select" >
        <option value='0' <?php if($choose_typepaid== 0 || empty($choose_typepaid)) echo "selected"; ?>><?php echo gettext("PREPAID CARD");?></option>
        <option value='1' <?php if($choose_typepaid== 1) echo "selected"; ?>><?php echo gettext("POSTPAY CARD");?></option>
       </select>
    <br/>

    <strong>8)</strong>
    <?php echo gettext("Credit Limit of postpay");?> : <input class="form_input_text" value="<?php if(is_numeric($creditlimit) && $creditlimit>0) echo $creditlimit; else echo 0;?>" name="creditlimit" size="10" maxlength="16" >
    <br/>

    <strong>9)</strong>
       <?php echo gettext("Enable expire");?>&nbsp;:
    <select name="enableexpire" class="form_input_select" >
        <option value="0" <?php if($enableexpire== 0 || empty($enableexpire)) echo "selected"; ?>> <?php echo gettext("NO EXPIRATION");?> </option>
        <option value="1" <?php if($enableexpire== 1) echo "selected";?> > <?php echo gettext("EXPIRE DATE");?> </option>
        <option value="2" <?php if($enableexpire== 2) echo "selected";?> > <?php echo gettext("EXPIRE DAYS SINCE FIRST USE");?> </option>
        <option value="3" <?php if($enableexpire== 3) echo "selected"; ?> > <?php echo gettext("EXPIRE DAYS SINCE CREATION");?> </option>
    </select>
    <br/>
    <?php
        $begin_date = date("Y");
        $begin_date_plus = date("Y")+10;
        $end_date = date("-m-d H:i:s");
        $comp_date = "value='".$begin_date.$end_date."'";
        $comp_date_plus = "value='".$begin_date_plus.$end_date."'";
    ?>

    <strong>10)</strong>
    <?php echo gettext("Expiry Date");?> : <input class="form_input_text"  name="expirationdate" size="40" maxlength="40" <?php if(!empty($expirationdate)) echo "value='$expirationdate'"; else echo $comp_date_plus;?> > <?php echo gettext("(Format YYYY-MM-DD HH:MM:SS)");?>
    <?php if ($expdate_error) { ?>
        <img style="vertical-align:middle;" src="<?php echo Images_Path;?>/exclamation.png" />
    <?php } ?>
    <br/>

    <strong>11)</strong>
   <?php echo gettext("Expiry days");?> : <input class="form_input_text"  name="expiredays" size="10" maxlength="6" value="<?php if(is_numeric($expiredays) && $expiredays>0) echo $expiredays; else echo 0;?>">
    <?php if ($expday_error) { ?>
        <img style="vertical-align:middle;" src="<?php echo Images_Path;?>/exclamation.png" />
    <?php } ?>
    <br/>

    <strong>12)</strong>
    <?php echo gettext("Run service");?> :
    <?php echo gettext("Yes");?> <input name="runservice" value="1" <?php if($runservice==1) echo "checked='checked'" ?> type="radio"> - <?php echo gettext("No");?> <input name="runservice" value="0" <?php if($runservice==0 || empty($runservice) ) echo "checked='checked'" ?>  type="radio">
    <br/>

    <strong>13)</strong>
   <?php echo gettext("Create SIP/IAX Friends");?>&nbsp;: <?php echo gettext("SIP")?> <input type="checkbox" name="sip" value="1" <?php if($sip==1) echo "checked" ?>> <?php echo gettext("IAX")?> : <input type="checkbox" name="iax" value="1" <?php if($iax==1 ) echo "checked" ?> >
    <br/>

    <strong>14)</strong>
    <?php echo gettext("Tag");?> : <input class="form_input_text"  name="tag" size="40" maxlength="40" <?php if(!empty($tag)) echo "value='$tag'"; ?> >
    <br/>

    <strong>15)</strong>
    <?php echo gettext("Customer group");?> :
    <select NAME="id_group" size="1" class="form_input_select" >
    <option value=''><?php echo gettext("Choose a group");?></option>
    <?php foreach ($list_group as $recordset) { ?>
        <option class=input value='<?php echo $recordset[0]?>' <?php if($recordset[0]==$id_group) echo "selected"; ?> ><?php echo $recordset[1]?></option>
    <?php } ?>
    </select>
    <?php if ($group_error) { ?>
        <img style="vertical-align:middle;" src="<?php echo Images_Path;?>/exclamation.png" />
    <?php } ?>
    <br/>
    <strong>16)</strong>
     <?php echo gettext("Discount");?> :
    <select NAME="discount" size="1" class="form_input_select" >
    <option value='0'><?php echo gettext("NO DISCOUNT");?></option>
    <?php for ($i=1;$i<99;$i++) { ?>
        <option class=input value='<?php echo $i; ?>' <?php if($i==$discount) echo "selected"; ?> ><?php echo $i;?>%</option>
    <?php } ?>
    </select>

    </td>
</tr>
<tr>
    <td align="right">
        <input name="action"  value="generate" type="hidden"/>
        <input class="form_input_button"  value=" GENERATE CUSTOMERS " type="submit"/>
    </td>
</tr>
</form>
</table>
</div>
<br>

<?php
// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

$HD_Form -> create_form($form_action, $list);
$HD_Form->setup_export();

// #### FOOTER SECTION
$smarty->display('footer.tpl');
