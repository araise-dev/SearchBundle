<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests\App\Filter;

use araise\SearchBundle\Filter\AbstractFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFilter extends AbstractFilter
{
    public function process(array $data): array
    {
        return $data;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('arg1');
        $resolver->setRequired('arg2');
    }
}
