<?php

namespace LicenseManagerForWooCommerce;

use Exception;
use Firebase\JWT\JWT;
use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;

defined('ABSPATH') || exit;

class Generator
{
    /**
     * Generator Constructor.
     */
    public function __construct()
    {
        add_filter('lmfwc_generate_license_keys', array($this, 'generateLicenseKeys'), 10, 3);
    }

    /**
     * Generate a single license string
     *
     * @param string $charset     Character map from which the license will be generated
     * @param int    $chunks      Number of chunks
     * @param int    $chunkLength The length of an individual chunk
     * @param string $separator   Separator used
     * @param string $prefix      Prefix used
     * @param string $suffix      Suffix used
     *
     * @return string
     */
    private function generateLicenseString($charset, $chunks, $chunkLength, $separator, $prefix, $suffix)
    {
        $charsetLength = strlen($charset);
        $licenseString = $prefix;

        // loop through the chunks
        for ($i=0; $i < $chunks; $i++) {
            // add n random characters from $charset to chunk, where n = $chunkLength
            for ($j = 0; $j < $chunkLength; $j++) {
                $licenseString .= $charset[rand(0, $charsetLength - 1)];
            }
            // do not add the separator on the last iteration
            if ($i < $chunks - 1) {
                $licenseString .= $separator;
            }
        }

        $licenseString .= $suffix;

        return $licenseString;
    }

    public function generateLicenseStringUseRSA($validFor, $usersNumber) {
        $payload = array(
            "users_number" => $usersNumber,
            "valid_for"=> $validFor,
            "iss" => "nodefy.me",
            "aud" => "nodefy.me",
            "iat" => time()
        );
        $privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDL4+C6iBh8nT76KAZtqN7pdD0LFVZxu31kGlTTiqiuBV6eb98j
+qlo67TFWCf6AeiajdFhf5iKibCkrp2888AeG1OGuUp68ajDgkhTYBnSrqJPHs7p
KNmkMmsUTAglqF7864UjRtjNG80YWktj8r9YVQVN8MrM6rdEw+xv9mnuCwIDAQAB
AoGAIFiFhXIj2Fkl7Y+9/VWmD4lGSeTXsvFYojHfNTQxMHJfoWkEEHQqvh9urUQg
C3SUDzjid8JDU+8mG+or0hclahjQFMYQtADpHdL99QaYxNqw/u/LGEiVOL5ixFK2
eUmmeRd586W/uT7H48lOiutF+RLJP4tBQVszpjdshTlHV5kCQQDpa12AsMXSfGJO
RZ6MqFWCYgVT0eReIP2kF7Ls3ai9w/HkxGw2iiDCWEMRlno9fQAwoIdQidfYcxIY
JQpa2P+lAkEA3504aNlH9IY1hSvy1huLnWt16+62Dp4fyDmoMuZVONADLE0PaqNZ
oU0tTffB5eQnnf8HE0Uzsx/+GaaiD5LH7wJBAMKP1sFlF4+KVFrP1weBbL0gPTaP
1p3LPABiLKcZYATFZkR8oYKYghPchBMN7diA7/6YYBH2w+7Mg8GSZl4xUG0CQFoq
kSZmBseonkgWkpKXBNLzE9eQp8R5KiOYbCQqEE8aQU1JvV+ogmmyLwRzVLIYL4lb
3kR88P3XdqRtNofFDe0CQB58eFTdM8EMERYeTI5R4PXl1Pr3Sn9w+O1zBGElxZSP
cIiD+QMDtzI+yam1IwQbKts8z39yf+zxOp4nVK9r6+c=
-----END RSA PRIVATE KEY-----
EOD;

        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDL4+C6iBh8nT76KAZtqN7pdD0L
FVZxu31kGlTTiqiuBV6eb98j+qlo67TFWCf6AeiajdFhf5iKibCkrp2888AeG1OG
uUp68ajDgkhTYBnSrqJPHs7pKNmkMmsUTAglqF7864UjRtjNG80YWktj8r9YVQVN
8MrM6rdEw+xv9mnuCwIDAQAB
-----END PUBLIC KEY-----
EOD;
        $jwt = JWT::encode($payload, $privateKey, 'RS256');
        print "Encode:\n" . print_r($jwt, true) . "\n";

        $decoded = JWT::decode($jwt, $publicKey, array('RS256'));

        /*
         NOTE: This will now be an object instead of an associative array. To get
         an associative array, you will need to cast it as such:
        */

        $decoded_array = (array) $decoded;
        print "Decode:\n" . print_r($decoded_array, true) . "\n";
        return $jwt;
    }

    /**
     * Bulk create license keys, if possible for given parameters.
     *
     * @param int $amount Number of license keys to be generated
     * @param GeneratorResourceModel $generator Generator used for the license keys
     * @param array $licenses Number of license keys to be generated
     *
     * @return array
     * @throws Exception
     */
    public function generateLicenseKeys($amount, $generator, $licenses = array())
    {
        // check if it's possible to create as many combinations using the input args
        $uniqueCharacters = count(array_unique(str_split($generator->getCharset())));
        $maxPossibleKeys = pow($uniqueCharacters, $generator->getChunks() * $generator->getChunkLength());

        if ($amount > $maxPossibleKeys) {
            Logger::exception(array($amount, $licenses, $generator));
            throw new Exception('It\'s not possible to generate that many keys with the given parameters, there are not enough combinations. Please review your inputs.');
        }

        // Generate the license strings
        for ($i = 0; $i < $amount; $i++) {
            $licenses[] = $this->generateLicenseStringUseRSA(
                $generator->getExpiresIn(),
                $generator->getUsersNumber()
            );
        }

        // Remove duplicate entries from the array
        $licenses = array_unique($licenses);

        // check if any licenses have been removed
        if (count($licenses) < $amount) {
            // regenerate removed license keys, repeat until there are no duplicates
            while (count($licenses) < $amount) {
                $licenses = $this->generateLicenseKeys(($amount - count($licenses)), $generator, $licenses);
            }
        }

        // Reindex and return the array
        return array_values($licenses);
    }
}