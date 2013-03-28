<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\ControllerAware;

/**
 * Cache component
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Cache extends ControllerAware implements \Doctrine\Common\Cache\Cache
{

    private $engine = "\\Doctrine\\Common\\Cache\\FilesystemCache";
    private $directory = "app/tmp/cache";
    private $extension = ".cache";
    private $lifeTime = null;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    public function getExtension()
    {
        return $this->extension;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getLifeTime()
    {
        if ($this->controller->getKernel()->isDebug()) {
            $this->lifeTime = "10";
        }
        return $this->lifeTime;
    }

    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = $lifeTime;
    }

    public function setEngine($engine)
    {
        $this->cache = $this->engine = $this->loadEngine($engine);
    }

    public function loadEngine($engine)
    {
        if ($engine === "\Doctrine\Common\Cache\FilesystemCache") {
            return new $engine($this->directory, $this->extension);
        }

        return new $engine();
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    public function getStats()
    {
        return $this->cache->getStats();
    }

    public function save($id, $data, $lifeTime = 0)
    {
        if ($lifeTime === 0) {
            $lifeTime = $this->lifeTime;
        }
        return $this->cache->save($id, $data, $lifeTime);
    }

}