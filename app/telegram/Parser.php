<?php

namespace App\telegram;

use App\Models\MainCategoriesModel;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DiDom\Document;
use GuzzleHttp\Client;
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


    public function parseMainCategories()
    {
        $response = $this->client->get($this->categoriesUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://makler.md/',
            ]
        ]);
        $html = $response->getBody()->getContents();
        $this->document->loadHTML($html);
        $headers = $this->document->find('.tub a');
        $mainCategories = [];
        foreach ($headers as $index => $header) {
            $mainCategories[] = trim($header->innerHtml());
        }
        return $mainCategories;
    }


    public function setMap()
    {
        $response = $this->client->get($this->categoriesUrl);
        $html = $response->getBody()->getContents();
        $this->document->loadHtml($html);

        $document = $this->document;

        $mainCategories = [];
        foreach ($document->find('.tub a') as $node) {
            $mainCategories[] = trim($node->text());
        }

        $result = [];

        $colBlocks = $document->find('.col13');
        foreach ($colBlocks as $index => $colBlock) {
            $main = $mainCategories[$index] ?? 'Без категории';

            $result[$main] = [];

            $rubItems = $colBlock->find('ul.rub > li');

            foreach ($rubItems as $rubLi) {
                $subCategoryAnchor = $rubLi->first('a');
                if (!$subCategoryAnchor) continue;

                $subCategory = trim($subCategoryAnchor->text());
                $result[$main][$subCategory] = [];

                $subList = $rubLi->first('ul.sub');
                if ($subList) {
                    foreach ($subList->find('li a') as $childAnchor) {
                        $result[$main][$subCategory][] = trim($childAnchor->text());
                    }
                }
            }
        }

        echo '<pre>';
        print_r($result);
        echo '</pre>';
        exit();
    }

}
