<?php
namespace ExtDirect;

use Nette\Reflection\AnnotationsParser;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;


/**
 * Class Discoverer
 * @package ExtDirect
 */
class Discoverer
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
     * @param $path
     * @return array
     */
    public function loadDir($path)
    {
        $files = [];
        $globPath = $path . '/*.php';
        foreach (glob($globPath) as $filename) {
            $files[] = $filename;
        }

        return $files;
    }

    /**
     * @param \ReflectionClass $reflectedClass
     * @return array
     */
    public function getMethods(\ReflectionClass $reflectedClass)
    {
        $methods = [];

        foreach($reflectedClass->getMethods() as $reflectedMethod) {
            if (false === $reflectedMethod->isPublic()) {
                continue;
            }

            if ($reflectedMethod->isConstructor() || $reflectedMethod->isDestructor() || $reflectedMethod->isAbstract()) {
                continue;
            }

            $methodAnnotations = AnnotationsParser::getAll($reflectedMethod);
            if (false === isset($methodAnnotations['ExtDirect'])) {
                continue;
            }
            $method = [
                'name' => $reflectedMethod->getName(),
                'len' => $reflectedMethod->getNumberOfParameters()
            ];

            if (isset($methodAnnotations['ExtDirect\FormHandler'])) {
                $method['formHandler'] = true;
            }

            $methods[] = $method;
        }
        return $methods;
    }

    /**
     * Scan discoverable paths and get actions
     *
     * @return array
     */
    public function parseClasses()
    {
        $paths = $this->config->getDiscovererPaths();
        $files = $classes = $actions = $classMap = [];
        foreach ($paths as $path) {
            $files = array_merge($files, $this->loadDir($path));
        }

        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            $classes = array_merge($classes, array_keys(AnnotationsParser::parsePhp($fileContent)));

            require_once $file;
            foreach ($classes as $className) {
                $class = new \ReflectionClass($className);
                if (!$class->isInstantiable()) {
                    continue;
                }

                $classAnnotations = AnnotationsParser::getAll($class);
                if (!isset($classAnnotations['ExtDirect'])) {
                    continue;
                }

                $classAlias = null;
                if (isset($classAnnotations['ExtDirect\Alias'])) {
                    if (is_array($classAnnotations['ExtDirect\Alias']) &&
                        is_string($classAnnotations['ExtDirect\Alias'][0])) {
                        $classAlias = $classAnnotations['ExtDirect\Alias'][0];
                        $classMap[$classAlias] = $className;
                    }
                }

                $actions[$classAlias ?: $className] = $this->getMethods($class);
            }
        }

        return [
            'actions' => $actions,
            'classMap' => $classMap
        ];
    }

    /**
     * Get API declaration array
     *
     * @param array $actions
     * @return array
     */
    public function getApi(array $actions)
    {
        $apiCfg = $this->config->getApi()['declaration'];

        $api = [
            'url' => $apiCfg['url'],
            'type' => $apiCfg['type']
        ];

        if (isset($apiCfg['id']) && !is_null($apiCfg['id'])) {
            $api['id'] = $apiCfg['id'];
        }
        if (isset($apiCfg['namespace']) && !is_null($apiCfg['namespace'])) {
            $api['namespace'] = $apiCfg['namespace'];
        }
        if (isset($apiCfg['timeout']) && !is_null($apiCfg['timeout'])) {
            $api['timeout'] = $apiCfg['timeout'];
        }

        $api['actions'] = $actions;

        return $api;
    }

    /**
     *
     *
     * @param ResponseInterface|null $response
     * @param EmitterInterface|null $emitter
     * @param CacheProvider|null $cache
     * @return array
     */
    public function start(ResponseInterface $response = null,
                          EmitterInterface $emitter = null,
                          CacheProvider $cache = null)
    {
        $cacheDir = $this->config->getCacheDirectory();
        $cacheKey = $this->config->getApiProperty('id');
        $cacheLifetime = $this->config->getCacheLifetime();

        $response = $response ?: new Response();
        $emitter  = $emitter ?: new SapiEmitter();
        $cache  = $cache ?: new FilesystemCache($cacheDir);

        $parsedData = $this->parseClasses();

        if ($cache->contains($cacheKey)) {
            $cachedData = $cache->fetch($cacheKey);

            $api = $cachedData['api'];
        } else {
            $api = $this->getApi($parsedData['actions']);

            $cache->save($cacheKey, [
                'classMap' => $parsedData['classMap'],
                'api' => $api
            ], $cacheLifetime);
        }

        $body = sprintf('%s = %s;',
            $this->config->getApiDescriptor(),
            json_encode($api, \JSON_UNESCAPED_UNICODE));

        $response->getBody()->write($body);

        $emitter->emit($response->withHeader('Content-Type', 'text/javascript'));
    }
}