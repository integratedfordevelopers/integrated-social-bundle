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

use Integrated\Common\Channel\Connector\ConfigurationInterface;
use Integrated\Bundle\SocialBundle\Form\Type\FacebookType;

class FacebookConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return FacebookType::class;
    }
}