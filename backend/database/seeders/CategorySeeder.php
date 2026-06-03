<?php

namespace Database\Seeders;

use App\Models\CollectionCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // 主題類
            ['name' => '戀愛', 'group' => 'theme', 'display_order' => 1],
            ['name' => '校園', 'group' => 'theme', 'display_order' => 2],
            ['name' => '科幻', 'group' => 'theme', 'display_order' => 3],
            ['name' => '奇幻', 'group' => 'theme', 'display_order' => 4],
            ['name' => '冒險', 'group' => 'theme', 'display_order' => 5],
            ['name' => '動作', 'group' => 'theme', 'display_order' => 6],
            ['name' => '戰鬥', 'group' => 'theme', 'display_order' => 7],
            ['name' => '運動', 'group' => 'theme', 'display_order' => 8],
            ['name' => '搞笑', 'group' => 'theme', 'display_order' => 9],
            ['name' => '治癒', 'group' => 'theme', 'display_order' => 10],
            ['name' => '音樂', 'group' => 'theme', 'display_order' => 11],
            ['name' => '美食', 'group' => 'theme', 'display_order' => 12],
            ['name' => '偵探', 'group' => 'theme', 'display_order' => 13],
            ['name' => '懸疑', 'group' => 'theme', 'display_order' => 14],
            ['name' => '恐怖', 'group' => 'theme', 'display_order' => 15],
            ['name' => '機甲', 'group' => 'theme', 'display_order' => 16],
            ['name' => '魔法', 'group' => 'theme', 'display_order' => 17],
            ['name' => '異世界', 'group' => 'theme', 'display_order' => 18],
            ['name' => '職場', 'group' => 'theme', 'display_order' => 19],
            ['name' => '歷史', 'group' => 'theme', 'display_order' => 20],
            ['name' => '戰爭', 'group' => 'theme', 'display_order' => 21],
            ['name' => '後宮', 'group' => 'theme', 'display_order' => 22],
            ['name' => '百合', 'group' => 'theme', 'display_order' => 23],
            ['name' => '萌系', 'group' => 'theme', 'display_order' => 24],
            ['name' => '日常', 'group' => 'theme', 'display_order' => 25],
            // 來源類
            ['name' => '原創', 'group' => 'source', 'display_order' => 1],
            ['name' => '漫畫改編', 'group' => 'source', 'display_order' => 2],
            ['name' => '輕小說改編', 'group' => 'source', 'display_order' => 3],
            ['name' => '遊戲改編', 'group' => 'source', 'display_order' => 4],
            ['name' => '小說改編', 'group' => 'source', 'display_order' => 5],
            // 媒體類型
            ['name' => 'TV 動畫', 'group' => 'media_type', 'display_order' => 1],
            ['name' => '劇場版', 'group' => 'media_type', 'display_order' => 2],
            ['name' => 'OVA', 'group' => 'media_type', 'display_order' => 3],
            ['name' => 'ONA', 'group' => 'media_type', 'display_order' => 4],
            ['name' => '特別篇', 'group' => 'media_type', 'display_order' => 5],
            ['name' => '漫畫單行本', 'group' => 'media_type', 'display_order' => 6],
            ['name' => '網路漫畫', 'group' => 'media_type', 'display_order' => 7],
        ];

        foreach ($categories as $cat) {
            CollectionCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
