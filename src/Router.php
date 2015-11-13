<?php
namespace ExtDirect;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\MessageInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use \Neomerx\Cors\Contracts\Strategies\SettingsStrategyInterface;
use Neomerx\Cors\Strategies\Settings;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Neomerx\Cors\Analyzer;


/**
 * Class Discoverer
 * @package ExtDirect
 */
class Router
{
    /**
     * @var \ExtDirect\Config
     */
    protected $config;

    /**
     * Discoverer constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $paths = $config->getDiscovererPaths();
        if (count($paths) == 0) {
            throw new \DomainException('The Config object has no discoverable paths');
        }

        //@TODO check mandatory properties of API declaration (url, type)

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                throw new \InvalidArgumentException(sprintf('%s is not a directory', $path));
            }

            if (!is_readable($path)) {
                throw new \DomainException(sprintf('%s is not a readable directory', $path));
            }
        }

        $this->config = $config;
    }

    /**
     * @param RequestInterface $request
     * @param array $classMap
     * @return Action[]
     */
    public function getActions(RequestInterface $request, array $classMap)
    {
        $calls = json_decode($request->getBody()->getContents());

        if (!is_array($calls)) {
            $calls = array($calls);
        }

        $actions = [];
        foreach($calls as $call) {
            if (isset($call->type) && $call->type == 'rpc') {
                $actionName = $call->action;
                if (!isset($classMap[$actionName])) {
                    throw new \InvalidArgumentException(sprintf('Unknow action %s', $actionName));
                }
                $map = $classMap[$actionName];

                // @TODO check if method is allowed

                $actions[] = new Action($map, $call->method, $call->data, $call->tid);
            }
        }

        return $actions;
    }

    /**
     * @param SettingsStrategyInterface|null $corsSettings
     * @return SettingsStrategyInterface|Settings
     */
    public function getCorsSettings(SettingsStrategyInterface $corsSettings = null)
    {
        $corsSettings = $corsSettings ?: new Settings();
        $corsCfg = $this->config->getCors();

        foreach($corsCfg as $cfgKey => $cfgValue) {
            $methodName = 'set' . ucfirst($cfgKey);
            if (method_exists($corsSettings, $methodName)) {
                $corsSettings->{$methodName}($cfgValue);
            }
        }

        return $corsSettings;
    }

    /**
     *
     *
     * @param AnalyzerInterface $corsAnalyzer
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $success
     * @return ResponseInterface
     */
    public function analyzeCors(AnalyzerInterface $corsAnalyzer,
                                RequestInterface $request,
                                ResponseInterface $response,
                                &$success)
    {
        $cors = $corsAnalyzer->analyze($request);

        switch ($cors->getRequestType()) {
            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                $success = false;
                return $response->withStatus(403);

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                $corsHeaders = $cors->getResponseHeaders();

                foreach ($corsHeaders as $header => $value) {
                    $response = $response->withHeader($header, $value);
                }
                $success = false;
                return $response->withStatus(200);

            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                $success = true;
                return $response;
            default:
                $corsHeaders = $cors->getResponseHeaders();
                foreach ($corsHeaders as $header => $value) {
                    $response = $response->withHeader($header, $value);
                }
                $success = true;
                return $response->withStatus(200);
        }
    }

    /**
     *
     *
     * @param RequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param EmitterInterface|null $emitter
     * @param CacheProvider|null $cache
     * @return array
     */
    public function route(RequestInterface $request = null,
                          ResponseInterface $response = null,
                          EmitterInterface $emitter = null,
                          CacheProvider $cache = null,
                          SettingsStrategyInterface $corsSettings = null,
                          AnalyzerInterface $corsAnalyzer = null)
    {
        $cacheDir = $this->config->getCacheDirectory();
        $cacheKey = $this->config->getApiProperty('id');
        $cacheLifetime = $this->config->getCacheLifetime();

        $request  = $request ?: ServerRequestFactory::fromGlobals();
        $response = $response ?: new Response();
        $emitter  = $emitter ?: new SapiEmitter();
        $cache    = $cache ?: new FilesystemCache($cacheDir);
        $corsSettings  = $corsSettings ?: $this->getCorsSettings();
        $corsAnalyzer  = $corsAnalyzer ?: Analyzer::instance($corsSettings);

        /** @var ResponseInterface $response */
        $response = $this->analyzeCors($corsAnalyzer, $request, $response, $corsPassed);

        if ($corsPassed) {
            if ($cache->contains($cacheKey)) {
                $cachedData = $cache->fetch($cacheKey);

                //$api = $cachedData['api'];
                $classMap = $cachedData['classMap'];

            } else {
                $discoverer = new Discoverer($this->config);

                $parsedData = $discoverer->parseClasses();
                $api = $discoverer->getApi($parsedData['actions']);
                $classMap = $parsedData['classMap'];

                $cache->save($cacheKey, [
                    'classMap' => $classMap,
                    'api' => $api
                ], $cacheLifetime);
            }

            $actionsResults = [];

            // @TODO parse POST method (formAction)
            // @TODO parse upload

            $actions = $this->getActions($request, $classMap);
            foreach ($actions as $action) {
                $actionsResults[] = $action->run();
            }

            $response->getBody()->write(json_encode($actionsResults, \JSON_UNESCAPED_UNICODE));
        }
        /** @var ResponseInterface $response */
        $emitter->emit($response->withHeader('Content-Type', 'application/json'));
    }
}