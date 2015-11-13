<?php
namespace ExtDirect;

class Action implements ActionInterface
{
    protected $name;
    protected $class;
    protected $file;
    protected $method;
    protected $arguments = [];
    protected $tid;
    protected $upload;
    protected $formHandler;

    /**
     * Action constructor.
     *
     * @param array $map
     * @param $method
     * @param $data
     * @param $tid
     * @param bool|false $upload
     * @param bool|false $formHandler
     */
    public function __construct(array $map, $method, $arguments, $tid, $upload = false, $formHandler = false)
    {
        $this->name = $map['action'];
        $this->class = $map['class'];
        $this->file = $map['file'];
        $this->method = $method;
        $this->arguments = (array) $arguments;
        $this->tid = $tid;
        $this->upload = $upload;
        $this->formHandler = $formHandler;
    }

    /**
     * @return array
     */
    public function run()
    {
        $response = array(
            'action'  => $this->name,
            'method'  => $this->method,
            'result'  => null,
            'type'    => 'rpc',
            'tid'     => $this->tid
        );

        try {
            $result = $this->callAction();
            $response['result'] = $result;
        }
        catch (\Exception $e) {
            $response['result'] = 'Exception';
        }

        return $response;
    }

    public function callAction()
    {
        require_once $this->file;
        return call_user_func_array(array($this->class, $this->method), $this->arguments);
    }
}