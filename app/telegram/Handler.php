<?php

namespace App\telegram;

use App\Models\ChildCategoriesModel;
use App\Models\MainCategoriesModel;
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
                        Button::make('🚩 Приднестровье')->action('getMainCategory')->param('region', 'Pridnestrovie')->width(0.5),
                        Button::make('🇲🇩 Молдова')->action('getMainCategory')->param('region', 'Moldova')->width(0.5),
                    ])
                    ->row([
                        Button::make('🌐 Молдова + Приднестровье')->action('getMainCategory')->param('region', 'PM')->width(1),
                    ])
            )
            ->send();


    }

    /**
     * @return void
     */
    public function getMainCategory(): void
    {
        $region = $this->data->get('region');
        $mainCategories = MainCategoriesModel::all()->toArray();
        Log::info('q', $mainCategories);
        $keyboard = Keyboard::make();
        $row = [];
        foreach ($mainCategories as $category) {
            $row[] = Button::make($category['icon'] . $category['category_name'])->action('getChildCategory')
                ->param('region', $region)->param('category_id', $category['category_id']);
        }
        $chunks = array_chunk($row, 2);
        foreach ($chunks as $chunk) {
            $keyboard->row($chunk);
        }

        Telegraph::chat($this->chat->chat_id)->message('Выбери основную категорию')
            ->keyboard($keyboard)
            ->send();
    }

    /**
     * @return void
     */
    public function getChildCategory(): void
    {
        $categoryId = $this->data->get('category_id');
        $childCategories = ChildCategoriesModel::where('parent_id', $categoryId)->get()->toArray();
        $keyboard = Keyboard::make();
        $row = [];
        foreach ($childCategories as $category) {
            $row[] = Button::make($category['parent_icon'] . $category['child_name'])->action('confirm');
        }
        $chunks = array_chunk($row, 2);
        foreach ($chunks as $chunk) {
            $keyboard->row($chunk);
        }
        Telegraph::chat($this->chat->chat_id)->message('Теперь можешь выбрать подкатегорию')->keyboard($keyboard)->send();
    }

    public function confirm()
    {

    }

    public function handleChatMessage($message): void
    {
        $this->reply($message);
    }

}
