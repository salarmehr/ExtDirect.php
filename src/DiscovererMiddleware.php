<?php
namespace Mercatus;

use Zend\Stratigility\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ExtDirect\Discoverer;

class DiscovererMiddleware implements MiddlewareInterface
{
    /**
     * @var Discoverer
     */
    protected $discoverer;

    /**
     * DiscovererMiddleware constructor.
     * @param Discoverer $discoverer
     */
    public function __construct(Discoverer $discoverer)
    {
        $this->discoverer = $discoverer;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $this->discoverer->start();
        return $this->discoverer->getResponse();
    }
}