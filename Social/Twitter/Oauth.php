<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Social\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Oauth
 * @package Integrated\Bundle\SocialBundle\Social\Twitter
 */
class Oauth
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * Oauth constructor.
     * @param ContainerInterface $container
     * @param SessionInterface $session
     */
    public function __construct(ContainerInterface $container, SessionInterface $session)
    {
        $this->container = $container;
        $this->session = $session;
    }

    /**
     * @param $connector
     * @param $admin_url
     * @return string
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     * @throws \Exception
     */
    public function login($connector, $admin_url)
    {
        $twitteroauth = new TwitterOAuth(
            $this->container->getParameter("consumer_key"),
            $this->container->getParameter("consumer_key_secret")
        );

        // request token of application
        $request_token = $twitteroauth->oauth(
            'oauth/request_token',
            ['oauth_callback' => "http://".$_SERVER["SERVER_NAME"].":8080/".$admin_url.'/connector/config/'.$connector]
        );

        // throw exception if something gone wrong
        if ($twitteroauth->getLastHttpCode() != 200) {
            throw new \Exception('There was a problem performing this request');
        }

        // save token of application to session
        $this->session->set('oauth_token', $request_token['oauth_token']);
        $this->session->set('oauth_token_secret', $request_token['oauth_token_secret']);

        // generate the URL to make request to authorize our application
        $url = $twitteroauth->url(
            'oauth/authenticate',
            ['oauth_token' => $request_token['oauth_token'], 'force_login' => 'true']
        );

        return $url;
    }

    /**
     * @return array|bool
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    public function callback()
    {
        $oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');

        if (empty($oauth_verifier)
            || empty($this->session->get('oauth_token'))
            || empty($this->session->get('oauth_token_secret'))
        ) {
            // something's missing, go and login again
            return false;
        }

        // request user token
        $connection = new TwitterOAuth(
            $this->container->getParameter("consumer_key"),
            $this->container->getParameter("consumer_key_secret"),
            $this->session->get('oauth_token'),
            $this->session->get('oauth_token_secret')
        );

        // request user token
        $token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier]);

        return $token;
    }

    /**
     * @param $token
     * @param $token_secret
     * @param $content
     * @return array|object
     */
    public function tweet($token, $token_secret, $content)
    {
        $twitter = new TwitterOAuth(
            $this->container->getParameter("consumer_key"),
            $this->container->getParameter("consumer_key_secret"),
            $token,
            $token_secret
        );

        $status = $twitter->post("statuses/update", ["status" => $content]);

        return $status;
    }
}
