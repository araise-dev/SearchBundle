<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests\App\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;
use araise\SearchBundle\Filter\AbstractFilter;

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
