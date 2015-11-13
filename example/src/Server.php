<?php
namespace Util;

/**
 * Class Server
 *
 * @ExtDirect
 * @ExtDirect\Alias UtilServer
 */
class Server
{
    /**
     * @param $format
     * @return bool|string
     * @ExtDirect
     */
    public function date($format)
    {
        return date($format);
    }

    /**
     *
     * @return string
     * @ExtDirect
     */
    public function hostname()
    {
        return gethostname();
    }
}