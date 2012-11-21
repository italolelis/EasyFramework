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

namespace Easy\Mvc\Routing;

use Easy\Event\EventListener;

/**
 * This abstract class represents a filter to be applied to a dispatcher cycle. It acts as as
 * event listener with the ability to alter the request or response as needed before it is handled
 * by a controller or after the response body has already been built.
 */
abstract class DispatcherFilter implements EventListener
{

    /**
     * Default priority for all methods in this filter
     *
     * @var int
     * */
    public $priority = 10;

    /**
     * Returns the list of events this filter listens to.
     * Dispatcher notifies 2 different events `Dispatcher.before` and `Dispatcher.after`.
     * By default this class will attach `preDispatch` and `postDispatch` method respectively.
     *
     * Override this method at will to only listen to the events you are interested in.
     *
     * @return array
     * */
    public function implementedEvents()
    {
        return array(
            'Dispatcher.beforeDispatch' => array('callable' => 'beforeDispatch', 'priority' => $this->priority),
            'Dispatcher.afterDispatch' => array('callable' => 'afterDispatch', 'priority' => $this->priority),
        );
    }

    /**
     * Method called before the controller is instantiated and called to ser a request.
     * If used with default priority, it will be called after the Router has parsed the
     * url and set the routing params into the request object.
     *
     * If a Response object instance is returned, it will be served at the end of the
     * event cycle, not calling any controller as a result. This will also have the effect of
     * not calling the after event in the dispatcher.
     *
     * If false is returned, the event will be stopped and no more listeners will be notified.
     * Alternatively you can call `$event->stopPropagation()` to acheive the same result.
     *
     * @param Event $event container object having the `request`, `response` and `additionalParams`
     * 	keys in the data property.
     * @return Response|boolean
     * */
    public function beforeDispatch($event)
    {
        
    }

    /**
     * Method called after the controller served a request and generated a response.
     * It is posible to alter the response object at this point as it is not sent to the
     * client yet.
     *
     * If false is returned, the event will be stopped and no more listeners will be notified.
     * Alternatively you can call `$event->stopPropagation()` to acheive the same result.
     *
     * @param Event $event container object having the `request` and  `response`
     * 	keys in the data property.
     * @return mixed boolean to stop the event dispatching or null to continue
     * */
    public function afterDispatch($event)
    {
        
    }

}