<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Routing\Generator;

/**
 * UrlGeneratorInterface is the interface that all URL generator classes must implement.
 *
 * The constants in this interface define the different types of resource references that
 * are declared in RFC 3986: http://tools.ietf.org/html/rfc3986
 * We are using the term "URL" instead of "URI" as this is more common in web applications
 * and we do not need to distinguish them as the difference is mostly semantical and
 * less technical. Generating URIs, i.e. representation-independent resource identifiers,
 * is also possible.
 *
 * @author Ítalo Lelis de Vietro <italolelis@gmail.com>
 */
interface IUrlGenerator
{
    /**
     * Generates an absolute URL, e.g. "http://example.com/dir/file".
     */

    const ABSOLUTE_URL = true;

    /**
     * Generates an absolute path, e.g. "/dir/file".
     */
    const ABSOLUTE_PATH = false;

    /**
     * Generates a relative path based on the current request path, e.g. "../parent-file".
     * @see UrlGenerator::getRelativePath()
     */
    const RELATIVE_PATH = 'relative';

    /**
     * Generates a network path, e.g. "//example.com/dir/file".
     * Such reference reuses the current scheme but specifies the host.
     */
    const NETWORK_PATH = 'network';

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $actionName The action Name
     * @param string $controllerName The controller Name
     * $param mixed $params The params to the action
     * @return string An absolute url to the action
     */
    public function create($actionName, $controllerName = null, $params = null, $area = true, $full = true);
}
