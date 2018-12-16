<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:29
 */

namespace websixGmbh\Autodiscover;


interface UsernameResolver
{
    public function findUsername($request);
}