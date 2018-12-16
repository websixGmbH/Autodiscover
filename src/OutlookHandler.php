<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2018-12-16
 * Time: 12:26
 */

namespace websixGmbh\Autodiscover;



class OutlookHandler extends RequestHandler {
    public function writeResponse($config, $request) {
        header("Content-Type: application/xml");

        $writer = new XMLWriter();
        $writer->openMemory();

        $this->writeXml($writer, $config, $request);

        $response = $writer->outputMemory(true);
        echo $response;
    }

    protected function parseRequest() {
        $postdata = file_get_contents("php://input");

        if (strlen($postdata) > 0) {
            $xml = simplexml_load_string($postdata);
            return (object)array('email' => $xml->Request->EMailAddress);
        }

        return null;
    }

    public function writeXml($writer, $config, $request) {
        $writer->startDocument("1.0", "utf-8");
        $writer->setIndent(4);
        $writer->startElement("Autodiscover");
        $writer->writeAttribute("xmlns", "http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006");
        $writer->startElement("Response");
        $writer->writeAttribute("xmlns", "http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a");

        $writer->startElement("Account");
        $writer->writeElement("AccountType", "email");
        $writer->writeElement("Action", "settings");

        foreach ($config->servers as $server) {
            foreach ($server->endpoints as $endpoint) {
                if ($this->writeProtocol($writer, $server, $endpoint, $request))
                    break;
            }
        }

        $writer->endElement();

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();
    }

    protected function writeProtocol($writer, $server, $endpoint, $request) {
        switch ($endpoint->authentication) {
            case 'password-cleartext':
            case 'SPA':
                break;
            case 'none':
                if ($server->type !== 'smtp') return false;
                break;
            default:
                return false;
        }

        $writer->startElement('Protocol');
        $writer->writeElement('Type', strtoupper($server->type));
        $writer->writeElement('Server', $server->hostname);
        $writer->writeElement('Port', $endpoint->port);
        $writer->writeElement('LoginName', $this->getUsername($server, $request));
        $writer->writeElement('DomainRequired', 'off');
        $writer->writeElement('SPA', $endpoint->authentication === 'SPA' ? 'on' : 'off');

        switch ($endpoint->socketType) {
            case 'plain':
                $writer->writeElement("SSL", "off");
                break;
            case 'SSL':
                $writer->writeElement("SSL", "on");
                $writer->writeElement("Encryption", "SSL");
                break;
            case 'STARTTLS':
                $writer->writeElement("SSL", "on");
                $writer->writeElement("Encryption", "TLS");
                break;
        }

        $writer->writeElement("AuthRequired", $endpoint->authentication !== 'none' ? 'on' : 'off');

        if ($server->type == 'smtp') {
            $writer->writeElement('UsePOPAuth', $server->samePassword ? 'on' : 'off');
            $writer->writeElement('SMTPLast', 'off');
        }

        $writer->endElement();

        return true;
    }

    protected function mapAuthenticationType($authentication) {
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
