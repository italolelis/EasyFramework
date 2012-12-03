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

namespace Easy\Network\Controller;

use Easy\Configure\IConfiguration;
use Easy\Network\Request;
use Easy\Network\Response;
use InvalidArgumentException;

interface IControllerResolver
{

    /**
     * Returns the Controller instance associated with a Request.
     * As several resolvers can exist for a single application, a resolver must
     * return false when it is not able to determine the controller.
     * The resolver must only throw an exception when it should be able to load
     * controller but cannot because of some errors made by the developer.
     * @param Request $request
     * @param Response $$response
     * @return mixed|boolean A PHP callable representing the Controller, or false if this resolver is not able to determine the controller
     * @throws InvalidArgumentException If the controller can't be found
     */
    public function getController(Request $request, Response $response, $projectConfigs);
}
