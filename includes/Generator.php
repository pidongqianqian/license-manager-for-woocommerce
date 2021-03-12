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
MIICXAIBAAKBgQC8kGa1pSjbSYZVebtTRBLxBz5H4i2p/llLCrEeQhta5kaQu/Rn
vuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t0tyazyZ8JXw+KgXTxldMPEL9
5+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4ehde/zUxo6UvS7UrBQIDAQAB
AoGAb/MXV46XxCFRxNuB8LyAtmLDgi/xRnTAlMHjSACddwkyKem8//8eZtw9fzxz
bWZ/1/doQOuHBGYZU8aDzzj59FZ78dyzNFoF91hbvZKkg+6wGyd/LrGVEB+Xre0J
Nil0GReM2AHDNZUYRv+HYJPIOrB0CRczLQsgFJ8K6aAD6F0CQQDzbpjYdx10qgK1
cP59UHiHjPZYC0loEsk7s+hUmT3QHerAQJMZWC11Qrn2N+ybwwNblDKv+s5qgMQ5
5tNoQ9IfAkEAxkyffU6ythpg/H0Ixe1I2rd0GbF05biIzO/i77Det3n4YsJVlDck
ZkcvY3SK2iRIL4c9yY6hlIhs+K9wXTtGWwJBAO9Dskl48mO7woPR9uD22jDpNSwe
k90OMepTjzSvlhjbfuPN1IdhqvSJTDychRwn1kIJ7LQZgQ8fVz9OCFZ/6qMCQGOb
qaGwHmUK6xzpUbbacnYrIM6nLSkXgOAwv7XXCojvY614ILTK3iXiLBOxPu5Eu13k
eUz9sHyD6vkgZzjtxXECQAkp4Xerf5TGfQXGXhxIX52yH+N2LtujCdkQZjXAsGdm
B2zNzvrlgRmgBrklMTrMYgm1NPcW+bRLGcwgW2PTvNM=
-----END RSA PRIVATE KEY-----
EOD;

        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
ehde/zUxo6UvS7UrBQIDAQAB
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