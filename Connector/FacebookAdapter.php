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

use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;

use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Bundle\SocialBundle\Social\Facebook\Oauth;

class FacebookAdapter implements AdapterInterface, ConfigurableInterface, ExportableInterface
{
    /**
     * @var FacebookManifest
     */
    private $manifest = null;

    /**
     * @var FacebookConfiguration
     */
    private $configuration = null;

    /**
     * @var FacebookExporter
     */
    private $exporter = null;

    /**
     * @var Oauth
     */
    private $oauth;

    /**
     * FacebookAdapter constructor.
     * @param Oauth $oauth
     */
    public function __construct(Oauth $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * {@inheritdoc}
     */
    public function getManifest()
    {
        if (null === $this->manifest) {
            $this->manifest = new FacebookManifest();
        }

        return $this->manifest;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = new FacebookConfiguration();
        }

        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getExporter(OptionsInterface $options)
    {
        if (null === $this->exporter) {
            $this->exporter = new FacebookExporter($options, $this->oauth);
        }

        return $this->exporter;
    }
}
