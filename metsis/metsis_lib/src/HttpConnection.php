<?php
namespace Drupal\metsis_lib;
use GuzzleHttp\Exception\RequestException;

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
    \Drupal::logger('metsis_lib')->debug("HttpConnection request: " . "http://{$this->host}:{$this->port}{$path}?{$params} HTTP/1.0\r\n");
    //$client = new Client();
    //$client->setDefaultOption('verify', false);
  /*  $this->request = array(
      strtoupper($method),
      "http://{$this->host}:{$this->port}{$path}?{$params} HTTP/1.0\r\n",
      $this->headers->to_s() . "\r\n"
    );
*/
    //$this->request .= $this->headers->to_s() . "\r\n";

    $this->request = strtoupper($method) . " http://{$this->host}:{$this->port}{$path}?{$params} HTTP/1.0\r\n";

    $this->request .= $this->headers->to_s() . "\r\n";

    if ($fp = fsockopen($this->host, $this->port, $errno, $errstr, 15)) {

      if (fwrite($fp, $this->request)) {

        while (!feof($fp)) {

          $this->response .= fread($fp, 4096);
        }
      }
      fclose($fp);
    }
    else {

      throw new RequestException("could not establish connection with $host");
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
