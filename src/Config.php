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
     * Get API declaration
     *
     * @return array
     */
    public function getApi()
    {
        return (isset($this->config['api'])) ? $this->config['api'] : [];
    }
}