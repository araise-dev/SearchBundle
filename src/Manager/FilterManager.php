<?php

declare(strict_types=1);

namespace araise\SearchBundle\Manager;

use araise\SearchBundle\Exception\FilterChainDefinedException;
use araise\SearchBundle\Filter\FilterInterface;
use araise\SearchBundle\Tokenizer\StandardTokenizer;
use araise\SearchBundle\Tokenizer\TokenizerInterface;

class FilterManager
{
    protected array $filterList = [];

    protected array $tockenizers = [];

    public function addFilter(FilterInterface $filter, string $chain)
    {
        $this->filterList[$chain][$filter->getPriority()][] = $filter;
    }

    public function process(string $data, string $chain)
    {
        $tokens = $this->getTokens($data, $chain);

        if (! isset($this->filterList[$chain])) {
            throw new FilterChainDefinedException($chain);
        }

        ksort($this->filterList[$chain]);
        foreach ($this->filterList[$chain] as $prioList) {
            /** @var FilterInterface $filter */
            foreach ($prioList as $filter) {
                $tokens = $filter->process($tokens);
            }
        }

        return implode(' ', $tokens);
    }

    public function addTokenizer(TokenizerInterface $tokenizer, string $chain)
    {
        $this->tockenizers[$chain][$tokenizer->getPriority()][] = $tokenizer;
    }

    private function getTokens(string $value, string $chain)
    {
        $tokens = [];
        if (! isset($this->tockenizers[$chain])) {
            $this->tockenizers[$chain][0][] = new StandardTokenizer();
        }
        ksort($this->tockenizers[$chain]);
        foreach ($this->tockenizers[$chain] as $prioList) {
            /** @var TokenizerInterface $tokenizer */
            foreach ($prioList as $tokenizer) {
                $tokens = array_merge($tokens, $tokenizer->tokenize($value));
            }
        }

        return $tokens;
    }
}
