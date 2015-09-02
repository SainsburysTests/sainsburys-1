<?php
require_once("classes/Fruit.class.php");

class FruitTest extends PHPUnit_Framework_TestCase {
    private $test_fruit;
    private $stub_scraper;

    public function setUp() {
        $this->stub_scraper = $this->getMockBuilder('Scraper')->getMock();
        $this->stub_scraper->method('get_product_description_from_html')
                           ->willReturn("   One Fresh Pineapple        ");
        $this->stub_scraper->method('get_filesize')
                           ->willReturn("2500");

        $this->test_fruit = new Fruit("Pineapple", 1.3, "http://www.sainsburys.co.uk/products/pineapple", $this->stub_scraper);
    }

    public function test___construct() {
        $this->assertAttributeEquals("1.30", "unit_price", $this->test_fruit);
        $this->assertAttributeEquals("Pineapple", "title", $this->test_fruit);
    }

    public function test_get_description() {
        $this->assertEquals("One Fresh Pineapple", $this->test_fruit->description);
    }

    public function test_get_filesize_str() {
        $this->assertEquals("2.44kb", $this->test_fruit->size);
    }

    public function test_output() {
        $this->test_fruit->title = "Watermelon";
        $this->test_fruit->unit_price = "0.79";
        $this->test_fruit->description = "Large watermelon";
        $this->test_fruit->size = "5.15kb";

        $expected = array("title"=>$this->test_fruit->title,
                          "unit_price"=>$this->test_fruit->unit_price,
                          "description"=>$this->test_fruit->description,
                          "size"=>$this->test_fruit->size);
        $this->assertEquals($expected, $this->test_fruit->output());
    }

} 