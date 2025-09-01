<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ModifyRequestData middleware
 */
class ModifyRequestDataMiddleware implements MiddlewareInterface
{
    /**
     * Process method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getData();

        $data = $this->trimRequestData($data);

        //if "" as opposed to null, current datetime-stamp will be forced instead of null.
        if (isset($data['activation'])) {
            if (empty($data['activation'])) {
                $data['activation'] = null;
            }
        }

        //if "" as opposed to null, current datetime-stamp will be forced instead of null.
        if (isset($data['expiration'])) {
            if (empty($data['expiration'])) {
                $data['expiration'] = null;
            }
        }

        // Set the modified data back to the request
        $request = $request->withParsedBody($data);

        // Pass the request to the next middleware in the queue
        return $handler->handle($request);
    }


    /**
     * Recursively remove whitespace in the request data
     *
     * @param array $requestData
     * @param string $characters
     * @return array
     */
    private function trimRequestData(array $requestData, string $characters = " \t\n\r\0\x0B"): array
    {
        foreach ($requestData as $key => &$value) {
            if (is_array($value)) {
                $value = $this->trimRequestData($value, $characters);
            } elseif (is_string($value)) {
                $value = trim($value, $characters);
            }
        }

        return $requestData;
    }
}
