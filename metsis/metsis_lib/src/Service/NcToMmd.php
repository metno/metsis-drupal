<?php

namespace Drupal\metsis_lib\Service;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Client;

/**
 * Class NcToMmd
 *
 * @package Drupal\metsis_lib\Service
 */
class NcToMmd implements NcToMmdInterface {


  /**
   * Extraction status
   *
   * @var bool
   */
  private $status;


  /**
   * {@inheritDoc}
   */
   public function getMetadata(string $filepath, string $filename, string $output_path ): array
{
        $metadata = [];
        $out_nctommd = null;
        $status_nctommd = null;
        //\Drupal::messenger()->addMessage(t('nc to mmd input file: '.$input_file));
        //\Drupal::messenger()->addMessage(t('nc to mmd output path: '.$output_path));
        exec('/usr/local/bin/nc_to_mmd ' .$filepath . ' ' .$output_path . ' 2>&1', $out_nctommd, $status_nctommd);
        //dpm($out_nctommd);
        if ($status_nctommd === 0) {
            //get xml file content
          $xml_content = file_get_contents($output_path . substr($filename, 0, -3) . '.xml'); // this is a string from gettype
          //get xml object iterator
           $xml = new \SimpleXmlIterator($xml_content); // problem with boolean
           //$xml = simplexml_load_file($xml_content):
           //get xml object iterator with mmd namespaces
           $xml_wns = $xml->children($xml->getNamespaces(true)['mmd']);
	   $metadata[] = $this->depth_mmd("", $xml_wns);

	   $this->status = true;
        } else {
	    $this->status = false;
            $metadata = [];
            \Drupal::logger('nc_to_mmd')->error('<pre><code>' . print_r($out_nctommd, TRUE) . '</code></pre>');
        }
        //$retArr = [$status_nctommd, $metadata, $out_nctommd];
        return $metadata;

  }


/**
 * @{inheritDoc}
 *
 */
public function getStatus() {
	return $this->status;
}
  // extract mmd to the last child
    private function depth_mmd($prefix, $iterator)
    {
        $kv_a = array();
        foreach ($iterator as $k => $v) {
            if ($iterator->hasChildren()) {
                $kv_a = array_merge($kv_a, $this->depth_mmd($prefix . ' ' . $k, $v));
            } else {
                //add mmd keys and values to form_state to be passed to the second page.
                $kv_a[] = array($prefix . ' ' . $k, (string)$v);
            }
        }
        return $kv_a; //this function returns an array of arrys
    }
}
