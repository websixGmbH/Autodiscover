<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;

class MozillaHandler extends RequestHandler
{
    public function writeResponse($config, $request)
    {
        header("Content-Type: text/xml");
        $writer = new \XMLWriter();
        $writer->openURI("php://output");

        $this->writeXml($writer, $config, $request);
        $writer->flush();
    }

    protected function parseRequest()
    {
        return (object)array('email' => $_GET['emailaddress']);
    }

    protected function writeXml($writer, $config, $request)
    {
        $writer->startDocument("1.0");
        $writer->setIndent(4);
        $writer->startElement("clientConfig");
        $writer->writeAttribute("version", "1.1");

        $this->writeEmailProvider($writer, $config, $request);

        $writer->endElement();
        $writer->endDocument();
    }

    protected function writeEmailProvider($writer, $config, $request)
    {
        $writer->startElement("emailProvider");
        $writer->writeAttribute("id", $config->id);

        foreach ($config->domains as $domain) {
            $writer->writeElement("domain", $domain);
        }

        $writer->writeElement("displayName", $config->name);
        $writer->writeElement("displayShortName", $config->nameShort);

        foreach ($config->servers as $server) {
            foreach ($server->endpoints as $endpoint) {
                $this->writeServer($writer, $server, $endpoint, $request);
            }
        }

        $writer->endElement();
    }

    protected function writeServer($writer, $server, $endpoint, $request)
    {
        switch ($server->type) {
            case 'imap':
            case 'pop3':
                $this->writeIncomingServer($writer, $server, $endpoint, $request);
                break;
            case 'smtp':
                $this->writeSmtpServer($writer, $server, $endpoint, $request);
                break;
        }
    }

    protected function writeIncomingServer($writer, $server, $endpoint, $request)
    {
        $authentication = $this->mapAuthenticationType($endpoint->authentication);
        if (empty($authentication)) return;

        $writer->startElement("incomingServer");
        $writer->writeAttribute("type", $server->type);
        $writer->writeElement("hostname", $server->hostname);
        $writer->writeElement("port", $endpoint->port);
        $writer->writeElement("socketType", $endpoint->socketType);
        $writer->writeElement("username", $this->getUsername($server, $request));
        $writer->writeElement("authentication", $authentication);
        $writer->endElement();
    }

    protected function writeSmtpServer($writer, $server, $endpoint, $request)
    {
        $authentication = $this->mapAuthenticationType($endpoint->authentication);
        if ($authentication === null) return;

        $writer->startElement("outgoingServer");
        $writer->writeAttribute("type", "smtp");
        $writer->writeElement("hostname", $server->hostname);
        $writer->writeElement("port", $endpoint->port);
        $writer->writeElement("socketType", $endpoint->socketType);

        if ($authentication !== false) {
            $writer->writeElement("username", $this->getUsername($server, $request));
            $writer->writeElement("authentication", $authentication);
        }

        $writer->writeElement("addThisServer", "true");
        $writer->writeElement("useGlobalPreferredServer", "true");
        $writer->endElement();
    }

    protected function mapAuthenticationType($authentication)
    {
        switch ($authentication) {
            case 'password-cleartext':
                return 'password-cleartext';
            case 'CRAM-MD5':
                return 'password-encrypted';
            case 'none':
                return false;
            default:
                return null;
        }
    }
}