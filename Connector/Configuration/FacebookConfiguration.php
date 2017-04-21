<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Connector\Configuration;

use Integrated\Bundle\SocialBundle\Connector\Exporter\FacebookExporter;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;
use Integrated\Common\Channel\Connector\ConfigurationInterface;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;

use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Bundle\SocialBundle\Social\Facebook\Oauth;
use Integrated\Bundle\SocialBundle\Form\Type\FacebookType;

class FacebookConfiguration implements AdapterInterface, ConfigurableInterface, ExportableInterface, ConfigurationInterface, ManifestInterface
{
    /**
     * @var ManifestInterface
     */
    private $manifest = null;

    /**
     * @var ConfigurableInterface
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
            $this->manifest = $this;
        }

        return $this->manifest;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = $this;
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

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return FacebookType::class;
    }

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
        return 'Facebook Oauth adapter';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.0';
    }
}
