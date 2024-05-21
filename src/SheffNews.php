<?php
// Step 1: Fetch the webpage content
$filebase='sheffnews';
$url = "https://sheffnews.com/";
$html = file_get_contents($url);
$sBlogTitle='Sheff News';
$sBlogURL=$url;

// Step 2: Parse the HTML
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
libxml_clear_errors();
$xpath = new DOMXPath($doc);

// Extract blog post data
$items = [];
foreach ($xpath->query('//div[contains(@class, "article")]') as $post) {
    $titleNode = $xpath->query('.//a/h2', $post)->item(0);
    if(!$titleNode)
    continue;
    $linkNode = $xpath->query('.//a', $post)->item(0);
    $dateNode = $xpath->query('.//span[contains(@class, "date")]', $post)->item(0);
    $footerNode = $xpath->query('.//div[contains(@class, "meta")]', $post)->item(0);

    if ($titleNode) {
        $titleNode->parentNode->removeChild($titleNode);
    }
    if ($footerNode) {
        $footerNode->parentNode->removeChild($footerNode);
    }
    $content = '';
    foreach ($post->childNodes as $child) {
        $content .= $doc->saveHTML($child);
    }

    $items[] = [
        'title' => $titleNode->textContent,
        'link' => substr($url,0,-1).$linkNode->getAttribute('href'),
        'date' => $dateNode ? $dateNode->textContent : date('c'), // Fallback to current date if no date found
        'content' => $content,
    ];
}


// Step 3: Generate the RSS feed
$rssContent = '<?xml version="1.0" encoding="UTF-8" ?>';
$rssContent .= '<rss version="2.0"><channel>';
$rssContent .= '<title>'.$sBlogTitle.'</title>';
$rssContent .= '<link>'.$sBlogURL.'</link>';
$rssContent .= '<description>RSS feed for '.$sBlogTitle.'</description>';

foreach ($items as $item) {
    $rssContent .= '<item>';
    $rssContent .= '<title>' . htmlspecialchars($item['title']) . '</title>';
    $rssContent .= '<link>' . htmlspecialchars($item['link']) . '</link>';
    $rssContent .= '<pubDate>' . date(DATE_RSS, strtotime($item['date'])) . '</pubDate>';
    $rssContent .= '<description><![CDATA[' . $item['content'] . ']]></description>';
    $rssContent .= '</item>';
}

$rssContent .= '</channel></rss>';

// Step 4: Check if the content has changed and write to file if it has
$rssHash = md5($rssContent);
$hashFile = '../temp/'.$filebase.'_rss_feed.hash';
$prevHash = file_exists($hashFile) ? file_get_contents($hashFile) : '';

if ($rssHash !== $prevHash) {
    file_put_contents($hashFile, $rssHash);
    $filename = '../public/'.$filebase.'_rss_feed.xml';
    file_put_contents($filename, $rssContent);
    echo "RSS feed updated: $filename";
} else {
    echo "No changes detected.";
}
?>
