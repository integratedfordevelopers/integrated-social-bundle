<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class FacebookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user_id', 'text', ['attr' => ['readonly' => 'true']]);
        $builder->add('access_token', 'text', ['attr' => ['readonly' => 'true']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_facebook_tokens';
    }
}
