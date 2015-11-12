<?php
namespace ExtDirect;

final class Config
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Get paths to be discovered
     *
     * @return array
     */
    public function getDiscovererPaths()
    {
        return (isset($this->config['discoverer']['paths'])) ? $this->config['discoverer']['paths'] : [];
    }

    /**
     * Get API config
     *
     * @return array
     */
    public function getApi()
    {
        return (isset($this->config['api'])) ? $this->config['api'] : [];
    }

    /**
     * Get API config
     *
     * @return array
     */
    public function getApiProperty($prop)
    {
        return (isset($this->config['api'][$prop])) ? $this->config['api'][$prop] : null;
    }

    /**
     * Get API descriptor
     *
     * @return string|null
     */
    public function getApiDescriptor()
    {
        return (isset($this->config['api']['descriptor'])) ? $this->config['api']['descriptor'] : null;
    }

    /**
     *
     * @return string|null
     */
    public function getCacheDirectory()
    {
        return (isset($this->config['cache']['directory'])) ? $this->config['cache']['directory'] : null;
    }

    /**
     * Cache filetime in seconds. Default = 300
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return (isset($this->config['cache']['lifetime'])) ? $this->config['cache']['lifetime'] : 300;
    }
}