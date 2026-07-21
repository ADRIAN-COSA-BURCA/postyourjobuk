<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Models\NewsArticle;

class NewsController extends BaseController {

    private const CRON_SECRET = 'rss_fetch_secret_2026';

    private const RELEVANCE_KEYWORDS = [
        'recruit', 'hiring', 'hire', 'talent', 'job market', 'jobs',
        'candidate', 'applicant', 'workforce', 'employer', 'staffing',
        'hr tech', 'ats', 'labour market', 'labor market', 'headhunt'
    ];

    public function fetchFeeds() {
        $token = $_GET['token'] ?? '';
        if ($token !== self::CRON_SECRET) {
            http_response_code(403);
            die("Forbidden: Invalid security token.");
        }

        $feeds = [
            'Onrec (UK)'           => ['url' => 'https://www.onrec.com/news-feed', 'filter' => false],
            'Personnel Today (UK)' => ['url' => 'https://www.personneltoday.com/recruitment-retention/feed/', 'filter' => false],
            'Undercover Recruiter' => ['url' => 'https://theundercoverrecruiter.com/feed', 'filter' => false],
            'Recruiting Daily'     => ['url' => 'https://recruitingdaily.com/feed/', 'filter' => false],
            
        ];

        $newsModel = new NewsArticle();
        $totalInserted = 0;

        foreach ($feeds as $sourceName => $cfg) {
            $xmlString = $this->fetchWithCurl($cfg['url']);

            if ($xmlString === false) {
                error_log("RSS FETCH FAILED (no response): $sourceName ({$cfg['url']})");
                continue;
            }

            libxml_use_internal_errors(true);
            $rss = simplexml_load_string($xmlString);

            if ($rss === false) {
                $errors = libxml_get_errors();
                $firstError = $errors[0]->message ?? 'unknown parse error';
                error_log("RSS PARSE FAILED: $sourceName - " . trim($firstError));
                libxml_clear_errors();
                continue;
            }

            $items = $rss->channel->item ?? [];
            $itemCount = 0;
            $savedCount = 0;

            foreach ($items as $item) {
                if ($itemCount >= 8) break;
                $itemCount++;

                $title = trim((string)$item->title);
                $description = strip_tags((string)$item->description);

                if ($cfg['filter'] && !$this->isRelevant($title, $description)) {
                    continue;
                }

                $link = trim((string)$item->link);
                $description = mb_strimwidth($description, 0, 150, '...');

                $pubDate = (string)$item->pubDate;
                $publishedAt = date('Y-m-d H:i:s', strtotime($pubDate));

                if ($newsModel->saveArticle([
                    'title'        => $title,
                    'link'         => $link,
                    'description'  => $description,
                    'source_name'  => $sourceName,
                    'published_at' => $publishedAt
                ])) {
                    $totalInserted++;
                    $savedCount++;
                }

                if ($savedCount >= 5) break;
            }
        }

        echo "RSS Fetch complete. Inserted $totalInserted new articles.";
    }

    private function fetchWithCurl(string $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; PostYourJobUKBot/1.0; +https://postyourjobuk.example.com/bot)',
            CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/xml;q=0.9, */*;q=0.8'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode >= 400) {
            error_log("cURL fetch issue ($url): HTTP $httpCode $curlErr");
            return false;
        }
        return $body;
    }

    private function isRelevant(string $title, string $description): bool {
        $haystack = mb_strtolower($title . ' ' . $description);
        foreach (self::RELEVANCE_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }
        return false;
    }
}