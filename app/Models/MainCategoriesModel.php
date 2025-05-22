<?php

namespace App\Models;

use App\telegram\Parser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MainCategoriesModel extends Model
{
    protected $table = 'main_categories';
    protected $fillable = [
        'category_id',
        'category_name',
        'icon',
        'url',
        'created_at',
        'updated_at',
    ];
    const ICON_ARRAY = [
        'Недвижимость' => '🏠',
        'Транспорт' => '🚗',
        'Работа и обучение' => '💼',
        'Услуги' => '🛠',
        'Строительство и ремонт' => '🧱',
        'Мебель и интерьер' => '🛋',
        'Одежда, обувь, аксессуары' => '👗',
        'Все для детей' => '🧸',
        'Handmade' => '🎨',
        'Компьютеры, оргтехника и IT' => '💻',
        'Аудио, видео, фото, ТВ' => '📺',
        'Телефоны и связь' => '📱',
        'Бытовая техника' => '🧺',
        'Оборудование и приборы' => '⚙️',
        'Туризм, спорт и отдых' => '🏕',
        'Растения и животные' => '🐶',
        'Дачное и сельское хозяйство' => '🌾',
        'Знакомства' => '❤️',
        'Свадьбы, праздники и подарки' => '🎉',
        'Музыка, книги, искусство' => '📚',
        'Информационные сообщения' => 'ℹ️',
        'Прочее' => '📦',
        'default' => '❓'
    ];

    public static function updateMainCategories(): void
    {
        try {
            $categories = (new Parser())->getCategories();
            foreach ($categories as $index => $mainCategory) {
                $icon = self::ICON_ARRAY[$mainCategory['html']] ?? self::ICON_ARRAY['default'];
                MainCategoriesModel::updateOrCreate(
                    ['category_name' => $mainCategory['html']],
                    ['category_id'=> $index,'icon' => $icon, 'url' => $mainCategory['url'], 'updated_at' => now()]
                );
                foreach ($mainCategory['rubs'] as $child) {
                    ChildCategoriesModel::updateOrCreate(
                        ['child_name' => $child['html']],
                        ['parent_id' => $index, 'parent_icon' => $icon, 'url' => $child['url'], 'updated_at' => now()]
                    );
                }
            }
        } catch (\Exception $exception) {
            Log::error("update categories error" . $exception->getMessage());
        }

    }
}
