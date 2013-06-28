<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\HttpCache;

use Easy\Network\Response;

/**
 * EsiResponseCacheStrategy knows how to compute the Response cache HTTP header
 * based on the different ESI response cache headers.
 *
 * This implementation changes the master response TTL to the smallest TTL received
 * or force validation if one of the ESI has validation cache strategy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EsiResponseCacheStrategy implements EsiResponseCacheStrategyInterface
{

    private $cacheable = true;
    private $ttls = array();
    private $maxAges = array();

    /**
     * Adds a Response.
     *
     * @param Response $response
     */
    public function add(Response $response)
    {
        if ($response->isValidateable()) {
            $this->cacheable = false;
        } else {
            $this->ttls[] = $response->getTtl();
            $this->maxAges[] = $response->getMaxAge();
        }
    }

    /**
     * Updates the Response HTTP headers based on the embedded Responses.
     *
     * @param Response $response
     */
    public function update(Response $response)
    {
        // if we only have one Response, do nothing
        if (1 === count($this->ttls)) {
            return;
        }

        if (!$this->cacheable) {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');

            return;
        }

        if (null !== $maxAge = min($this->maxAges)) {
            $response->setSharedMaxAge($maxAge);
            $response->headers->set('Age', $maxAge - min($this->ttls));
        }
        $response->setMaxAge(0);
    }

}
