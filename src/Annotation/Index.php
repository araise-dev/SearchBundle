<?php

declare(strict_types=1);
/**
 * Copyright (c) 2016, whatwedo GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use whatwedo\CoreBundle\Formatter\DefaultFormatter;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
#[\Attribute]
class Index
{

    public function __construct($formatter = null)
    {
        if ($formatter === null) {
            $formatter = DefaultFormatter::class;
        }
        $this->formatter = $formatter;
    }

    /**
     * @var string
     */
    public $formatter;

    /**
     * @var array
     */
    public $formatterOptions = [];

    /**
     * @return string
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param string $formatter
     *
     * @return self
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getFormatterOptions(): array
    {
        return $this->formatterOptions;
    }

    public function setFormatterOptions(array $formatterOptions): void
    {
        $this->formatterOptions = $formatterOptions;
    }
}
