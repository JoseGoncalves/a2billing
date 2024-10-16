<?php

use A2billing\Admin;
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

$menu_section = 2;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('agentid', 'tariffplan', 'group','task'));

$FG_DEBUG = 0;
$DBHandle  = DbConnect();
$instance_table_agent = new Table("cc_agent ", "id, login, firstname, lastname");
$list_agent = $instance_table_agent  -> get_list ($DBHandle, "", "id");
$disabled = true;

if (!empty($agentid) && is_numeric($agentid)) {

    $instance_table_tariffname = new Table("cc_tariffgroup LEFT JOIN cc_agent_tariffgroup ON cc_tariffgroup.id = cc_agent_tariffgroup.id_tariffgroup", "id, tariffgroupname");
    $FG_TABLE_CLAUSE = "id_agent = ".$agentid;
    $list_tariffname = $instance_table_tariffname  -> get_list ($DBHandle, $FG_TABLE_CLAUSE, "tariffgroupname");
    $instance_table_group = new Table("cc_card_group", "id, name");
    $FG_TABLE_CLAUSE = "id_agent = ".$agentid;
    $list_group = $instance_table_group -> get_list ($DBHandle, $FG_TABLE_CLAUSE, "id");
    $disabled =false;
}

if ($task=="generate" && !empty($agentid) && !empty($tariffplan) && !empty($group)) {
    $code = generate_unique_value('cc_agent_signup',10,'code');
    $table_signup = new Table('cc_agent_signup');
    $fields = "code,id_agent,id_tariffgroup,id_group";
    $values =  "'$code','$agentid', '$tariffplan','$group'";
    $result_insert = $table_signup -> Add_table($DBHandle,$values,$fields);
    if($result_insert)$URL = $A2B->config['signup']['urlcustomerinterface']."signup.php?key=$code";
}

$smarty->display('main.tpl');

?>
<script>
$(function () {
    $('#selectagent').on('change', function () {
        document.form.method = "GET";
        $('form').submit();
    });
    $('#group').on('change', function () {
        $('#generate').prop("disabled", !($('#tariff').val().length && $(this).val().length));
        $('#result').empty();
    });
    $('#tariff').on('change', function () {
        $('#generate').prop("disabled", !($(this).val().length && $('#group').val().length));
        $('#result').empty();
    });
    $("input#generate").on('click', function() {
        var form = this.form;
        if ($("[name=tariffplan]", form).val().length && $("[name=group]", form).val().length && $("[name=agentid]", form).val().length) {
            $("[name=task]", form).val("generate");
            form.submit();
        }
    })
});
</script>

<?php
echo $CC_help_generate_signup;
?>
<center>
        <b><?php echo gettext("Create signup url for a specific agent, customer group and Call Plan.");?>.</b><br/><br/>
    <form name="form" enctype="multipart/form-data" action="A2B_signup_agent.php" method="post">

        <table width="95%" border="0" cellspacing="2" align="center" class="records">

                <tr>
                      <td colspan="2" align=center>
                          <?php echo gettext ( "Select Agent" );?>:

                          <select id="selectagent" NAME="agentid" size="1" class="form_input_select">
                                <option value='' ><?php echo gettext("Choose an Agent ");?></option>
                                <?php
                                     foreach ($list_agent as $recordset) {
                                ?>
                                    <option class=input value='<?php  echo $recordset['id']?>' <?php if ($recordset['id']==$agentid) echo "selected";?>><?php echo $recordset['login']." ( ".$recordset['id']." - ".$recordset['firstname']." ".$recordset['lastname']." )"?></option>
                                <?php 	 }
                                ?>
                           </select>
                          <a href="A2B_entity_agent.php" class="badge bg-primary popup_trigger" aria-label="open a popup to select an item">&gt;</a>

                      </td>
                  </tr>
                  <tr>
                    <td  colspan="2">
                         &nbsp;
                     </td>
                </tr>
                <tr>
                  <td colspan="2" align=center>
                  <?php echo gettext("Choose the Call Plan to use");?> :
                  <select id="tariff" NAME="tariffplan" size="1"  style="width=250" class="form_input_select<?php if($disabled) echo "_disabled" ?>" <?php if($disabled) echo "disabled=true" ?> >
                                <option value=''><?php echo gettext("Choose a Call Plan");?></option>

                                <?php
                                 foreach ($list_tariffname as $recordset) {
                                ?>
                                    <option class=input value='<?php  echo $recordset['id']?>' <?php if ($recordset['id']==$tariffplan) echo "selected";?>><?php echo $recordset[1]?></option>
                                <?php 	 }
                                ?>
                        </select>
                        <br><br>
                   <?php echo gettext("Choose the Customer group to use");?> :
                  <select id="group" NAME="group" size="1"  style="width=250" class="form_input_select<?php if($disabled) echo "_disabled" ?>" <?php if($disabled) echo "disabled=true" ?> >
                              <option value=''><?php echo gettext("Choose a Customer Group");?></option>
                                <?php
                                 foreach ($list_group as $recordset) {
                                ?>
                                    <option class=input value='<?php  echo $recordset['id']?>' <?php if ($recordset[0]==$group) echo "selected";?>><?php echo $recordset[1]?></option>
                                <?php 	 }
                                ?>
                        </select>
                        <br>
                        <br>

                </td>
                </tr>
                <tr>
                    <td  colspan="2">
                         &nbsp;
                     </td>
                </tr>
                <tr>
                 <td  width="50%" align="center">
                        <a class="cssbutton_big"  href="A2B_entity_signup_agent.php">
                            <?php echo gettext("RETURN TO URL KEY LIST"); ?>
                        </a>
                  </td>
                  <td  align="center" width="50%">
                        <input type="hidden" name="task" value="">
                      <input id="generate" type="button" value="<?php echo gettext('ADD URL KEY'); ?>" onFocus=this.select() class="form_input_button_disabled" name="submit1" disabled="true" />
                       <p>
                  </td>
                </tr>

                <tr>
                    <td  colspan="2">
                         &nbsp;
                     </td>
                </tr>

                 <tr>
                  <td colspan="2"  align="left">

                        <div id="result" >

                         <?php if (!empty($URL)) { ?>
                         <span style="font-family: sans-serif" >
                         <b>
                             <a href="<?php echo $URL;?>"> <?php 	echo gettext("URL")."";?> <img src="<?php echo Images_Path."/link.png"?>" border="0" style="vertical-align:bottom;" title="<?php echo gettext("Link to the URL")?>" alt="<?php echo  gettext("Link to the URL")?>"></a>
                             <?php
                             echo " : ".$URL;	echo "</b><br>"; ?>
                           </span>
                        <?php  }  ?>

                        </div>

                  </td>
                </tr>

            </table>
    </form>
</center>

<?php

$smarty->display('footer.tpl');
