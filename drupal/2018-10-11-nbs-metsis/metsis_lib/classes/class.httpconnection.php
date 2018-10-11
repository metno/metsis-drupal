<?php

//Currently supports POST requests only.

/**
 * Example:
 *  $con = new HttpConnection('normap-dev.met.no','8080');
 *  $res=$con->get('/solr/l1-adcsolr/select',array("q"=>"*:*","rows"=>30,"wt"=>"json","indent"=>"true"));
 */
class HttpConnection {

    private $host;
    private $path;
    private $request;
    private $response = '';
    private $headers;
    private $response_body;
    private $response_headers;

    public function __construct($host, $port) {

        $this->host = $host;

        $this->port = $port;

        $this->headers = new HeaderList(array(), "\r\n");
    }

    public function get($path, $params = array(), $headers = array()) {

        return $this->send($path, 'get', $params, $headers);
    }

    public static function serialize_auth($user, $pass) {

        return base64_encode("$user:$pass");
    }

    public static function serialize_params($params) {

        $query_string = '';

        foreach ($params as $key => $value) {

            $query_string .= '&' . urlencode($key) . '=' . urlencode($value);
        }

        return substr($query_string, 1);
    }

    private function send($path, $method, $params = array(), $headers = array()) {

        $this->headers->add($headers);

        $params = self::serialize_params($params);



        $this->request = strtoupper($method) . " http://{$this->host}:{$this->port}{$path}?{$params} HTTP/1.0\r\n";

        $this->request .= $this->headers->to_s() . "\r\n";

        if ($fp = fsockopen($this->host, $this->port, $errno, $errstr, 15)) {

            if (fwrite($fp, $this->request)) {

                while (!feof($fp)) {

                    $this->response .= fread($fp, 4096);
                }
            }
//              sdpm("This is the request sent to host:");
//              sdpm($this->request);
//              sdpm(urldecode($this->request));

            fclose($fp);
        }
        else {

            throw new Exception("could not establish connection with $host");
        }



        return $this->parse_response();
    }

    private function parse_response() {

        $this->response = str_replace("\r\n", "\n", $this->response);

        list($headers, $body) = explode("\n\n", $this->response, 2);

        $headers = new HeaderList($headers);
        return array('headers' => $headers->to_a(), 'body' => $body, 'code' => $headers->get_response_code());
    }

}

/**
 * should be moved out to its own file
 */
class HeaderList {

    private $headers;
    private $response_code;
    private $linebreak;

    public function __construct($headers = array(), $linebreak = "\n") {

        $this->linebreak = $linebreak;

        $this->headers = $headers;

        if (is_string($this->headers)) {

            $this->parse_headers_string();
        }
    }

    public function to_s() {

        $headers = '';

        foreach ($this->headers as $header => $value) {

            $headers .= "$header: $value{$this->linebreak}";
        }

        return $headers;
    }

    public function to_a() {

        return $this->headers;
    }

    public function __toString() {

        return $this->to_s();
    }

    public function add($headers) {

        $this->headers = array_merge($this->headers, $headers);
    }

    public function get($header) {

        return $this->headers[$header];
    }

    public function get_response_code() {

        return $this->response_code;
    }

    private function parse_headers_string() {

        $replace = ($this->linebreak == "\n" ? "\r\n" : "\n");

        $headers = str_replace($replace, $this->linebreak, trim($this->headers));

        $headers = explode($this->linebreak, $headers);

        $this->headers = array();

        if (preg_match('/^HTTP\/\d\.\d (\d{3})/', $headers[0], $matches)) {

            $this->response_code = $matches[1];

            array_shift($headers);
        }

        foreach ($headers as $string) {

            list($header, $value) = explode(': ', $string, 2);

            $this->headers[$header] = $value;
        }
    }

}
