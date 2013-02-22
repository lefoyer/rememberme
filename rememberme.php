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

    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->add_hook('template_object_loginform', array($this,'update_loginform'));
        $this->add_hook('session_destroy', array($this, 'kill_session'));

        if ($this->rc->task == 'login' && $this->rc->action == 'login') {
            $this->load_env();
            $this->add_hook('authenticate', array($this, 'auth'));
            $this->add_hook('login_after', array($this, 'save_prefs'));
        } else if ($this->rc->task != 'logout' && (isset($_COOKIE['rememberme']) || $this->rc->config->get('rememberme', false))) {
            $this->load_env();
            $this->add_hook('startup', array($this, 'update_lifetime'));
        }

    }

    function load_env()
    {
        $this->load_config();
        $this->session_lifetime = $this->rc->config->get('session_storage', 'db') == 'memcache' ?
                                    min(43200, $this->rc->config->get('rememberme_session_lifetime', 43200)) :
                                    $this->rc->config->get('rememberme_session_lifetime', 43200);
    }

    // add rememberme to login form
    function update_loginform($content)
    {
        $this->add_texts('localization/', true);

        $this->include_stylesheet($this->local_skin_path().'/rememberme.css');
        $this->include_script('rememberme.js');

        return $content;
    }

    // after logout set rememberme value to false in user preferences
    function kill_session($args)
    {
        $this->rc->user->save_prefs(array('rememberme' => false));
        rcube_utils::setcookie('rememberme', '-del-', time() - 60);
        return $args;
    }

    // if not login task and rememberme set update session_lifetime
    function update_lifetime($args = null)
    {
        $this->rc->session->set_lifetime($this->session_lifetime * 60);

        return $args;
    }

    // after login save rememberme value to user preferences
    function save_prefs($args)
    {
        $this->rc->user->save_prefs(array('rememberme' => $this->rememberme_value));
        rcube_utils::setcookie('rememberme', '1', 0);
        return $args;
    }

    // if rememberme set update session_lifetime
    function auth($args)
    {
        if ($this->rememberme_value = (bool) rcube_utils::get_input_value('_rememberme', rcube_utils::INPUT_POST, false))
            $this->update_lifetime();

        return $args;
    }

}
