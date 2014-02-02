<?php

/**
 * Remember me
 *
 * Add persistent login functionality
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Sergey Sidlyarenko
 */

class rememberme extends rcube_plugin
{
    private $rc;
    private $rememberme_value;
    private $session_lifetime;
    private $debug = false;

    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->add_hook('template_object_loginform', array($this,'update_loginform'));
        $this->add_hook('session_destroy', array($this, 'kill_session'));

        if ($this->rc->task == 'login' && $this->rc->action == 'login') {
            $this->add_hook('authenticate', array($this, 'auth'));
            $this->add_hook('login_after', array($this, 'save_prefs'));
        } else if ($this->rc->task != 'logout' && $_SESSION['session_lifetime']) {
            $this->add_hook('startup', array($this, 'update_lifetime'));
        }
    }

    function load_env()
    {
        $this->load_config();
        $this->debug = $this->rc->config->get('rememberme_log', false);
    }

    function load_session_lifetime()
    {
        $this->session_lifetime = $this->rc->config->get('session_storage', 'db') == 'memcache' ?
                                    min(43200, $this->rc->config->get('rememberme_session_lifetime', 43200)) :
                                    $this->rc->config->get('rememberme_session_lifetime', 43200);
    }

    // add rememberme to login form
    function update_loginform($content)
    {
        $this->load_env();

        $this->add_texts('localization/', true);

        $this->include_stylesheet($this->local_skin_path().'/rememberme.css');
        $this->include_script('rememberme.js');

        if ($this->rc->config->get('rememberme_autocheck', false)) {
          $rcmail = rcmail::get_instance();
          $rcmail->output->add_script("$('#rememberme').prop('checked', true);", 'docready');
        }

        if ($this->debug) write_log('rememberme', sprintf("%s login_form", session_id()));

        return $content;
    }

    // after logout set rememberme value to false in user preferences
    function kill_session($args)
    {
        $this->load_env();

        unset($_SESSION['session_lifetime']);

        if ($this->debug) write_log('rememberme', sprintf("%s kill_session", session_id()));

        return $args;
    }

    // if not login task and rememberme set update session_lifetime
    function update_lifetime($args = null)
    {
        $this->load_env();

        if (!empty($_SESSION) && $_SESSION['session_lifetime'])
            $this->session_lifetime = $_SESSION['session_lifetime'];
        else
            $this->load_session_lifetime();

        $this->rc->session->set_lifetime($this->session_lifetime * 60);
        ini_set('session.gc_maxlifetime', $this->session_lifetime * 60 * 2);

        if ($this->rc->config->get('rememberme_usetime', true) && $this->rc->task != 'login') {
            $roundcube_session_name = $this->rc->config->get('session_name', 'roundcube_sessid');
            $roundcube_sessauth_name = 'roundcube_sessauth';
            rcube_utils::setcookie($roundcube_session_name, $_COOKIE[$roundcube_session_name], $this->session_lifetime * 60 + time());
            rcube_utils::setcookie($roundcube_sessauth_name, $_COOKIE[$roundcube_sessauth_name], $this->session_lifetime * 60 + time());
        }

        if ($this->debug) write_log('rememberme', sprintf("%s task:%s, action:%s, lifetime:%d", session_id(), $this->rc->task, $this->rc->action, $this->session_lifetime));

        return $args;
    }

    // after login save rememberme value to user preferences
    function save_prefs($args)
    {
        if ($this->rememberme_value) {
            $this->load_env();
            $this->load_session_lifetime();

            $_SESSION['session_lifetime'] = $this->session_lifetime;

            if ($this->debug) write_log('rememberme', sprintf("%s prefs saved", session_id()));
        }
        return $args;
    }

    // if rememberme set update session_lifetime
    function auth($args)
    {
        $this->load_env();

        if ($this->rememberme_value = (bool) rcube_utils::get_input_value('_rememberme', rcube_utils::INPUT_POST, false))
            $this->update_lifetime();

        if ($this->debug) write_log('rememberme', sprintf("%s auth", session_id()));

        return $args;
    }

}
