<?php

namespace App\telegram;

use App\Models\MainCategoriesModel;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Spatie\Browsershot\Browsershot;

class Parser extends WebhookHandler
{
    protected Client $client;
    protected Document $document;
    private string $categoriesUrl;


    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
        $this->document = new Document();
        $this->categoriesUrl = 'https://makler.md/ru/categories';
    }

    public function getCategories(): array
    {
        $jar = new CookieJar();
        $response = $this->client->get('https://makler.md', [
            'cookies' => $jar,
        ]);
        $response = $this->client->post($this->categoriesUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://makler.md/',
            ]
        ]);

        $html = $response->getBody()->getContents();
        $this->document->loadHTML($html);

        $result = [];
        $maps = $this->document->find('.map');
        foreach ($maps as $map) {
            $mains = $map->find('.tub a');

            foreach ($mains as $mainsIndex => $main) {
                $res = [];

                $url = str_replace(['/ru/', 'transnistria/'], '', $main->attr('href'));
                $result[$mainsIndex] = [
                    'html' => $main->innerHtml(),
                    'url' => $url,
                ];

                $rubs = $map->find('.clrfix .rub li a');
                foreach ($rubs as $ind => $rub) {
                    if (str_contains($rub->innerHtml(), '→')) {
                        continue;
                    }

                    $urlRub = str_replace(['/ru/', 'transnistria/'], '', $rub->attr('href'));

                    if (str_contains($urlRub, $url)) {

                        $res[$ind] = [
                            'html' => trim($rub->innerHtml()),
                            'url' => $urlRub,
                        ];
                    }
                }
                $result[$mainsIndex]['rubs'] = $res;
            }
        }

        return $result;
    }



}
