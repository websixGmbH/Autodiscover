<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;


class DomainConfiguration
{
    public $domains;
    public $servers = array();
    public $username;
    public $defaultPort;
    public $defaultSslPort;

    public function addServer($type, $hostname, $defaulPort = null, $defaultSslPort = null)
    {
        $server = $this->createServer($type, $hostname, $defaulPort, $defaultSslPort);
        $server->username = $this->username;
        array_push($this->servers, $server);
        return $server;
    }

    private function createServer($type, $hostname, $defaultPort, $defaultSslPort)
    {
        switch ($type) {
            case 'imap':
                return new Server($type, $hostname, !is_null($defaultPort) ? $defaultPort : 143, !is_null($defaultSslPort) ? $defaultSslPort : 993);
            case 'pop3':
                return new Server($type, $hostname, !is_null($defaultPort) ? $defaultPort : 110, !is_null($defaultSslPort) ? $defaultSslPort : 995);
            case 'smtp':
                return new Server($type, $hostname, !is_null($defaultPort) ? $defaultPort : 25, !is_null($defaultSslPort) ? $defaultSslPort : 465);
            default:
                throw new Exception("Unrecognized server type \"$type\"");
        }
    }
}