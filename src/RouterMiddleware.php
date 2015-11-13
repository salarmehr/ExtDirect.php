<?php
namespace Mercatus;

use Zend\Stratigility\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ExtDirect\Router;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * RouterMiddleware constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $this->router->route($request);
        return $this->router->getResponse();
    }
}