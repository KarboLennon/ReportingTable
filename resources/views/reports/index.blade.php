@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1/daterangepicker.css" rel="stylesheet" />
    <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.css">

    <style>
        .page-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1rem;
        }

        .page-title .bi {
            font-size: 1.5rem
        }

        .app-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .06);
        }

        .app-card .card-header {
            background: transparent;
            border-bottom: 0;
            font-weight: 600;
            padding-bottom: 0;
        }

        .summary-card {
            border: 0;
            border-radius: 1rem;
            color: #fff;
            overflow: hidden;
            position: relative;
            min-height: 120px;
        }

        .chart-container{ position:relative; height:360px; } 

        .summary-card .card-body {
            position: relative;
            z-index: 2
        }

        .summary-card .bg-shape {
            position: absolute;
            right: -40px;
            bottom: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            opacity: .15;
            background: #fff;
            z-index: 1;
        }

        .summary-kpi-value {
            font-size: 1.6rem;
            font-weight: 700
        }

        .summary-kpi-label {
            font-size: .85rem;
            opacity: .95
        }

        .summary-kpi-delta {
            font-size: .8rem;
            font-weight: 600
        }

        .select2-container .select2-selection--multiple {
            min-height: 38px;
            padding-bottom: 4px
        }

        .filter-actions .btn {
            height: 38px
        }

        .chart-wrap {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .06)
        }

        #pivot-container .pvtUi {
            border-radius: .75rem;
            border: 1px solid #eaeaea
        }

        .help-text {
            font-size: .85rem;
            color: #6c757d
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">

        <!-- Title -->
        <div class="page-title">
            <i class="bi bi-bar-chart-line-fill text-primary"></i>
            <div>
                <h3 class="mb-0">Reporting Dashboard</h3>
                <div class="help-text">Filter data, lihat ringkasan, grafik, dan pivot drag-and-drop</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card app-card mb-4">
            <div class="card-header px-3 px-md-4 pt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Filters</div>
                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
            <div class="card-body px-3 px-md-4 pb-4">
                <form id="filter-form" method="GET" action="{{ route('reports.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date Range</label>
                            <input type="text" id="daterange" class="form-control" autocomplete="off">
                            <input type="hidden" name="from" id="from"
                                value="{{ $filters['from'] ?? now()->subDays(30)->format('Y-m-d') }}">
                            <input type="hidden" name="to" id="to" value="{{ $filters['to'] ?? now()->format('Y-m-d') }}">
                            <div class="help-text mt-1">Default: 30 hari terakhir</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status[]" id="status" class="form-select" multiple>
                                @isset($meta['statuses'])
                                    @foreach($meta['statuses'] as $s)
                                        <option value="{{ $s }}" @selected(in_array($s, $filters['status'] ?? []))>{{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Channel</label>
                            <select name="channel[]" id="channel" class="form-select" multiple>
                                @isset($meta['channels'])
                                    @foreach($meta['channels'] as $s)
                                        <option value="{{ $s }}" @selected(in_array($s, $filters['channel'] ?? []))>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category[]" id="category" class="form-select" multiple>
                                @isset($meta['categories'])
                                    @foreach($meta['categories'] as $s)
                                        <option value="{{ $s }}" @selected(in_array($s, $filters['category'] ?? []))>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end filter-actions mt-1">
                            <button class="btn btn-primary px-4">
                                <i class="bi bi-funnel-fill me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <div class="bg-shape"></div>
                    <div class="card-body">
                        <div class="summary-kpi-label mb-1"><i class="bi bi-receipt-cutoff me-1"></i> Total Orders</div>
                        <div class="summary-kpi-value">{{ number_format($summary['count'] ?? 0) }}</div>
                        <div class="summary-kpi-delta mt-1">
                            <i class="bi bi-graph-up-arrow"></i>
                            {{ ($summary['growth_count'] ?? 0) }}% vs prev ({{ $summary['prev_period'][0] ?? '-' }} →
                            {{ $summary['prev_period'][1] ?? '-' }})
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #10b981, #22c55e);">
                    <div class="bg-shape"></div>
                    <div class="card-body">
                        <div class="summary-kpi-label mb-1"><i class="bi bi-cash-coin me-1"></i> Gross Amount</div>
                        <div class="summary-kpi-value">{{ number_format($summary['amount'] ?? 0, 2) }}</div>
                        <div class="summary-kpi-delta mt-1">
                            <i class="bi bi-graph-up-arrow"></i> {{ ($summary['growth_amount'] ?? 0) }}% vs prev
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
                    <div class="bg-shape"></div>
                    <div class="card-body">
                        <div class="summary-kpi-label mb-1"><i class="bi bi-basket2-fill me-1"></i> Avg Order Value</div>
                        <div class="summary-kpi-value">{{ number_format($summary['avg'] ?? 0, 2) }}</div>
                        <div class="summary-kpi-delta mt-1">
                            <i class="bi bi-calendar-date"></i> Period: {{ $filters['from'] ?? '-' }} →
                            {{ $filters['to'] ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card summary-card" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                    <div class="bg-shape"></div>
                    <div class="card-body">
                        <div class="summary-kpi-label mb-1"><i class="bi bi-clock-history me-1"></i> Refresh</div>
                        <div class="summary-kpi-value d-flex align-items-center gap-2">
                            <button id="refresh-chart" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Refresh Data
                            </button>
                        </div>
                        <div class="summary-kpi-delta mt-1">Sync chart & pivot dengan filter aktif</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card app-card chart-wrap mb-4">
            <div class="card-header px-3 px-md-4 pt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold"><i class="bi bi-activity me-2"></i> Orders & Amount by Day</div>
                </div>
            </div>
            <div class="card-body px-3 px-md-4">
                <div class="chart-container">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pivot -->
        <div class="card app-card mb-4">
            <div class="card-header px-3 px-md-4 pt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold"><i class="bi bi-table me-2"></i> Pivot Table</div>
                    <button id="reset-pivot" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
            <div class="card-body px-3 px-md-4">
                <div class="help-text mb-2">Drag kolom dari atas ke Rows/Cols. Klik renderer untuk ganti tipe tampilan.
                </div>
                <div id="pivot-container"></div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.js"></script>

    <script>
        $(function () {
            // Select2
            $('#status,#channel,#category').select2({
                width: '100%', placeholder: 'Any', allowClear: true
            });

            // Date Range Picker
            const from = $('#from').val();
            const to = $('#to').val();
            $('#daterange').val(from + ' - ' + to);
            $('#daterange').daterangepicker({
                startDate: from,
                endDate: to,
                locale: { format: 'YYYY-MM-DD' }
            }, function (start, end) {
                $('#from').val(start.format('YYYY-MM-DD'));
                $('#to').val(end.format('YYYY-MM-DD'));
            });

            // Chart.js
            let chart;
            function renderChart(data) {
                const ctx = document.getElementById('ordersChart');
                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Orders', data: data.count },
                            { label: 'Amount', data: data.amount, yAxisID: 'y1' },
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        maintainAspectRatio: false,
                        elements: { line: { tension: .3 } },
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            y: { type: 'linear', position: 'left' },
                            y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } }
                        }
                    }
                });
            }

            function loadData() {
                const params = new URLSearchParams($('#filter-form').serialize());
                $.getJSON('{{ route('reports.data') }}?' + params.toString(), function (resp) {
                    try {
                        // Chart
                        renderChart(resp.chart || { labels: [], count: [], amount: [] });

                        // Pivot
                        $('#pivot-container').empty().pivotUI(resp.rows || [], {
                            rows: ['Status'],
                            cols: ['Channel'],
                            vals: ['Amount'],
                            aggregatorName: 'Sum',
                            rendererName: 'Table'
                        });
                    } catch (e) {
                        console.error('[Pivot error]', e);
                    }
                });
            }

            loadData();

            $('#refresh-chart').on('click', function (e) {
                e.preventDefault(); loadData();
            });
            $('#reset-pivot').on('click', function () {
                loadData();
            });
        });
    </script>
@endpush