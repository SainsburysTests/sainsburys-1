<?php
require_once("Fruit.class.php");

class Scraper {
    public $url;
    public $html;

    function __construct($url=NULL) {
        if($url!==NULL) {
            $this->initialise_url($url);
        }
    }

    public function initialise_url($url) {
        $this->set_url($url);
        $this->set_html($url);
    }

    private function set_url($url) {
        $this->url = $url;
    }

    private function set_html($url) {
        $this->html = $this->generate_html($url);
    }

    public function generate_html($url) {
        // uses curl to getch the html for the given url

        $curl = curl_init($url);
        $curl_transfer_options = array(CURLOPT_USERAGENT=>"Sainsbury's Scraper Bot",
                                       CURLOPT_FOLLOWLOCATION=>true, // Follow http 3xx redirects
                                       CURLOPT_RETURNTRANSFER=>1, // sets curl_exec to return the HTML from the url
                                       CURLOPT_COOKIEJAR=>"cookies.txt", // Location to write cookies to
                                       CURLOPT_COOKIEFILE=>"cookies.txt"); // The location to read cookies from
        curl_setopt_array($curl, $curl_transfer_options);
        $html = curl_exec( $curl );
        curl_close($curl);
        return mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
    }

    public function get_filesize($html) {
        return mb_strlen($html, '8bit');
    }

    private function get_dom_xpath_for_html($html) {
        /*
         *  Returns a domxpath object for the given html
         *  E_WARNING level errors are disabled as the loadHTML function will report
         *  on all malformed HTML formatting errors
         */

        $dom = new DOMDocument();
        error_reporting(E_ERROR | E_PARSE);
        $dom->loadHTML($html);
        error_reporting(E_ALL);
        $xpath_finder = new DomXPath($dom);
        return $xpath_finder;
    }

    private function recursive_build_description($node, $write_text=False, $level=0) {
        /*
         *  Iterates through elements until an h3 title "Description" is found.
         *  All text following this title is collected until the next h3 tag is encountered.
         */

        $description = "";

        if(!$node) {
            return $description;
        }

        foreach($node->childNodes as $child) {
            if(isset($child->tagName) && $child->tagName=="h3" && $child->textContent=="Description") {
                $write_text = True;
            } elseif(isset($child->tagName) && $child->tagName=="h3") {
                $write_text = False;
            } elseif($child->hasChildNodes()) {
                $description .= $this->recursive_build_description($child, $write_text, $level=$level+1);
            } else {
                $description .= $write_text ? $child->textContent : "";
            }
        }

        return $description;
    }

    public function get_product_description_from_html($description_page_html) {
        // Uses the supplied html to find the description using recursive_build_description

        $description_xpath_finder = $this->get_dom_xpath_for_html($description_page_html);
        $description_pieces = $description_xpath_finder->query("//div[@id='information']")->item(0);
        $description = $this->recursive_build_description($description_pieces);

        return $description;
    }

    public function scrape() {
        // Main process for querying the DOM and creating a new Fruit object for each new item found

        $xpath_finder = $this->get_dom_xpath_for_html($this->html);

        $scraped_products = array();

        foreach($xpath_finder->query("//div[contains(@class, 'productInner')]") as $product_element) {
            $title = $xpath_finder->query("./div/div[contains(@class, 'productInfo')]/h3/a/text()", $product_element)->item(0)->textContent;
            $unit_price = $xpath_finder->query("div/div/div/div/div/p[contains(@class, 'pricePerUnit')]/text()", $product_element)->item(0)->textContent;
            $description_url = $xpath_finder->query("div/div[contains(@class, 'productInfo')]/h3/a/@href", $product_element)->item(0)->textContent;

            $scraped_product = new Fruit($title, $unit_price, $description_url);
            array_push($scraped_products, $scraped_product);
        }

        return $scraped_products;
    }

    public function construct_json($products, $pretty=False) {
        /*
         *  Creates a json document from collected Fruits, ready for display
         *  Optional argument '$pretty' can be passed in to prettyprint format the JSON response
         */

        $total = 0;
        foreach($products as $product) {
            $total += $product->unit_price;
        }
        $total = number_format((float)$total, 2);

        $json = array("results"=>array_map(function($product) {return $product->output();}, $products),
                      "total"=>$total);

        #JSON_PRESERVE_ZERO_FRACTION -- 5.6.6+
        $encode_options = $pretty ? JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT : JSON_UNESCAPED_SLASHES;
        return json_encode($json, $encode_options);
    }
}



