<?php

namespace Cvar1984\BruteForce;

class Tools
{
    public function __construct()
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
    }
    public function requestXml(string $url, $fields)
    {
        if (!isset($this->requestHandler)) {
            $this->requestHandler = curl_init();
        }

        $ch = $this->requestHandler;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_HEADER, 'Content-Type: text/xml');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        $this->requestResult = curl_exec($ch);
        return $this;
    }
    public function searchArray($needle, $haystack, $strict = false): bool
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->searchArray($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }
    public function getRequestResult()
    {
        $xmlObject = simplexml_load_string($this->requestResult);
        if(!$xmlObject) {
            return false;
        }
        return json_decode(json_encode($xmlObject), true);
    }
    public function fileToArray($file)
    {
        $content = file_get_contents($file);
        $content = trim($content, "\n\n");
        $arrayContent = explode("\n", $content);
        return $arrayContent;
    }
    public function __destruct()
    {
        if($this->requestHandler instanceof \curl_init) {
            fclose($this->requestHandler);
        }
    }
}
