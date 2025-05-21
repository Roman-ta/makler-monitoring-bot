<?php

namespace App\Models;

use App\telegram\Parser;
use Illuminate\Database\Eloquent\Model;

class MainCategoriesModel extends Model
{
    protected $table = 'main_categories';
    protected $fillable = [
        'category_name',
        'icon',
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
        $mainCategories = (new Parser())->parseMainCategories();
        foreach ($mainCategories as $index => $mainCategory) {
            $icon = self::ICON_ARRAY[$mainCategory] ?? self::ICON_ARRAY['default'];
            MainCategoriesModel::updateOrCreate(
                ['category_name' => $mainCategory],
                ['icon' => $icon, 'updated_at' => now()]
            );

        }
    }
}
