<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\CacheWarmer;

use RuntimeException;

/**
 * Abstract cache warmer that knows how to write a file to the cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class CacheWarmer implements CacheWarmerInterface
{

    protected function writeCacheFile($file, $content)
    {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
            @chmod($file, 0666 & ~umask());

            return;
        }

        throw new RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }

}