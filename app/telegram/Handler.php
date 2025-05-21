<?php

namespace App\telegram;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

class Handler extends WebhookHandler
{
    private string $bot_token;
    private Parser $parser;

    public function __construct()
    {
        parent::__construct();
        $this->bot_token = env('BOT_TOKEN');
        $this->parser = new Parser();
    }

    public function start()
    {
        Telegraph::chat($this->chat->chat_id)
            ->message(
                "👋 Привет! Я твой бот, который поможет находить свежие объявления на [makler.md](https://makler.md).\n\n" .
                "📢 Я умею автоматически присылать тебе объявления из выбранной рубрики — с нужной тебе периодичностью.\n\n" .
                "🌍 Давай сначала выберем регион, в котором ты хочешь искать объявления:"
            )
            ->keyboard(
                Keyboard::make()
                    ->row([
                        Button::make('🚩 Приднестровье')->action('getMainRubric')->param('region', 'Pridnestrovie')->width(0.5),
                        Button::make('🇲🇩 Молдова')->action('getMainRubric')->param('region', 'Moldova')->width(0.5),
                    ])
                    ->row([
                        Button::make('🌐 Молдова + Приднестровье')->action('getMainRubric')->param('region', 'PM')->width(1),
                    ])
            )
            ->send();


    }

    public function getMainRubric()
    {
        $region = $this->data->get('region');
        $mainCategories = $this->parser->parseMainCategories();
        $keyboard = Keyboard::make();
        $row = [];
        foreach ($mainCategories as $category) {
            $row[] = Button::make($category)->action('test')->param('region', $region);
        }
        $chunks = array_chunk($row, 2);
        foreach ($chunks as $chunk) {
            $keyboard->row($chunk);
        }

        Telegraph::chat($this->chat->chat_id)->message('Выбери основную категорию')
            ->keyboard($keyboard)
            ->send();
    }


    public function handleChatMessage($message): void
    {
        $this->reply($message);
    }
}
