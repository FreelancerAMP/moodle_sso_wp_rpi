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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_sso_wp_rpi
 * @category    string
 * @copyright   2022 rpi-virtuell
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['auth_description'] = 'Login über wordpress KONTO_SERVER';
$string['pluginname'] = 'Authentifizierung eines Wordpress Nutzers';
$string['login_attempt_error'] = 'Maximale Anzahl von Login-Versuchen erreicht! Bitte warten Sie 20 Minuten und versuchen Sie es erneut.';
$string['settings_heading'] = 'Einstellungsseite zum Einstellen möglicher Unternehmen, zu denen sich der Benutzer bei der Anmeldung zuordnen kann';
$string['settings_txt_input_name'] = 'Erlaubte Firmennamen';
$string['settings_txt_input_desc'] = 'HINWEIS: Diese Feld erwartet die Kurznamen der erlaubten Firmen, denen sich der Benutzer zuordnen kann. Die Kurznamen sollten nur durch kommata getrennt werden';