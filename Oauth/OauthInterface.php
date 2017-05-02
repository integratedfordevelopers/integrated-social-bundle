<?php
/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Oauth;

/**
 * Interface OauthInterface
 * @package Integrated\Bundle\SocialBundle\Oauth
 */
interface OauthInterface
{
    /**
     * @param string                    $connector
     * @param string                    $admin_url
     */
    public function login($connector, $admin_url);

    /**
     * @return mixed
     */
    public function callback();
}
