<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Xml;

use DOMDocument;
use DOMXPath;
use MasyaSmv\AtonStatementParser\Exceptions\MissingBisNamespaceException;

final class XPathFactory
{
    public static function make(DOMDocument $dom): DOMXPath
    {
        $xpath = new DOMXPath($dom);

        $root = $dom->documentElement;
        $ns = $root?->namespaceURI;

        if (!is_string($ns) || $ns === '') {
            throw new MissingBisNamespaceException('Cannot detect BIS namespace from XML root element.');
        }

        // Регистрируем префикс BIS на namespace, который реально в файле
        $xpath->registerNamespace('BIS', $ns);

        return $xpath;
    }
}
