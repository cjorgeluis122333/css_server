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

it('sums pending debt from the paid month through the current month', function (): void {
    Carbon::setTestNow('2026-05-27');

    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 123,
        'nombre' => 'Socio de prueba',
        'ingreso' => '2026-03-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2026-03',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    $marchPayment = HistoryPay::create([
        'acc' => 123,
        'fecha' => '2026-05-27',
        'mes' => '2026-03',
        'monto' => 1.20,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(123);

    expect($debtMap[(int) $marchPayment->ind])->toBe(118.80);
});

it('shows advance payments as negative balance from the current month to the paid future month', function (): void {
    Carbon::setTestNow('2026-05-27');

    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 456,
        'nombre' => 'Socio adelantado',
        'ingreso' => '2026-05-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2026-05',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    $currentMonthPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-05-27',
        'mes' => '2026-05',
        'monto' => 40.00,
    ]);

    $junePayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-05-27',
        'mes' => '2026-06',
        'monto' => 40.00,
    ]);

    $firstJulyPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-05-27',
        'mes' => '2026-07',
        'monto' => 10.00,
    ]);

    $secondJulyPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-05-27',
        'mes' => '2026-07',
        'monto' => 10.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(456);

    expect($debtMap[(int) $currentMonthPayment->ind])->toBe(0.00)
        ->and($debtMap[(int) $junePayment->ind])->toBe(-40.00)
        ->and($debtMap[(int) $firstJulyPayment->ind])->toBe(-50.00)
        ->and($debtMap[(int) $secondJulyPayment->ind])->toBe(-60.00);
});
