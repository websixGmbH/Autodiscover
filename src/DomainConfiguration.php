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

    public function addServer($type, $hostname)
    {
        $server = $this->createServer($type, $hostname);
        $server->username = $this->username;
        array_push($this->servers, $server);
        return $server;
    }

    private function createServer($type, $hostname)
    {
        switch ($type) {
            case 'imap':
                return new Server($type, $hostname, 143, 993);
            case 'pop3':
                return new Server($type, $hostname, 110, 995);
            case 'smtp':
                return new Server($type, $hostname, 25, 465);
            default:
                throw new Exception("Unrecognized server type \"$type\"");
        }
    }
}