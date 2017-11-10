<?php
/**
 * Lyra URL library. This library can be used to validate, check
 * parse, download, proxy and ... with URLs. It is a simple tool
 * to work with URLs.
 *
 * @author Pouyan Azari <pouyan.azari@uni-wuerzburg.de>
 * @license MIT
 */

 namespace De\Uniwue\RZ\Lyra\URL;

class URL
{

    /**
     * Placeholder for the config
     * @var array
     */
    private $config;

    /**
     * Placeholder for the logger
     * @var Monolog/Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param array     $config The configuration for the URL library
     * @param Logger    $logger The logger object to be used.
     *
     */
    public function __construct($config = array(), $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
    * This can be used to log to the given application. It only works container and logger name are set.
    *
    * @param string $level   The log level for the logging in lowercase
    * @param string $message The message to the logger
    * @param string $context The context of log
    */
    public function log($level, $message, $context = array())
    {
        if ($this->logger !== null) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Parses the given URL
     *
     * @param string $url       The URL that should be parsed
     * @param int   $component  The component that should be used for parsing
     *
     * @return array|string|int|null
     */
    public function parse($url, $component = -1)
    {
        if($this->validate($url) === false){
            
            return null;
        }
        return \parse_url($url, $component);
    }

    /**
     * Encodes the given URL by changing not ASCII character to hexadecimal values. #RFC3986
     *
     * @param string $url The url that should be encoded
     *
     * @return string
     */
    public function encode($url)
    {
        return \urlencode($url);
    }

    /**
     * Decodes the given URL back to UTF-8 characters
     *
     * @param string $url The URL that should be decoded
     *
     * @return string
     */
    public function decode($url)
    {
        return \urldecode($url);
    }

    /**
     * Validates the URL, If not valid boolean false will be returned
     *
     *  @param string $url The URL that should be validated
     *
     * @return bool
     */
    public function validate($url)
    {
        return (bool) \filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Checks the availability of the given URL. Returns an array as result
     *
     * @param string $url               The URL that should be checked
     * @param bool   $useProxy          If the check should use a proxy
     * @param bool   $followRedirect    If the check should follow redirect
     *
     * @return array `["code" => "200", "redirect" => "The last URL to be redirected to", "url" => "The url uses", "proxy" => false, "follow" => false ]`
     */
    public function check($url, $useProxy = false, $followRedirect = false)
    {
        $result = array(
            "code" => "",
            "redirect" => "",
            "url" => $url,
            "proxy" => $useProxy,
            "follow" => $followRedirect
        );
        if ($this->validate($url) === false) {
            $result["code"] = 0;
        }
        $curl = \curl_init();
        if ($useProxy === true) {
            $this->setProxySetting($curl, $this->config);
        }
        if ($followRedirect === true) {
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }
        \curl_setopt($curl, CURLOPT_URL, $url);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        \curl_exec($curl);
        $result["redirect"] = \curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $result["code"] = \curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->log("info", "'". $url. "' is called , with proxy: ". (int)$useProxy . ", followRedirect: ". (int) $followRedirect);
        $this->log("info", "'". $url. "' gives code: " . $result["code"]);

        return $result;
    }

    /**
     * Downloads the given URL using the CURL
     *
     * @param string $url               The url that should be used
     * @param string $output            The output path of the download, if not set the result will be saved in a variable
     * @param bool   $withHeaders       If the headers should be attached to the result
     * @param bool   $useProxy          If the proxy should be used
     * @param bool   $followRedirect    If the download should follow redirect
     * @param array  $headers           The headers that should be used
     * @param string $cookie            The cookie string that should be used
     *
     * @return null|string
     */
    public function download($url, $output = null, $useProxy = false, $followRedirect = false, $headers = array(), $cookie = null)
    {
        if ($this->validate($url) === false) {
            return null;
        }
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_URL, $url);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        \curl_setopt($curl, CURLOPT_ENCODING, "");
        if ($useProxy === true) {
            $this->setProxySetting($curl, $this->config);
        }
        if ($followRedirect === true) {
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }
        if ($output !== null) {
            $fp = \fopen ($output, 'w+');
            if ($fp === false) {
                throw new \Exception("File '$output' can be opened");
            }
            \curl_setopt($curl, CURLOPT_FILE, $fp);
        }
        if ($cookie !== null) {
            \curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        if (sizeof($headers) > 0) {
            \curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $result = \curl_exec($curl);
        if ($output !== null) {
            \fclose($fp);
        }
        \curl_close($curl);

        return $result;
    }

    /**
     * Returns the query headers for the given URL
     *
     * @param string $url The url the headers needed
     * @param array $headers The request headers for the given query
     *
     * @return array
     * 
     * Uses @link https://stackoverflow.com/a/10590242/371488 by c.hill cc-by-sa
     */
    public function getHeaders($url, $useProxy = false, $followRedirect = false, $headers = array())
    {
        $headers = array();
        if ($this->validate($url) === false) {
            return $headers;
        }
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, CURLOPT_HEADER, 1);
        \curl_setopt($curl, CURLOPT_URL, $url);
        if ($cookie !== null) {
            \curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        if (sizeof($headers) > 0) {
            \curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if ($useProxy === true) {
            $this->setProxySetting($curl, $this->config);
        }
        if ($followRedirect === true) {
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }
        $response = \curl_exec($curl);

        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }
        \curl_close($curl);

        return $headers;
    }

    /**
     * Returns the info for the given URL
     * @param string $url               The URL that should be checked
     * @param bool   $useProxy          If the check should use a proxy
     * @param bool   $followRedirect    If the check should follow redirect
     *
     * @return array
     */
    public function getInfo($url, $useProxy = false, $followRedirect = false)
    {
        $result = array(
            "code" => "",
            "redirect" => "",
            "url" => $url,
            "proxy" => $useProxy,
            "follow" => $followRedirect
        );
        if ($this->validate($url) === false) {
            $result["code"] = 0;
        }
        $curl = \curl_init();
        if ($useProxy === true) {
            $this->setProxySetting($curl, $this->config);
        }
        if ($followRedirect === true) {
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        }
        \curl_setopt($curl, CURLOPT_URL, $url);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        \curl_exec($curl);

        return \curl_getinfo($curl);
    }

    /**
     * Sets the proxy setting for the given CURL resource
     *
     * @param resource $curl        The curl resource that should be configured for proxy
     * @param array    $settings    The proxy settings
     *
     * @return resource
     */
    private function setProxySetting($curl, $proxySetting = array())
    {
        $validSettings = $this->checkProxySettings($proxySetting);
        if ($validSettings === false) {
            throw new \Exception("The proxy settings are wrong. Please make sure you set host and port");
        }
        \curl_setopt($curl, CURLOPT_PROXY, $proxySetting["host"]);
        \curl_setopt($curl, CURLOPT_PROXYPORT, $proxySetting["port"]);
        if (isset($proxySetting["auth"])) {
            \curl_setopt($curl, CURLOPT_PROXYAUTH, $proxySetting["auth"]);
        }
        if (isset($proxySetting["type"])) {
            \curl_setopt($curl, CURLOPT_PROXYAUTH, $this->getProxyType($proxySetting["type"]));
        }

        return $curl;
    }

    /**
     * Checks if the proxy settings is valid
     *
     * @param array $proxySetting The proxy setting as array
     *
     * @return bool
     */
    private function checkProxySettings($proxySetting)
    {

        if (isset($proxySetting["host"]) === false || isset($proxySetting["port"]) === false) {
            return false;
        }

        return true;
    }

    /**
    * Returns the proxy type in CURL for the given name
    *
    * @param string $proxyType The type that should be converted to CURL proxy type
    *
    * @return int
    */
    private function getProxyType($proxyType = null)
    {
        switch ($proxyType) {
            case "http":
                return CURLPROXY_HTTP;
            
            case "http1":
                return CURLPROXY_HTTP_1_0;
            
            case "socks4":
                return CURLPROXY_SOCKS4;

            case "socks4a":
                return CURLPROXY_SOCKS4A;

            case "socks5":
                return CURLPROXY_SOCKS5;

            case "socks5h":
                return CURLPROXY_SOCKS5_HOSTNAME;

            default:
                return CURLPROXY_HTTP;
        }
    }
}
