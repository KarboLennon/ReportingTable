<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'paid', 'shipped', 'cancelled', 'refunded'];
        $channels = ['web', 'store', 'marketplace'];
        $categories = ['electronics', 'fashion', 'groceries', 'beauty', 'toys'];
        $brands = ['Acme', 'Globex', 'Umbrella', 'Soylent', 'Initech'];


        $now = now();
        for ($i = 0; $i < 500; $i++) {
            $orderedAt = $now->copy()->subDays(rand(0, 120))->setTime(rand(8, 20), rand(0, 59));
            $status = $statuses[array_rand($statuses)];
            $amount = rand(20, 500) * 1000; // IDR-like numbers


            $orderId = DB::table('orders')->insertGetId([
                'order_no' => strtoupper(Str::random(10)),
                'customer_id' => rand(1, 80),
                'customer_name' => 'Customer ' . rand(1, 80),
                'channel' => $channels[array_rand($channels)],
                'category' => $categories[array_rand($categories)],
                'status' => $status,
                'amount' => $amount,
                'ordered_at' => $orderedAt,
                'created_at' => $orderedAt,
                'updated_at' => $orderedAt,
            ]);


            $itemsCount = rand(1, 5);
            for ($j = 0; $j < $itemsCount; $j++) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'product_name' => 'Product ' . rand(1, 100),
                    'brand' => $brands[array_rand($brands)],
                    'category' => $categories[array_rand($categories)],
                    'qty' => rand(1, 5),
                    'price' => rand(5, 100) * 1000,
                    'created_at' => $orderedAt,
                    'updated_at' => $orderedAt,
                ]);
            }
        }
    }
}