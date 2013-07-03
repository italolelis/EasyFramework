<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\CacheClearer;

/**
 * ChainCacheClearer.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class ChainCacheClearer implements CacheClearerInterface
{

    /**
     * @var array $clearers
     */
    protected $clearers;

    /**
     * Constructs a new instance of ChainCacheClearer.
     *
     * @param array $clearers The initial clearers.
     */
    public function __construct(array $clearers = array())
    {
        $this->clearers = $clearers;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($cacheDir)
    {
        foreach ($this->clearers as $clearer) {
            $clearer->clear($cacheDir);
        }
    }

    /**
     * Adds a cache clearer to the aggregate.
     *
     * @param CacheClearerInterface $clearer
     */
    public function add(CacheClearerInterface $clearer)
    {
        $this->clearers[] = $clearer;
    }

}