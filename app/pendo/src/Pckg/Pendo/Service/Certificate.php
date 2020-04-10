<?php namespace Pckg\Pendo\Service;

class Certificate
{

    const CODE_SUCCESS = 'SUCCESS';

    const CODE_ERROR_PRIVILEGES = 'ERROR_PRIVILEGES';

    const CODE_ERROR_READ = 'ERROR_READ';

    const CODE_ERROR_PASS = 'ERROR_PASS';

    const CODE_ERROR_PARSE = 'ERROR_PARSE';

    const CODE_ERROR_OPENSSL = 'ERROR_OPENSSL';

    const CODE_EMPTY = 'EMPTY';

    const CODE_EMPTY_CERT = 'EMPTY_CERT';

    const CODE_NO_FILE = 'NO_FILE';

    const CODE_NOT_SUPPORTED = 'NOT_SUPPORTED';

    /**
     * @param string $path
     * @param string $file
     * @param null $pass
     */
    public function getInfo(&$props, string $path, string $file, $pass = null)
    {
        if (!file_exists($path . $file)) {
            return static::CODE_NO_FILE;
        }

        try {
            $cert = file_get_contents($path . $file);
        } catch (\Throwable $e) {
            return static::CODE_ERROR_PRIVILEGES;
        }

        if (!$cert) {
            return static::CODE_EMPTY;
        }

        if (strpos($file, '.pem')) {
            try {
                $content = openssl_x509_read($cert);
            } catch (\Throwable $e) {
                return static::CODE_ERROR_READ;
            }
        } elseif (strpos($file, '.p12')) {
            try {
                openssl_pkcs12_read($cert, $certs, $pass);
            } catch (\Throwable $e) {
                return static::CODE_ERROR_OPENSSL;
            }
            if ($e = openssl_error_string()) {
                return static::CODE_ERROR_PASS;
            }
            $content = $certs['cert'] ?? null;
        } else {
            return static::CODE_NOT_SUPPORTED;
        }

        if (!$content) {
            return static::CODE_EMPTY_CERT;
        }

        try {
            $data = openssl_x509_parse($content);
        } catch (\Throwable $e) {
            return static::CODE_ERROR_PARSE;
        }

        $props = [
            'vat' => $data['subject']['OU'][1] ?? null,
            'companyCountry' => $data['subject']['C'] ?? null,
            'company' => $data['subject']['CN'] ?? null,
            'issuerCountry' => $data['issuer']['C'] ?? null,
            'issuer' => $data['issuer']['CN'] ?? null,
            'validTo' => date('Y-m-d', $data['validTo_time_t'] ?? null),
        ];

        return static::CODE_SUCCESS;
    }

}