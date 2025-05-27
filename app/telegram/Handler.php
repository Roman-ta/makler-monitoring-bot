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

        Telegraph::chat($this->chat->chat_id)->message('🎯 Отлично! Теперь выбери основную категорию из списка ниже 👇')
            ->keyboard($keyboard)
            ->send();
    }

    /**
     * @return void
     */
    public function getChildCategory(): void
    {
        $categoryId = $this->data->get('category_id');
        $region = $this->data->get('region');

        $childCategories = ChildCategoriesModel::where('parent_id', $categoryId)->get()->toArray();
        $keyboard = Keyboard::make();
        $row = [];
        foreach ($childCategories as $category) {
            $row[] = Button::make($category['parent_icon'] . $category['child_name'])->action('confirmLogic')
                ->param('region', $region)->param('category_id', $categoryId)->param('child_id', $category['id']);
        }
        $chunks = array_chunk($row, 2);
        foreach ($chunks as $chunk) {
            $keyboard->row($chunk);
        }
        Telegraph::chat($this->chat->chat_id)->message('🧩 Почти готово! Выбери подходящую подкатегорию 🎨')->keyboard($keyboard)->send();
    }

    public function confirmLogic()
    {
        $region = $this->data->get('region');
        $categoryId = $this->data->get('category_id');
        $child = $this->data->get('child_id');


        $mainCategoryInfo = MainCategoriesModel::where('category_id', $categoryId)->first()->toArray();
        $childCategoryInfo = ChildCategoriesModel::where('id', $child)->first()->toArray();

        Telegraph::chat($this->chat->chat_id)->message("🚀 Отлично! Ты выбрал:\n\n🌍 Регион: *$region*\n📦 Категория: *{$mainCategoryInfo['category_name']}*\n📁 Подкатегория: *{$childCategoryInfo['child_name']}*\n\nВсе правильно? Подтверди, если всё ок! 😉")
            ->keyboard(Keyboard::make()->row([
                Button::make('Да, все верно')->action('confirmTime'),
                Button::make('Нет')->action('test'),
            ]))
            ->send();


    }

    public function confirmTime()
    {
        Telegraph::chat($this->chat->chat_id)->message("Я могу присылать объявления с выбранной тобой рубрики с периодичностью:")
            ->keyboard(Keyboard::make()->row([
                Button::make('1 мин')->action('finish'),
                Button::make('2 мин')->action('finish'),
                Button::make('5 мин')->action('finish')])
                ->row(
                    [Button::make('10 мин')->action('finish'),
                    Button::make('15 мин')->action('finish'),
                    Button::make('20 мин')->action('finish')])
                ->row([
                    Button::make('30 мин')->action('finish'),
                    Button::make('1 час')->action('finish'),
                    Button::make('2 часа')->action('finish'),
                ])->row([
                    Button::make('3 часа')->action('finish'),
                    Button::make('6 часов')->action('finish'),
                ])->row([
                    Button::make('12 часов')->action('finish'),
                    Button::make('24 часа')->action('finish'),
                ]))
            ->send();

    }


    public function handleChatMessage($message): void
    {

        $this->reply($message);
    }

}
