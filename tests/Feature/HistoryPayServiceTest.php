<?php

use App\Enum\PartnerCategory;
use App\Models\Fee;
use App\Models\HistoryPay;
use App\Models\Partner;
use App\Service\HistoryPayService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Carbon::setTestNow();
});

it('keeps past advance payments at zero once their month is due', function (): void {
    Carbon::setTestNow('2025-04-10');

    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 123,
        'nombre' => 'Socio de prueba',
        'ingreso' => '2025-03-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2025-01',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    Fee::create([
        'mes' => '2025-03',
        'cuota' => 45.00,
        'impuesto' => 0.00,
    ]);

    $marchPayment = HistoryPay::create([
        'acc' => 123,
        'fecha' => '2025-02-23',
        'mes' => '2025-03',
        'monto' => 40.00,
    ]);

    $aprilPayment = HistoryPay::create([
        'acc' => 123,
        'fecha' => '2025-04-10',
        'mes' => '2025-04',
        'monto' => 45.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(123);

    expect($debtMap[(int) $marchPayment->ind])->toBe(0.00)
        ->and($debtMap[(int) $aprilPayment->ind])->toBe(0.00);
});

it('only shows negative debt for payments applied after the current month', function (): void {
    Carbon::setTestNow('2025-08-05');

    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 456,
        'nombre' => 'Socio adelantado',
        'ingreso' => '2025-07-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2025-07',
        'cuota' => 40.02,
        'impuesto' => 0.00,
    ]);

    $julyPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2025-08-05',
        'mes' => '2025-07',
        'monto' => 40.02,
    ]);

    $augustPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2025-08-05',
        'mes' => '2025-08',
        'monto' => 32.02,
    ]);

    $augustDiscount = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2025-08-05',
        'mes' => '2025-08',
        'monto' => 8.00,
    ]);

    $septemberPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2025-08-05',
        'mes' => '2025-09',
        'monto' => 32.02,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(456);

    expect($debtMap[(int) $julyPayment->ind])->toBe(0.00)
        ->and($debtMap[(int) $augustPayment->ind])->toBe(8.00)
        ->and($debtMap[(int) $augustDiscount->ind])->toBe(0.00)
        ->and($debtMap[(int) $septemberPayment->ind])->toBe(-32.02);
});
