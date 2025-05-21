<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class RevenueChart extends ChartWidget
{
  protected static ?string $heading = 'Revenue';

  protected int | string | array $columnSpan = 'full';

  protected static ?string $maxHeight = '300px';

  protected function getData(): array
  {
    // Replace this with actual data from your database
    $data = [
      'Jan' => 5000000,
      'Feb' => 4200000,
      'Mar' => 6800000,
      'Apr' => 7500000,
      'May' => 9200000,
      'Jun' => 8100000,
    ];

    return [
      'datasets' => [
        [
          'label' => 'Revenue (Rp)',
          'data' => array_values($data),
          'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
          'borderColor' => 'rgb(255, 205, 86)',
          'borderWidth' => 1,
        ],
      ],
      'labels' => array_keys($data),
    ];
  }

  protected function getType(): string
  {
    return 'bar';
  }
}
