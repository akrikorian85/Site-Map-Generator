<?php
namespace Module\SiteMapGenerator;

/**
 * Generates a sitemap.xml file with the same json data that is used for the Navigation Sleepy Mustache module
 */
class SiteMapGenerator {
  /**
   * The base URL to prepend to the paths found in the navigation.json file used with the Navigation Sleepy Mustache module
   *
   * @var string
   */
  private $baseUrl;
  /**
   * The page data
   *
   * @var string[]
   */
  private $data = array();
  /**
   * Initializes properties
   *
   * @param string $jsonFilePath the path to the json file
   * @param string $baseUrl
   */
  function __construct($jsonFilePath, $baseUrl) {
    if (empty($baseUrl)) {
      throw new \Exception('A base URL (http://www.example.com) must be supplied to the constructor.');
    }
    $this->baseUrl = $baseUrl;
    $this->data = json_decode(file_get_contents($jsonFilePath), true);
  }
  /**
   * Creates XML string and outputs it to a file
   *
   * @param \XMLWriter $writer
   * @return mixed This function returns the number of bytes that were written to the file, or FALSE on failure.
   */
  public function createXML(\XMLWriter $writer) {
    $writer->openMemory();
    $writer->startDocument('1.0', 'UTF-8');
    $writer->setIndent(true);
    $writer->startElement('urlset');

    foreach($this->data['pages'] as $page) {
      $this->createURLElement($writer, $page);
    }
    $writer->endElement();
    $writer->endDocument();

    $xml = $writer->outputMemory();

    return $this->outputFile($xml);
  }
  /**
   * Creates a Site map XML url element and writes it to memory
   *
   * @param \XMLWriter $writer
   * @param string[] $data The associative array carrying a single page's sitemap data
   * @return void
   */
  private function createURLElement(\XMLWriter $writer, $data) {
    if (empty($data['link'])) {
      throw new \Exception('"link" key must be assigned a value.');
    }

    $writer->startElement('url');

    $this->createElement($writer, 'loc', $this->baseUrl . $data['link']);

    // optional elements
    if (!empty($data['lastmod'])) {
      $this->createElement($writer, 'lastmod', $data['lastmod']);
    }
    if (!empty($data['changefreq'])) {
      $this->createElement($writer, 'changefreq', $data['changefreq']);
    }
    if (!empty($data['priority'])) {
      $this->createElement($writer, 'priority', $data['priority']);
    }
    $writer->endElement();
  }
  /**
   * Creates an XML element and writes in to memory
   *
   * @param \XMLWriter $writer
   * @param string $name the name of the element
   * @param string $value the inner text of the element
   * @return void
   */
  private function createElement(\XMLWriter $writer, $name, $value) {
    $writer->startElement($name);
    $writer->text($value);
    $writer->endElement();
  }
  /**
   * Outputs the XML (or any string) to a file. It's just a wrapper for file_put_contents
   *
   * @param string $input the string to write
   * @param string $filename the file name of the new file
   * @return mixed This function returns the number of bytes that were written to the file, or FALSE on failure.
   */
  private function outputFile($input, $filename = 'sitemap.xml') {
    return file_put_contents($filename, $input);
  }
}