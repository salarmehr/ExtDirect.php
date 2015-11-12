<?php
/**
 * Class Server
 *
 * @ExtDirect
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