<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     auth_sso_wp_rpi
 * @category    admin
 * @copyright   2022 rpi-virtuell
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {


        // Introductory explanation.
        $settings->add(new admin_setting_heading('auth_sso_wp_rpi/sso_heading', '', get_string('settings_heading', 'auth_sso_wp_rpi')));


        $settings->add(new admin_setting_configtext('auth_sso_wp_rpi/sso_company_txt_config', get_string('settings_txt_input_name', 'auth_sso_wp_rpi'),
            get_string('settings_txt_input_desc', 'auth_sso_wp_rpi'), 'relilab,ci,rpi', PARAM_RAW, null));

    }
}
