<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Extbase\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * The extbase request.
 *
 * This is a decorator: The core PSR-7 request is hand over as constructor
 * argument, this class implements ServerRequestInterface, too.
 * Additionally, the extbase request details are attached as 'extbase'
 * attribute to the PSR-7 request and this class implements extbase RequestInterface.
 * This class has no state except the PSR-7 request, all operations are
 * hand down to the PSR-7 request.
 */
class Request implements ServerRequestInterface, RequestInterface
{
    protected ServerRequestInterface $request;

    /**
     * @todo v12: final public function __construct(ServerRequestInterface $request)
     */
    public function __construct($request = null)
    {
        if (is_string($request) && !empty($request)) {
            // Deprecation layer for old extbase Request __construct(string $controllerClassName = '')
            $controllerClassName = $request;
            /** @var ServerRequestInterface $request */
            $request = $GLOBALS['TYPO3_REQUEST'] ?? new ServerRequest();
            $attribute = new ExtbaseRequestParameters($controllerClassName);
            $request = $request->withAttribute('extbase', $attribute);
        } elseif ($request === null) {
            // Deprecation layer when ServerRequestInterface is not given yet
            /** @var ServerRequestInterface $request */
            // Fallback "new ServerRequest()" currently used in install tool.
            $request = $GLOBALS['TYPO3_REQUEST'] ?? new ServerRequest();
            $attribute = new ExtbaseRequestParameters('');
            $request = $request->withAttribute('extbase', $attribute);
        }
        if (!$request instanceof ServerRequestInterface) {
            throw new \InvalidArgumentException(
                'Request must implement PSR-7 ServerRequestInterface',
                1624452071
            );
        }
        if (!$request->getAttribute('extbase') instanceof ExtbaseRequestParameters) {
            throw new \InvalidArgumentException(
                'Given request must have an attribute "extbase" of type ExtbaseAttribute',
                1624452070
            );
        }
        $this->request = $request;
    }

    /**
     * ExtbaseAttribute attached as attribute 'extbase' to $request carries extbase
     * specific request values. This helper method type hints this attribute.
     */
    protected function getExtbaseAttribute(): ExtbaseRequestParameters
    {
        return $this->request->getAttribute('extbase');
    }

    public function getControllerObjectName(): string
    {
        return $this->getExtbaseAttribute()->getControllerObjectName();
    }

    /**
     * Return an instance with the specified controller object name set.
     */
    public function withControllerObjectName(string $controllerObjectName): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setControllerObjectName($controllerObjectName);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Returns the plugin key.
     */
    public function getPluginName(): string
    {
        return $this->getExtbaseAttribute()->getPluginName();
    }

    /**
     * Return an instance with the specified plugin name set.
     */
    public function withPluginName($pluginName = null): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setPluginName($pluginName);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Returns the extension name of the specified controller.
     */
    public function getControllerExtensionName(): string
    {
        return $this->getExtbaseAttribute()->getControllerExtensionName();
    }

    /**
     * Return an instance with the specified controller extension name set.
     */
    public function withControllerExtensionName(?string $controllerExtensionName = null): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setControllerExtensionName($controllerExtensionName);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Returns the extension key of the specified controller.
     */
    public function getControllerExtensionKey(): string
    {
        return $this->getExtbaseAttribute()->getControllerExtensionKey();
    }

    /**
     * Returns the controller name supposed to handle this request, if one
     * was set already (if not, the name of the default controller is returned)
     */
    public function getControllerName(): string
    {
        return $this->getExtbaseAttribute()->getControllerName();
    }

    /**
     * Return an instance with the specified controller name set.
     */
    public function withControllerName(?string $controllerName = null): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setControllerName($controllerName);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Returns the name of the action the controller is supposed to execute.
     */
    public function getControllerActionName(): string
    {
        return $this->getExtbaseAttribute()->getControllerActionName();
    }

