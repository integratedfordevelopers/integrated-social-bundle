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

class FacebookType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user_id', 'text', ['attr' => ['readonly' => 'true']]);
        $builder->add('access_token', 'text', ['attr' => ['readonly' => 'true']]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'integrated_facebook_token';
    }
}
