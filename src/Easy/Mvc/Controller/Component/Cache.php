<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;

/**
 * Cache component
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Cache extends Component implements \Doctrine\Common\Cache\Cache
{

    private $engine = "\\Doctrine\\Common\\Cache\\FilesystemCache";
    private $directory = "tmp/cache";
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
        if ($this->controller->getProjectConfiguration()->isDebug()) {
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