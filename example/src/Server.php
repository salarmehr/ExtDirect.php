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
    public function date( $format )
    {
        return date( $format );
    }
}