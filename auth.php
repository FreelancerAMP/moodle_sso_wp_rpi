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
 * Authentication class for sso_wp_rpi is defined here.
 *
 * @package     auth_sso_wp_rpi
 * @copyright   2022 rpi-virtuell
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

// For further information about authentication plugins please read
// https://docs.moodle.org/dev/Authentication_plugins.
//
// The base class auth_plugin_base is located at /lib/authlib.php.
// Override functions as needed.

/**
 * Authentication class for sso_wp_rpi.
 */
class auth_plugin_sso_wp_rpi extends auth_plugin_base
{

    /**
     * Set the properties of the instance.
     */
    public function __construct()
    {
        $this->authtype = 'sso_wp_rpi';
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password)
    {
        global $CFG, $DB;
        if (!defined('KONTO_SERVER')) {
            if (getenv('KONTO_SERVER'))
                define('KONTO_SERVER', getenv('KONTO_SERVER'));
        }
        $url = KONTO_SERVER . '/wp-json/sso/v1/check_credentials';

        $c = new curl;
        if ($c->post($url, array(
            'username' => $username,
            'password' => $password,
            'origin_url' => home_url()
        ))) {
            $response = $c->getResponse();

            if($response['success']){
                if ($this->user_exists($response['profile']['user_login'])){
                    return true;
                }else{

                }
            }

            // TODO: token zwischenspeichern?



        }

        // Validate the login by using the Moodle user table.
        // Remove if a different authentication method is desired.
        $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));

        // User does not exist.
        if (!$user) {
            return false;
        }

        //return true||false
        return validate_internal_user_password($user, $password);
    }

    /**
     * Returns true if this authentication plugin can change the user's password.
     *
     * @return bool
     */
    public function can_change_password()
    {
        return false;
    }

    /**
     * Returns true if this authentication plugin can edit the users'profile.
     *
     * @return bool
     */
    public function can_edit_profile()
    {
        return true;
    }

    /**
     * Returns true if this authentication plugin is "internal".
     *
     * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal()
    {
        return false;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     *
     * @return bool True means password hash stored in user table, false means flag 'not_cached' stored there instead.
     */
    public function prevent_local_passwords()
    {
        return true;
    }

    /**
     * Indicates if moodle should automatically update internal user
     * records with data from external sources using the information
     * from get_userinfo() method.
     *
     * @return bool True means automatically copy data from ext to user table.
     */
    public function is_synchronised_with_external()
    {
        return false;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool.
     */
    public function can_reset_password()
    {
        return true;
    }

    /**
     * Returns true if plugin allows signup and user creation.
     *
     * @return bool
     */
    public function can_signup()
    {
        return true;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm()
    {
        return false;
    }

    /**
     * Returns whether or not this authentication plugin can be manually set
     * for users, for example, when bulk uploading users.
     *
     * This should be overriden by authentication plugins where setting the
     * authentication method manually is allowed.
     *
     * @return bool
     */
    public function can_be_manually_set()
    {
        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param object $config
     * @param object $err
     * @param array $userfields
     */
    public function config_form($config, $err, $userfields)
    {

        // The form file can be included here.
        // phpcs:ignore moodle.Commenting.InlineComment
        // include('config.html');

    }

    /**
     * Processes and stores configuration data for the plugin.
     *
     * @param stdClass $config Object with submitted configuration settings (without system magic quotes).
     * @return bool True if the configuration was processed successfully.
     */
    public function process_config($config)
    {
        return true;
    }
}
