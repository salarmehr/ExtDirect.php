<?php
namespace ExtDirect;

use Nette\Reflection\AnnotationsParser;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;


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
     * @param \ReflectionClass $class
     * @return array
     */
    public function getActions(\ReflectionClass $class)
    {
        $actions = [];

        foreach($class->getMethods() as $method) {
            if (false === $method->isPublic()) {
                continue;
            }

            if ($method->isConstructor() || $method->isDestructor() || $method->isAbstract()) {
                continue;
            }

            $methodAnnotations = AnnotationsParser::getAll($method);
            if (false === isset($methodAnnotations['ExtDirect'])) {
                continue;
            }
            $action = [
                'name' => $method->getName(),
                'len' => $method->getNumberOfParameters()
            ];

            if (isset($methodAnnotations['ExtDirect\FormHandler'])) {
                $action['formHandler'] = true;
            }

            $actions[] = $action;
        }
        return $actions;
    }

    /**
     * Scan discoverable paths and get actions
     *
     * @return array
     */
    public function loadPaths()
    {
        $paths = $this->config->getDiscovererPaths();
        $files = $classes = $actions = [];
        foreach ($paths as $path) {
            $files = array_merge($files, $this->loadDir($path));
        }

        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            $classes = array_merge($classes, array_keys(AnnotationsParser::parsePhp($fileContent)));

            require_once $file;
            foreach ($classes as $className) {
                $class = new \ReflectionClass($className);
                if (false === $class->isInstantiable()) {
                    continue;
                }

                $classAnnotations = AnnotationsParser::getAll($class);
                if (false === isset($classAnnotations['ExtDirect'])) {
                    continue;
                }

                $actions[$className] = $this->getActions($class);
            }
        }

        return $actions;
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
     * @return array
     */
    public function start(ResponseInterface $response = null,
                             EmitterInterface $emitter = null)
    {
        $response = $response ?: new Response();
        $emitter  = $emitter ?: new SapiEmitter();

        $actions = $this->loadPaths();
        $api = $this->getApi($actions);

        $body = sprintf('%s = %s;',
            $this->config->getApi()['descriptor'],
            json_encode($api, \JSON_UNESCAPED_UNICODE));

        $response->getBody()->write($body);

        $emitter->emit($response->withHeader('Content-Type', 'text/javascript'));
    }
}