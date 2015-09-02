<?php
require_once("classes/Scraper.class.php");

class ScraperTest extends PHPUnit_Framework_TestCase {
    private $test_scraper;
    private $url;

    public function setUp() {
        $this->url = "http://www.sainsburys.co.uk/webapp/wcs/stores/servlet/CategoryDisplay?listView=true&orderBy=FAVOURITES_FIRST&parent_category_rn=12518&top_category=12518&langId=44&beginIndex=0&pageSize=20&catalogId=10137&searchTerm=&categoryId=185749&listId=&storeId=10151&promotionId=#langId=44&storeId=10151";
        $this->test_scraper = new Scraper($this->url);
    }

    public function test___construct() {
        $this->assertAttributeEquals($this->url, "url", $this->test_scraper);
        $this->assertNotContains("Please enable cookies or JavaScript", $this->test_scraper->html, $message="Returned HTML included a cookie/javascript warning");
        $this->assertContains("<title>Ripe &amp; ready | Sainsbury&#039;s</title>", $this->test_scraper->html);
    }

    public function test_get_filesize() {
        $actual_size = $this->test_scraper->get_filesize("<html>Content</html>");
        $this->assertEquals(20, $actual_size);
    }

    public function test_get_product_description_from_html() {
        $page_html = "<div id='information'>
                          <h3>Description</h3><p>This is the description.</p>\nThis is still the description.<div>Here's some description in a child element</div><h3>Other Information</h3>
                          <p>This is not the description.</p>
                      </div>";

        $actual_description = $this->test_scraper->get_product_description_from_html($page_html);

        $this->assertEquals("This is the description.\nThis is still the description.Here's some description in a child element", $actual_description);
    }

    public function test_scrape() {
        $this->test_scraper->html = "<html>
                                        <div class='productInner'>
                                            <div>
                                                <div class='productInfo'>
                                                    <h3><a href='http://www.google.com'>My First Link</a></h3>
                                                    <div>
                                                        <div>
                                                            <div>
                                                                <p class='pricePerUnit'>1.99</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='productInner'>
                                            <div>
                                                <div class='productInfo'>
                                                    <h3><a href='http://www.hotmail.com'>My Second Link</a></h3>
                                                    <div>
                                                        <div>
                                                            <div>
                                                                <p class='pricePerUnit'>2.25</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                     </html>";
        $result = $this->test_scraper->scrape();
        $this->assertCount(2, $result);
        $this->assertEquals("My First Link", $result[0]->title);
        $this->assertEquals("My Second Link", $result[1]->title);
        $this->assertEquals("http://www.google.com", $result[0]->description_url);
        $this->assertEquals("http://www.hotmail.com", $result[1]->description_url);
        $this->assertEquals("1.99", $result[0]->unit_price);
        $this->assertEquals("2.25", $result[1]->unit_price);
    }

    public function test_construct_json() {
        $test_apple = new Fruit("My Apple", 0.49, "http://www.myshop.shop/apples");
        $test_orange = new Fruit("My Orange", 0.24, "http://www.myshop.shop/oranges");
        $test_banana = new Fruit("My Banana", 0.62, "http://www.myshop.shop/bananas");

        $actual_json = $this->test_scraper->construct_json(array($test_apple, $test_orange, $test_banana));
        $expected_data = array("results"=>array(array("title"=>"My Apple","unit_price"=>"0.49","description"=>"","size"=>"0.00kb"),
                                                array("title"=>"My Orange","unit_price"=>"0.24","description"=>"","size"=>"0.00kb"),
                                                array("title"=>"My Banana","unit_price"=>"0.62","description"=>"","size"=>"0.00kb")),
                               "total"=>"1.35");
        $this->assertEquals(json_encode($expected_data), $actual_json);
    }
}
