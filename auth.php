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
global $CFG;
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

    public function user_authenticated_hook(&$user, $username, $password)
    {

        global $DB, $SESSION;

        if ($wp_profile = $SESSION->wp_sso_rpi_profile) {
            //userprofile ergänzen
            if ($user) {
                $user->firstname = $wp_profile['first_name'];
                $user->lastname = $wp_profile['last_name'];
                $user->email = $wp_profile['user_email'];
                $DB->update_record('user', $user);

                //iomad installed?
                if (class_exists('company')) {
                    $company = company::by_userid($user->id);
                    if (!$company) {
                        //assign user to organisation
                        //TODO: get company from $_GET param
                        $comprec = $DB->get_record('company', array('shortname' => 'relilab'));
                        if ($comprec) {
                            $company = new company($comprec->id);
                            $company->assign_user_to_company($user->id);
                        }
                    }

                }


            }
        }

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
        global $CFG, $DB, $SESSION;

        //check failed logins
        if (!$this->check_login($username)) {
            \core\notification::error(get_string('login_attempt_error', 'auth_sso_wp_rpi'));

            return false;
        };

        //define('KONTO_SERVER', 'https://test.rpi-virtuell.de');
        if (!defined('KONTO_SERVER')) {
            if (getenv('KONTO_SERVER'))
                define('KONTO_SERVER', getenv('KONTO_SERVER'));
        }
        $url = KONTO_SERVER . '/wp-json/sso/v1/check_credentials';


        $c = new curl;

        // REST Call via CURL to check user credentials with remote konto server
        $endpoint = KONTO_SERVER . '/wp-json/sso/v1/check_credentials';
        $home_url = 'https://' . $_SERVER["SERVER_NAME"];
        $postdata = '{"username": "' . $username . '","password": "' . $password . '","origin": "' . $home_url . '"}';
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        // Check if response was a success and wether user is already known by this server
        if (isset($responseData['success']) && $responseData['success']) {
            $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
            if (!$user) {
                // SET Session var to access it later when creating new user account
                $SESSION->wp_sso_rpi_profile = $responseData['profile'];
            }
            return true;
        } else {
            $this->add_failed_login($username);
            return false;

        }

    }

    /**
     * check bruce force attac
     */
    protected function check_login($username)
    {
        global $DB;

        $this->delete_failed_logins();

        $result = $DB->get_records('sso_wp_rpi_last_login', array('hash' => md5($username . $_SERVER['REMOTE_ADDR'])));

        if (count($result) > 3) {
            return false;
        }
        return true;
    }

    /**
     * check bruce force attac
     */
    protected function add_failed_login($username)
    {
        global $DB;

        $DB->insert_record('sso_wp_rpi_last_login', array(
            'hash' => md5($username . $_SERVER['REMOTE_ADDR']),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'username' => $username,
            'last_login' => time()
        ), false);

    }

    /**
     * delete laste login
     */
    protected function delete_failed_logins()
    {
        global $DB;
        $sql = "DELETE FROM {sso_wp_rpi_last_login} WHERE last_login < " . (time() - (60 * 20));
        $DB->execute($sql);
    }

    /**
     * Returns true if this authentication plugin can change the user's password.
     *
     * @return bool
     */
    public
    function can_change_password()
    {
        return false;
    }

    /**
     * Returns true if this authentication plugin can edit the users'profile.
     *
     * @return bool
     */
    public
    function can_edit_profile()
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
    public
    function is_internal()
    {
        return false;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     *
     * @return bool True means password hash stored in user table, false means flag 'not_cached' stored there instead.
     */
    public
    function prevent_local_passwords()
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
    public
    function is_synchronised_with_external()
    {
        return false;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool.
     */
    public
    function can_reset_password()
    {
        return true;
    }

    /**
     * Returns true if plugin allows signup and user creation.
     *
     * @return bool
     */
    public
    function can_signup()
    {
        return true;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public
    function can_confirm()
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
    public
    function can_be_manually_set()
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
    public
    function config_form($config, $err, $userfields)
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
    public
    function process_config($config)
    {
        return true;
    }

}
