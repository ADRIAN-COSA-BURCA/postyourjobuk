<?php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class NewsArticle extends BaseModel {
    
    protected $table = 'news_articles';
    protected $primaryKey = 'article_id';

    public function getRecentNews(int $limit = 20): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY published_at DESC LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function exists(string $link): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE link = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$link]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function saveArticle(array $data): bool {
        if ($this->exists($data['link'])) {
            return false; 
        }
        
        $this->create([
            'title'        => $data['title'],
            'link'         => $data['link'],
            'description'  => $data['description'],
            'source_name'  => $data['source_name'],
            'published_at' => $data['published_at']
        ]);
        
        return true;
    }

    /**
     * Fetch feeds directly in the model to avoid routing/middleware blocks.
     */
    public function fetchAndStoreRssFeed(): int {
    $feeds = [
        'Onrec (UK)'           => ['url' => 'https://www.onrec.com/news-feed', 'filter' => false],
        'Personnel Today (UK)' => ['url' => 'https://www.personneltoday.com/recruitment-retention/feed/', 'filter' => false],
        'Undercover Recruiter' => ['url' => 'https://theundercoverrecruiter.com/feed', 'filter' => false],
        'Recruiting Daily'     => ['url' => 'https://recruitingdaily.com/feed/', 'filter' => false],
    ];

    $totalInserted = 0;
    $allowedKeywords = ['recruit', 'hire', 'job', 'talent', 'hr', 'workforce', 'employment', 'candidate', 'resume', 'hiring'];

    foreach ($feeds as $sourceName => $cfg) {
        $xmlString = $this->fetchWithCurl($cfg['url']);
        if ($xmlString === false) {
            error_log("RSS FETCH FAILED: $sourceName ({$cfg['url']})");
            continue;
        }

        libxml_use_internal_errors(true);
        $rss = simplexml_load_string($xmlString);
        if ($rss === false || !isset($rss->channel->item)) {
            $errors = libxml_get_errors();
            error_log("RSS PARSE FAILED: $sourceName - " . ($errors[0]->message ?? 'unknown'));
            libxml_clear_errors();
            continue;
        }

        $itemCount = 0;
        $savedCount = 0;

        foreach ($rss->channel->item as $item) {
            if ($itemCount >= 8) break;
            $itemCount++;

            $title = trim((string)$item->title);
            $description = strip_tags((string)$item->description);

            if ($cfg['filter']) {
                $textToCheck = strtolower($title . ' ' . $description);
                $isRelevant = false;
                foreach ($allowedKeywords as $kw) {
                    if (str_contains($textToCheck, $kw)) {
                        $isRelevant = true;
                        break;
                    }
                }
                if (!$isRelevant) continue;
            }

            $link = trim((string)$item->link);
            $description = mb_strimwidth($description, 0, 150, '...');
            $pubDate = (string)$item->pubDate;
            $publishedAt = date('Y-m-d H:i:s', strtotime($pubDate ?: 'now'));

            if ($this->saveArticle([
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

    return $totalInserted;
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
}