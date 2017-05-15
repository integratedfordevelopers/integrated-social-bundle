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
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

/**
 * Interface OauthInterface
 * @package Integrated\Bundle\SocialBundle\Oauth
 */
interface OauthInterface
{
    /**
     * @param string $connector
     * @param string $admin_url
     * @return string
     */
    public function login($connector, $admin_url);

    /**
     * @param OptionsInterface $options
     * @return mixed
     */
    public function callback(OptionsInterface $options);
}
