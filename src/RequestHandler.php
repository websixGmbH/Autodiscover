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

        $cachedConfig = $this->readConfig($request);
        $cachedEmail = $request->email;

        return $cachedConfig->getDomainConfig($request->domain);
    }

    protected function readConfig($vars)
    {
        foreach ($vars as $var => $value) {
            $$var = $value;
        }

        $config = new Configuration();
        $cfg = $config->add('autoconf.de.test');
        $cfg->name = 'Example mail services';
        $cfg->nameShort = 'Example';
        $cfg->domains = [ 'autoconf.de.test', 'example.org' ];
        $cfg->username = $_GET['emailaddress'];//new AliasesFileUsernameResolver("/etc/mail/domains/$domain/aliases");

        $cfg->addServer('imap', 'mail.autoconf.de.test')
            ->withEndpoint('STARTTLS')
            ->withEndpoint('SSL');

        $cfg->addServer('smtp', 'smtp.autoconf.de.test')
            ->withEndpoint('STARTTLS')
            ->withEndpoint('SSL');
        return $config;
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