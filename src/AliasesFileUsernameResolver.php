<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:28
 */

namespace websixGmbh\Autodiscover;

class AliasesFileUsernameResolver implements UsernameResolver
{
    private $fileName;

    function __construct($fileName = "/etc/mail/aliases")
    {
        $this->fileName = $fileName;
    }

    public function findUsername($request)
    {
        static $cachedEmail = null;
        static $cachedUsername = null;

        if ($request->email === $cachedEmail) {
            return $cachedUsername;
        }

        $fp = fopen($this->fileName, 'rb');

        if ($fp === false) {
            throw new Exception("Unable to open aliases file \"$this->fileName\"");
        }

        $username = $this->findLocalPart($fp, $request->localpart);
        if (strpos($username, "@") !== false || strpos($username, ",") !== false) {
            $username = null;
        }

        $cachedEmail = $request->email;
        $cachedUsername = $username;
        return $username;
    }

    protected function findLocalPart($fp, $localPart)
    {
        while (($line = fgets($fp)) !== false) {
            $matches = array();
            if (!preg_match("/^\s*" . preg_quote($localPart) . "\s*:\s*(\S+)\s*$/", $line, $matches)) continue;
            return $matches[1];
        }
    }
}
