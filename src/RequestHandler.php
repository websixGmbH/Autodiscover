<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;


abstract class RequestHandler
{

    private $config = null;


    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }


    public function handleRequest()
    {
        $request = $this->parseRequest();
        $this->expandRequest($request);
        $config = $this->getDomainConfig($request);
        $this->writeResponse($config, $request);
    }

    protected abstract function parseRequest();

    protected abstract function writeResponse($config, $request);

    protected function expandRequest($request)
    {

        list($localpart, $domain) = explode('@', $request->email);

        if (!isset($request->localpart)) {
            $request->localpart = $localpart;
        }

        if (!isset($request->domain)) {
            $request->domain = strtolower($domain);
        }
    }

    protected function getDomainConfig($request)
    {
        static $cachedEmail = null;
        static $cachedConfig = null;

        if ($cachedEmail === $request->email) {
            return $cachedConfig;
        }

        $cachedConfig = $this->config;
        $cachedEmail = $request->email;

        return $cachedConfig->getDomainConfig($request->domain);
    }


    protected function getUsername($server, $request)
    {
        if (is_string($server->username)) {
            return $server->username;
        }

        if ($server->username instanceof UsernameResolver) {
            $resolver = $server->username;
            return $resolver->findUsername($request);
        }
    }
}