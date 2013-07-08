<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\CacheWarmer;

/**
 * Aggregates several cache warmers into a single one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CacheWarmerAggregate implements CacheWarmerInterface
{

    protected $warmers;
    protected $optionalsEnabled;

    public function __construct(array $warmers = array())
    {
        $this->setWarmers($warmers);
        $this->optionalsEnabled = false;
    }

    public function enableOptionalWarmers()
    {
        $this->optionalsEnabled = true;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->warmers as $warmer) {
            if (!$this->optionalsEnabled && $warmer->isOptional()) {
                continue;
            }

            $warmer->warmUp($cacheDir);
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return false;
    }

    public function setWarmers(array $warmers)
    {
        $this->warmers = array();
        foreach ($warmers as $warmer) {
            $this->add($warmer);
        }
    }

    public function add(CacheWarmerInterface $warmer)
    {
        $this->warmers[] = $warmer;
    }

}