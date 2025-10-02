<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function data(Request $request)
    {
        $filters = $this->filters($request);

        $rows = Order::query()
            ->whenDateRange($filters['from'], $filters['to'])
            ->whenStatus($filters['status'])
            ->whenChannel($filters['channel'])
            ->whenCategory($filters['category'])
            ->orderBy('ordered_at')
            ->get(['id', 'order_no', 'customer_name', 'channel', 'category', 'status', 'amount', 'ordered_at']);

        // agregasi chart per hari
        $byDay = $rows->groupBy(fn ($r) => $r->ordered_at->format('Y-m-d'))
            ->map(fn ($g) => [
                'count'  => $g->count(),
                'amount' => (float) $g->sum('amount'),
            ])
            ->sortKeys();

        return response()->json([
            'rows' => $rows->map(function ($r) {
                return [
                    'Order No'   => $r->order_no,
                    'Customer'   => $r->customer_name,
                    'Channel'    => $r->channel,
                    'Category'   => $r->category,
                    'Status'     => $r->status,
                    'Amount'     => (float) $r->amount,
                    'Ordered At' => $r->ordered_at->format('Y-m-d H:i:s'),
                ];
            }),
            'chart' => [
                'labels' => array_values($byDay->keys()->all()),
                'count'  => array_values($byDay->pluck('count')->all()),
                'amount' => array_values($byDay->pluck('amount')->all()),
            ],
        ]);
    }

    private function filters(Request $request): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        // default: 30 hari terakhir
        if (!$from || !$to) {
            $to   = now()->format('Y-m-d');
            $from = now()->subDays(30)->format('Y-m-d');
        }

        return [
            'from'      => $from,
            'to'        => $to,
            'status'    => array_filter((array) $request->input('status')) ?: null,
            'channel'   => array_filter((array) $request->input('channel')) ?: null,
            'category'  => array_filter((array) $request->input('category')) ?: null,
            'sort'      => $request->input('sort'),
            'dir'       => $request->input('dir'),
            'per_page'  => $request->input('per_page'),
        ];
    }

    private function summary(?string $from, ?string $to, array $filters): array
    {
        $base = Order::query()
            ->whenDateRange($from, $to)
            ->whenStatus($filters['status'] ?? null)
            ->whenChannel($filters['channel'] ?? null)
            ->whenCategory($filters['category'] ?? null);

        $count  = (clone $base)->count();
        $amount = (clone $base)->sum('amount');
        $avg    = $count ? $amount / $count : 0;

        // periode sebelumnya (untuk growth)
        $start    = Carbon::parse($from . ' 00:00:00');
        $end      = Carbon::parse($to . ' 23:59:59');
        $days     = $start->diffInDays($end) + 1;
        $prevFrom = $start->copy()->subDays($days);
        $prevTo   = $start->copy()->subSecond();

        $prevBase = Order::query()
            ->whenDateRange($prevFrom->format('Y-m-d'), $prevTo->format('Y-m-d'))
            ->whenStatus($filters['status'] ?? null)
            ->whenChannel($filters['channel'] ?? null)
            ->whenCategory($filters['category'] ?? null);

        $prevCount  = (clone $prevBase)->count();
        $prevAmount = (clone $prevBase)->sum('amount');

        $growth = function ($curr, $prev) {
            if ($prev == 0) {
                return $curr > 0 ? 100 : 0;
            }
            return round((($curr - $prev) / $prev) * 100, 2);
        };

        return [
            'count'         => $count,
            'amount'        => (float) $amount,
            'avg'           => (float) round($avg, 2),
            'growth_count'  => $growth($count, $prevCount),
            'growth_amount' => $growth($amount, $prevAmount),
            'prev_period'   => [$prevFrom->toDateString(), $prevTo->toDateString()],
        ];
    }

     public function index(Request $request)
    {
        $filters = $this->filters($request);

        // query utama + sorting
        $q = Order::query()
            ->whenDateRange($filters['from'], $filters['to'])
            ->whenStatus($filters['status'])
            ->whenChannel($filters['channel'])
            ->whenCategory($filters['category']);

        $sort = $filters['sort'] ?? 'ordered_at';
        $dir  = $filters['dir']  ?? 'desc';
        $q->orderBy($sort, $dir);

        $perPage = $filters['per_page'] ? (int)$filters['per_page'] : 25;
        $orders = $q->paginate($perPage)->appends($filters);

        // executive summary
        $summary = $this->summary($filters['from'], $filters['to'], $filters);

        // nilai unik untuk Select2
        $meta = [
            'statuses'   => Order::select('status')->distinct()->pluck('status'),
            'channels'   => Order::select('channel')->distinct()->pluck('channel'),
            'categories' => Order::select('category')->distinct()->pluck('category'),
        ];

        return view('reports.index', compact('orders', 'filters', 'summary', 'meta'));
    }
}
