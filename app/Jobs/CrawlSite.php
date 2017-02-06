<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/14/2016
 * Time: 11:13 AM
 */

namespace App\Jobs;


use App\Contracts\Repository\Crawler\CrawlerContract;
use App\Models\Crawler;
use App\Models\Domain;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Invigor\Crawler\Contracts\CrawlerInterface;
use Invigor\Crawler\Contracts\ParserInterface;

class CrawlSite extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $crawler;

    /**
     * Create a new job instance.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Execute the job.
     * @param CrawlerContract $crawler
     * @return bool
     */
    public function handle(CrawlerContract $crawler)
    {
        Log::info(memory_get_peak_usage());
        $this->crawler->pick();
        if (isset($this->crawler->site) && isset($this->crawler->site->product) && isset($this->crawler->site->product->user)) {
            $user = $this->crawler->site->product->user;
            /*check user subscription status*/
            if ($user->needSubscription && (is_null($this->crawler->site->product->user->subscription) || !$this->crawler->site->product->user->subscription->isValid())) {
                $this->crawler->resetStatus();
                return false;
            }
        }
        Log::info(memory_get_peak_usage());

        $crawler_class = "DefaultCrawler";
        $parser_class = "XPathParser";
        if (!is_null($this->crawler->crawler_class)) {
            $crawler_class = $this->crawler->crawler_class;
        } else {
            /*TODO enable domain in the second phase*/
            $domain = Domain::where("domain_url", $this->crawler->site->domain)->first();
            if (!is_null($domain) && !is_null($domain->crawler_class)) {
                $crawler_class = $domain->crawler_class;
            }
        }
        Log::info(memory_get_peak_usage());

        $crawlerClassFullPath = 'Invigor\Crawler\Repositories\Crawlers\\' . $crawler_class;

        if (!is_null($this->crawler->parser_class)) {
            $parser_class = $this->crawler->parser_class;
        } else {
            /*TODO enable domain in the second phase*/
            $domain = Domain::where("domain_url", $this->crawler->site->domain)->first();
            if (!is_null($domain) && !is_null($domain->parser_class)) {
                $parser_class = $domain->parser_class;
            }
        }
        $parserClassFullPath = 'Invigor\Crawler\Repositories\Parsers\\' . $parser_class;

        Log::info(memory_get_peak_usage());

        $currency_formatter_class = null;
        if (!is_null($this->crawler->currency_formatter_class)) {
            $currency_formatter_class = $this->crawler->currency_formatter_class;
        } else {
            /*TODO enable domain in the second phase*/
            $domain = Domain::where("domain_url", $this->crawler->site->domain)->first();
            if (!is_null($domain) && !is_null($domain->currency_formatter_class)) {
                $currency_formatter_class = $domain->currency_formatter_class;
            }
        }

        Log::info(memory_get_peak_usage());

        $crawlerClass = app()->make($crawlerClassFullPath);
        $parserClass = app()->make($parserClassFullPath);

        $currencyFormatterClass = null;
        if (!is_null($currency_formatter_class)) {
            $currencyFormatterClassFullPath = 'Invigor\Crawler\Repositories\CurrencyFormatters\\' . $currency_formatter_class;
            $currencyFormatterClass = app()->make($currencyFormatterClassFullPath);
        }

        Log::info(memory_get_peak_usage());

        $crawler->crawl($this->crawler, $crawlerClass, $parserClass, $currencyFormatterClass);

        Log::info(memory_get_peak_usage());
        /* unset everything to prevent memory leak */
        unset($user, $crawler_class, $parser_class);
        if (isset($domain)) {
            unset($domain);
        }
        unset($crawlerClassFullPath, $parserClassFullPath, $crawlerClass, $parserClass, $currency_formatter_class, $currencyFormatterClass);
        unset($crawler);
        Log::info(memory_get_peak_usage());
    }
}