<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;


class Server
{
    public $type;
    public $hostname;
    public $username;
    public $endpoints;
    public $samePassword;

    public function __construct($type, $hostname, $defaultPort, $defaultSslPort)
    {
        $this->type = $type;
        $this->hostname = $hostname;
        $this->defaultPort = $defaultPort;
        $this->defaultSslPort = $defaultSslPort;
        $this->endpoints = array();
        $this->samePassword = true;
    }

    public function withUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function withDifferentPassword()
    {
        $this->samePassword = false;
        return $this;
    }

    public function withEndpoint($socketType, $port = null, $authentication = 'password-cleartext')
    {
        if ($port === null) {
            $port = $socketType === 'SSL' ? $this->defaultSslPort : $this->defaultPort;
        }

        array_push($this->endpoints, (object)array(
            'socketType' => $socketType,
            'port' => $port,
            'authentication' => $authentication));

        return $this;
    }


}