<?php

use A2billing\Admin;

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

$FG_DEBUG = 0;

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

$smarty->display('main.tpl');
?>

<table width="70%" border="0" align="center" cellpadding="0" cellspacing="5" >
    <TR>
        <TD style="border-bottom: medium dotted #EEEEEE" colspan=2>&nbsp; </TD>
    </TR>
    <?php  for ($i=1;$i<=$A2B->config['webui']['num_musiconhold_class'];$i++) { ?>
    <tr>
        <td class="bgcolor_006" height="31" align="center">
            <img src="<?php echo KICON_PATH; ?>/stock-panel-multimedia.gif"/>
        </td>
        <td class="bgcolor_006" height="31" align="center">
            <a href="CC_upload.php?acc=<?php echo $i?>"><?php echo gettext("CUSTOM THE MUSICONHOLD CLASS");?> : <b>ACC_<?php echo $i?></b></a>
        </td>
    </tr>
    <?php  } ?>
</table>
<br>

<?php

$smarty->display('footer.tpl');
