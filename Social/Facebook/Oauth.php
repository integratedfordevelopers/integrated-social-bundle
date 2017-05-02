<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Social\Facebook;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Integrated\Bundle\SocialBundle\Oauth\OauthInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Oauth
 * @package Integrated\Bundle\SocialBundle\Social\Facebook
 */
class Oauth implements OauthInterface
{
    private $app_id;

    private $app_id_secret;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Oauth constructor.
     * @param $app_id
     * @param $app_id_secret
     * @param RequestStack $requestStack
     */
    public function __construct($app_id, $app_id_secret, RequestStack $requestStack)
    {
        $this->app_id = $app_id;
        $this->app_id_secret = $app_id_secret;
        $this->requestStack = $requestStack->getCurrentRequest();
    }

    /**
     * @param $connector
     * @param $admin_url
     * @return string
     */
    public function login($connector, $admin_url)
    {
        $fb = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_id_secret,
            'default_graph_version' => 'v2.2'
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $this->requestStack = $this->requestStack->createFromGlobals();

        $loginUrl = $helper->getLoginUrl(
            $this->requestStack->server->get('REQUEST_SCHEME')
            . "://"
            . $this->requestStack->server->get('HTTP_HOST')
            . "/"
            . $admin_url
            . '/connector/config/'
            . $connector,
            ['publish_actions']
        );

        return $loginUrl;
    }

    /**
     * @return array
     * @throws FacebookSDKException
     * @throws \Exception
     */
    public function callback()
    {
        $fb = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_id_secret,
            'default_graph_version' => 'v2.2',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            // When Graph returns an error
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                $error = "Error: " . $helper->getError() . "\n";
                $error .= "Error Code: " . $helper->getErrorCode() . "\n";
                $error .= "Error Reason: " . $helper->getErrorReason() . "\n";
                $error .= "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                $error = 'Bad request';
            }
            throw new \Exception($error);
        }

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                throw new \Exception("<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n");
            }
        }

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me', $accessToken);
        } catch (FacebookResponseException $e) {
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        $userdata = [];

        $userdata["user_id"] = $response->getDecodedBody()["id"];
        $userdata["access_token"] = $accessToken->getValue();

        return $userdata;
    }

    /**
     * @param $userid
     * @param $access_token
     * @param $link
     * @param $message
     * @return \Facebook\GraphNodes\GraphNode
     * @throws \Exception
     */
    public function post($userid, $access_token, $link, $message)
    {
        $fb = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_id_secret,
            'default_graph_version' => 'v2.2',
        ]);

        $postData = [
            'link' => $link,
            'message' => $message
        ];

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->post('/'. $userid .'/feed', $postData, $access_token);
        } catch (FacebookResponseException $e) {
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }

        $graphNode = $response->getGraphNode();

        return $graphNode;
    }
}
