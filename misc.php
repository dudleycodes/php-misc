function generate_uid($maxLength = 48)
{
    // Based upon: http://seld.be/notes/unpredictable-hashes-for-humans
    $entropy = null;

    // try ssl first
    if (function_exists('openssl_random_pseudo_bytes')) {
        $entropy = openssl_random_pseudo_bytes(64, $strong);
        // skip ssl since it wasn't using the strong algo
        if($strong !== true) {
            $entropy = '';
        }
    }

    // add some basic mt_rand/uniqid combo
    $entropy .= uniqid(mt_rand(), true);

    // add longer, slower changing entropy
    $entropy = date('L'). $entropy. date('Y'). date('I');

    // try to read from the windows RNG
    if (class_exists('COM')) {
        try {
            $com = new COM('CAPICOM.Utilities.1');
            $entropy .= base64_decode($com->GetRandom(64, 0));
        } catch (Exception $ex) {
        }
    }

    // try to read from the unix RNG
    if (is_readable('/dev/urandom')) {
        $h = fopen('/dev/urandom', 'rb');
        $entropy .= fread($h, 64);
        fclose($h);
    }

    $hash = hash('whirlpool', $entropy);
    if ($maxLength) {
        return substr($hash, 0, $maxLength);
    }
    return $hash;
}

function include_catch($filename, $includeOnce = false)
{
    $filename = str_replace('//', '/', $filename);
    $filename = str_replace('..', '', $filename);

    ob_start();

    if ($includeOnce)
    {
        include_once($filename);
    }
    else
    {
        include($filename);
    }

    $t = ob_get_contents();
    ob_end_clean();
    return (empty($t))? false: $t;
}

function kill_cookie( $name, $path = '/' )
{
    setcookie( $name, 'goodbye', time() - 3600, $path );
}

/**
* Redirects the request agent (browser) to the specified page
*
* @param  $uri The URI (URL) to redirect to
* @param  $method The redirect method (default: location)
* @param  $httpResponseCode The HTTP response code (default: 301 moved permanently or can used 302 move temporarly)
* @return void
*/
function redirect($uri = null, $method='location', $httpResponseCode = 301)
{
    $method = (empty($method))? 'location': strtolower($method);

    if (empty($uri))
    {
        $uri = ($_SERVER['HTTPS'])? 'https': 'http';
        $uri .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    switch(intval($httpResponseCode))
    {
        case 302:
            header('HTTP/1.1 302 Moved Temporarily');
            break;
         default:
             header('HTTP/1.1 301 Moved Permanently');
             break;
    }

    switch($method)
    {
        case 'refresh':
            header( 'Refresh:0;url='. $uri, TRUE );
            break;
        default:
            header( 'Location: '. $uri, TRUE, $httpResponseCode );
    }

    die();
}

function require_catch($filename, $requireOnce = false)
{
    $filename = str_replace('//', '/', $filename);
    $filename = str_replace('..', '', $filename);

    ob_start();

    if ($requireOnce)
    {
        require_once($filename);
    }
    else
    {
        require($filename);
    }

    $t = ob_get_contents();
    ob_end_clean();
    return (empty($t))? false: $t;
}

/**
 * Set HTTP Status Header
 *
 * @access  public
 * @param   int     the status code
 * @param   string
 * @return  void
 */
function set_httpStatus($code, $text = null)
{
    $codes = array(     200 => 'OK',
                        201 => 'Created',
                        202 => 'Accepted',
                        203 => 'Non-Authoritative Information',
                        204 => 'No Content',
                        205 => 'Reset Content',
                        206 => 'Partial Content',

                        300 => 'Multiple Choices',
                        301 => 'Moved Permanently',
                        302 => 'Found',
                        304 => 'Not Modified',
                        305 => 'Use Proxy',
                        307 => 'Temporary Redirect',

                        400 => 'Bad Request',
                        401 => 'Unauthorized',
                        403 => 'Forbidden',
                        404 => 'Not Found',
                        405 => 'Method Not Allowed',
                        406 => 'Not Acceptable',
                        407 => 'Proxy Authentication Required',
                        408 => 'Request Timeout',
                        409 => 'Conflict',
                        410 => 'Gone',
                        411 => 'Length Required',
                        412 => 'Precondition Failed',
                        413 => 'Request Entity Too Large',
                        414 => 'Request-URI Too Long',
                        415 => 'Unsupported Media Type',
                        416 => 'Requested Range Not Satisfiable',
                        417 => 'Expectation Failed',
                        451 => 'Unavailable For Legal Reasons',

                        500 => 'Internal Server Error',
                        501 => 'Not Implemented',
                        502 => 'Bad Gateway',
                        503 => 'Service Unavailable',
                        504 => 'Gateway Timeout',
                        505 => 'HTTP Version Not Supported'     );

    if ($code == null OR !is_numeric($code))
    {
        //todo - error 500 - status code must be numeric!
    }

    if (empty($text))
    {
        if (isset($codes[$code]))
        {
            $text = $codes[$code];
        }
        else
        {
            $text = 'No status text available.  Please check your status code number or supply your own message text.';
        }
    }

    $serverProtocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

    if (substr(php_sapi_name(), 0, 3) == 'cgi')
    {
        header("Status: {$code} {$text}", TRUE);
    }
    elseif ($serverProtocol == 'HTTP/1.1' OR $serverProtocol == 'HTTP/1.0')
    {
        header($serverProtocol." {$code} {$text}", TRUE, $code);
    }
    else
    {
        header("HTTP/1.1 {$code} {$text}", TRUE, $code);
    }
}