    /**
     * Return an instance with the specified controller action name set.
     * Note that the action name must start with a lower case letter and is case-sensitive.
     */
    public function withControllerActionName(?string $actionName = null): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setControllerActionName($actionName);
        return $this->withAttribute('extbase', $attribute);
    }

    public function getArguments(): array
    {
        return $this->getExtbaseAttribute()->getArguments();
    }

    /**
     * Return an instance with the specified extbase arguments, replacing
     * any arguments which existed before.
     */
    public function withArguments(array $arguments): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setArguments($arguments);
        return $this->withAttribute('extbase', $attribute);
    }

    public function getArgument($argumentName)
    {
        return $this->getExtbaseAttribute()->getArgument($argumentName);
    }

    public function hasArgument($argumentName): bool
    {
        return $this->getExtbaseAttribute()->hasArgument($argumentName);
    }

    /**
     * Return an instance with the specified argument set.
     *
     * @param string $argumentName Name of the argument to set
     * @param mixed $value The new value
     * @return self
     */
    public function withArgument(string $argumentName, $value): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setArgument($argumentName, $value);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Returns the requested representation format, something
     * like "html", "xml", "png", "json" or the like.
     */
    public function getFormat(): string
    {
        return $this->getExtbaseAttribute()->getFormat();
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getFormat().
     */
    public function withFormat(string $format): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setFormat($format);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * Extbase @internal methods, not part of extbase RequestInterface. Should vanish as soon as unused.
     */

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setControllerObjectName($controllerObjectName)
    {
        $this->getExtbaseAttribute()->setControllerObjectName($controllerObjectName);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setPluginName($pluginName = null)
    {
        $this->getExtbaseAttribute()->setPluginName($pluginName);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setControllerExtensionName($controllerExtensionName)
    {
        $this->getExtbaseAttribute()->setControllerExtensionName($controllerExtensionName);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setControllerAliasToClassNameMapping(array $controllerAliasToClassNameMapping)
    {
        // this is only needed as long as forwarded requests are altered and unless there
        // is no new request object created by the request builder.
        $this->getExtbaseAttribute()->setControllerAliasToClassNameMapping($controllerAliasToClassNameMapping);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function withControllerAliasToClassNameMapping(array $controllerAliasToClassNameMapping): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        // this is only needed as long as forwarded requests are altered and unless there
        // is no new request object created by the request builder.
        $attribute->setControllerAliasToClassNameMapping($controllerAliasToClassNameMapping);
        return $this->withAttribute('extbase', $attribute);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setControllerName($controllerName)
    {
        $this->getExtbaseAttribute()->setControllerName($controllerName);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setControllerActionName($actionName)
    {
        $this->getExtbaseAttribute()->setControllerActionName($actionName);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setArgument($argumentName, $value)
    {
        $this->getExtbaseAttribute()->setArgument($argumentName, $value);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setArguments(array $arguments)
    {
        $this->getExtbaseAttribute()->setArguments($arguments);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setFormat($format)
    {
        $this->getExtbaseAttribute()->setFormat($format);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function getOriginalRequest(): ?Request
    {
        return $this->getExtbaseAttribute()->getOriginalRequest();
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setOriginalRequest(Request $originalRequest)
    {
        $this->getExtbaseAttribute()->setOriginalRequest($originalRequest);
    }

    /**
     * Get the request mapping results for the original request.
     *
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function getOriginalRequestMappingResults(): Result
    {
        return $this->getExtbaseAttribute()->getOriginalRequestMappingResults();
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API. Violates immutability.
     */
    public function setOriginalRequestMappingResults(Result $originalRequestMappingResults)
    {
        $this->getExtbaseAttribute()->setOriginalRequestMappingResults($originalRequestMappingResults);
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function getInternalArguments(): array
    {
        return $this->getExtbaseAttribute()->getInternalArguments();
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function getInternalArgument($argumentName)
    {
        return $this->getExtbaseAttribute()->getInternalArgument($argumentName);
    }

    /**
     * Methods implementing ServerRequestInterface
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    public function withCookieParams(array $cookies): self
    {
        $request = $this->request->withCookieParams($cookies);
        return new static($request);
    }

    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    public function withQueryParams(array $query): self
    {
        $request = $this->request->withQueryParams($query);
        return new static($request);
    }

    public function getUploadedFiles(): array
    {
        return $this->getExtbaseAttribute()->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $attribute = clone $this->getExtbaseAttribute();
        $attribute->setUploadedFiles($uploadedFiles);
        return $this->withAttribute('extbase', $attribute);
    }

    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    public function withParsedBody($data): self
    {
        $request = $this->request->withParsedBody($data);
        return new static($request);
    }

    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }

    public function withAttribute($name, $value): self
    {
        $request = $this->request->withAttribute($name, $value);
        return new static($request);
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        $request = $this->request->withoutAttribute($name);
        if ($name === 'extbase') {
            return $request;
        }
        return new static($request);
    }

    /**
     * Methods implementing RequestInterface
     */
    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): self
    {
        $request = $this->request->withRequestTarget($requestTarget);
        return new static($request);
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function withMethod($method): self
    {
        $request = $this->request->withMethod($method);
        return new static($request);
    }

    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $request = $this->request->withUri($uri, $preserveHost);
        return new static($request);
    }

    /**
     * Methods implementing MessageInterface
     */
    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function withProtocolVersion($version): self
    {
        $request = $this->request->withProtocolVersion($version);
        return new static($request);
    }

    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        $request = $this->request->withHeader($name, $value);
        return new static($request);
    }

    public function withAddedHeader($name, $value): self
    {
        $request = $this->request->withAddedHeader($name, $value);
        return new static($request);
    }

    public function withoutHeader($name): self
    {
        $request = $this->request->withoutHeader($name);
        return new static($request);
    }

    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    public function withBody(StreamInterface $body): self
    {
        $request = $this->request->withBody($body);
        return new static($request);
    }
}
