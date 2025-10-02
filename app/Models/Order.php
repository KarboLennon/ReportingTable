<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
use HasFactory;


protected $fillable = [
'order_no','customer_id','customer_name','channel','category','status','amount','ordered_at'
];


protected $casts = [
'ordered_at' => 'datetime',
'amount' => 'decimal:2',
];


public function scopeWhenDateRange($q, ?string $from, ?string $to)
{
if ($from) { $q->where('ordered_at', '>=', $from.' 00:00:00'); }
if ($to) { $q->where('ordered_at', '<=', $to.' 23:59:59'); }
return $q;
}


public function scopeWhenStatus($q, ?array $statuses)
{
if ($statuses && count($statuses)) { $q->whereIn('status', $statuses); }
return $q;
}


public function scopeWhenChannel($q, ?array $channels)
{
if ($channels && count($channels)) { $q->whereIn('channel', $channels); }
return $q;
}


public function scopeWhenCategory($q, ?array $categories)
{
if ($categories && count($categories)) { $q->whereIn('category', $categories); }
return $q;
}
}