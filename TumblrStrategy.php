<?php
/**
 * Tumblr strategy for Opauth
 * based on http://www.tumblr.com/docs/en/api/v2#auth
 * Author: Benjamin Bjurstrom
 *
 * tumblroauth library from: https://groups.google.com/forum/#!msg/tumblr-api/g6SeIBWvsnE/gnWqT9jFSlEJ
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.TumblrStrategy
 * @license      MIT License
 */


class TumblrStrategy extends OpauthStrategy
{

    /**
     * Compulsory config keys, listed as unassociative arrays
     * eg. array('app_id', 'app_secret');
     */
    public $expects = array('consumer_key', 'consumer_secret');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}int_callback'
    );

    /**
     * Auth request
     */
    public function request()
    {
        require dirname(__FILE__) . '/tumblroauth/tumblroauth.php';
        $callback_url = NULL;

        //instantiate tumblr object
        $tum_oauth = new TumblrOAuth\TumblrOAuth($this->strategy['consumer_key'], $this->strategy['consumer_secret']);

        //generate request token
        $request_token = $tum_oauth->getRequestToken($callback_url);

        //get auth url
        $url = $tum_oauth->getAuthorizeURL($request_token['request_token']);
        $url = $url . $request_token['oauth_token'];

        //load url
        $this->redirect($url);
    }

    /**
     * Internal callback, after Tumblr's OAuth
     */
    public function int_callback()
    {

    }

}