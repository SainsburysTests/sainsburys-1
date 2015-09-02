<?php
require_once('vendor/autoload.php');
require_once('Scraper.class.php');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CommandLine extends Command {
    /* This class sets up and handles the command line interface with sainsburys.php
       Adds the one command 'run' with optional argument 'prettyprint' */

    protected function configure()
    {
        $this->setName("run")
             ->setDescription("Run Sainsbury's product scraper with the default (fruit) url")
             ->addOption("prettyprint", $shortcut=null, $mode=InputOption::VALUE_NONE, $description="Prettyprint the JSON output", $default=null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $default_url = "http://www.sainsburys.co.uk/webapp/wcs/stores/servlet/CategoryDisplay?listView=true&orderBy=FAVOURITES_FIRST&parent_category_rn=12518&top_category=12518&langId=44&beginIndex=0&pageSize=20&catalogId=10137&searchTerm=&categoryId=185749&listId=&storeId=10151&promotionId=#langId=44&storeId=10151&catalogId=10137&categoryId=185749&parent_category_rn=12518&top_category=12518&pageSize=20&orderBy=FAVOURITES_FIRST&searchTerm=&beginIndex=0&hideFilters=true";

        $scraper = new Scraper($default_url);
        $scraped_products = $scraper->scrape();

        if($input->getOption('prettyprint')) {
            $pretty_print = True;
        } else {
            $pretty_print = False;
        }

        $json = $scraper->construct_json($scraped_products, $pretty=$pretty_print);

        $output->writeln($json);
    }
}