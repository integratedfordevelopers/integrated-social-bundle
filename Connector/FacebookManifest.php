<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Connector;

use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;

class FacebookManifest implements ManifestInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Dit is een Facebook OAuth adapter';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.0';
    }
}