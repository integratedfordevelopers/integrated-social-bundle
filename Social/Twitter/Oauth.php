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
use Integrated\Bundle\SocialBundle\Oauth\OauthInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Oauth
 * @package Integrated\Bundle\SocialBundle\Social\Twitter
 */
class Oauth implements OauthInterface
{
    private $consumer_key;

    private $consumer_key_secret;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Oauth constructor.
     * @param $consumer_key
     * @param $consumer_key_secret
     * @param SessionInterface $session
     * @param RequestStack $requestStack
     */
    public function __construct(
        $consumer_key,
        $consumer_key_secret,
        SessionInterface $session,
        RequestStack $requestStack
    ) {
        $this->consumer_key = $consumer_key;
        $this->consumer_key_secret = $consumer_key_secret;
        $this->session = $session;
        $this->requestStack = $requestStack->getCurrentRequest();
    }

    /**
     * @param $connector
     * @param $admin_url
     * @return mixed
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     * @throws \Exception
     */
    public function login($connector, $admin_url)
    {
        $twitteroauth = new TwitterOAuth(
            $this->consumer_key,
            $this->consumer_key_secret
        );

        $this->requestStack = $this->requestStack->createFromGlobals();

        // request token of application
        $request_token = $twitteroauth->oauth(
            'oauth/request_token',
            [
                'oauth_callback' => $this->requestStack->server->get('REQUEST_SCHEME')
                . "://"
                . $this->requestStack->server->get('HTTP_HOST')
                . "/"
                . $admin_url
                . '/connector/config/'
                . $connector
            ]
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
     * @param OptionsInterface $options
     * @param null $connectorName
     * @return bool|string|RedirectResponse
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     * @throws \Exception
     */
    public function callback(OptionsInterface $options, $connectorName = null)
    {
        $oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');

        if (empty($oauth_verifier)
            || empty($this->session->get('oauth_token'))
            || empty($this->session->get('oauth_token_secret'))
        ) {
            dump("false");
            // something's missing, go and login again
            return false;
//            return $this->login($connectorName, "admin");
        }
        dump("true");

        // request user token
        $connection = new TwitterOAuth(
            $this->consumer_key,
            $this->consumer_key_secret,
            $this->session->get('oauth_token'),
            $this->session->get('oauth_token_secret')
        );

        // request user token
        $token = $connection->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier]);

        dump($token);
//        if ($token !== false) {
            return $token;

//            $options->set("token", $token["oauth_token"]);
//            $options->set("token_secret", $token["oauth_token_secret"]);
//            return "token set";
//        } else {
//            return $this->login($connectorName, "admin");
//        }
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
            $this->consumer_key,
            $this->consumer_key_secret,
            $token,
            $token_secret
        );

        $status = $twitter->post("statuses/update", ["status" => $content]);

        return $status;
    }
}
