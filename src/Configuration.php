<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;


class Configuration
{
    private $items = array();

    public function add($id)
    {
        $result = new DomainConfiguration();
        $result->id = $id;
        array_push($this->items, $result);
        return $result;
    }

    public function getDomainConfig($domain)
    {
        foreach ($this->items as $domainConfig) {
            if (in_array($domain, $domainConfig->domains)) {
                return $domainConfig;
            }
        }

        throw new Exception('No configuration found for requested domain.');
    }
}