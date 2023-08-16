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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
**/

-- Update Version
UPDATE cc_version SET version = '3.0.0';

ALTER TABLE cc_call_archive ADD a2b_custom1 VARCHAR(20) DEFAULT NULL, ADD a2b_custom2 VARCHAR(20) DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY calledstation VARCHAR(100) NOT NULL;

DELETE FROM cc_config WHERE config_key IN ('asterisk_version', 'cront_currency_update', 'didx_id', 'didx_pass', 'didx_min_rating', 'didx_ring_to');
