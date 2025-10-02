<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
Schema::create('orders', function (Blueprint $table) {
$table->id();
$table->string('order_no')->unique();
$table->unsignedBigInteger('customer_id')->nullable();
$table->string('customer_name')->nullable();
$table->string('channel')->nullable(); // e.g. web, store, marketplace
$table->string('category')->nullable(); // e.g. electronics, fashion, etc.
$table->enum('status', ['pending','paid','shipped','cancelled','refunded'])->default('pending');
$table->decimal('amount', 12, 2);
$table->timestamp('ordered_at')->index();
$table->timestamps();
});


Schema::create('order_items', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('order_id');
$table->string('sku');
$table->string('product_name');
$table->string('brand')->nullable();
$table->string('category')->nullable();
$table->integer('qty');
$table->decimal('price', 12, 2);
$table->timestamps();
$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
});
}


public function down(): void
{
Schema::dropIfExists('order_items');
Schema::dropIfExists('orders');
}
};