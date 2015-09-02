<?php
require_once("Scraper.class.php");

class Fruit {

    public $title = NULL;
    public $unit_price = NULL;
    public $description = NULL;
    public $size = NULL;
    public $description_html = NULL;

    function __construct($title, $unit_price, $description_url, $scraper=NULL) {
        $this->title = trim($title);
        $this->unit_price = number_format((float)$unit_price,2);
        $this->description_url = $description_url;
        $this->scraper = $scraper===NULL ? new Scraper() : $scraper;
        $this->description_html = $this->scraper->generate_html($description_url);
        $this->description = $this->get_description();
        $this->size = $this->get_filesize_str();
    }

    public function output() {
        // representation of object for JSON response

        return array("title"=>$this->title,
                     "unit_price"=>$this->unit_price,
                     "description"=>$this->description,
                     "size"=>$this->size);
    }

    private function get_description() {
        $description = $this->scraper->get_product_description_from_html($this->description_html);
        return trim($description);
    }

    private function get_filesize_str() {
        $size_in_bytes = $this->scraper->get_filesize($this->description_html);
        return number_format($size_in_bytes/1024,2)."kb";
    }
} 