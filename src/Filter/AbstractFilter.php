<?php

declare(strict_types=1);

namespace araise\SearchBundle\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilter implements FilterInterface
{
    protected array $options = [];

    public function setOptions(array $options): void
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function getPriority(): int
    {
        return $this->options['priority'];
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('priority', 0);
        $resolver->setAllowedTypes('priority', 'int');
    }
}
