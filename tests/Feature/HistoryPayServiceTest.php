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

it('sums pending debt from the paid month through the payment date month', function (): void {
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

it('calculates current month debt using payments applied to that same month', function (): void {
    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 456,
        'nombre' => 'Socio corriente',
        'ingreso' => '2026-01-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2026-01',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    $firstJanuaryPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-01-22',
        'mes' => '2026-01',
        'monto' => 30.00,
    ]);

    $secondJanuaryPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-01-23',
        'mes' => '2026-01',
        'monto' => 8.00,
    ]);

    $finalJanuaryPayment = HistoryPay::create([
        'acc' => 456,
        'fecha' => '2026-01-24',
        'mes' => '2026-01',
        'monto' => 2.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(456);

    expect($debtMap[(int) $firstJanuaryPayment->ind])->toBe(10.00)
        ->and($debtMap[(int) $secondJanuaryPayment->ind])->toBe(2.00)
        ->and($debtMap[(int) $finalJanuaryPayment->ind])->toBe(0.00);
});

it('shows advance payments as negative balance from the payment date month to the paid future month', function (): void {
    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 789,
        'nombre' => 'Socio adelantado',
        'ingreso' => '2025-08-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2025-08',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    HistoryPay::create([
        'acc' => 789,
        'fecha' => '2025-08-05',
        'mes' => '2025-08',
        'monto' => 40.00,
    ]);

    HistoryPay::create([
        'acc' => 789,
        'fecha' => '2025-08-05',
        'mes' => '2025-09',
        'monto' => 20.00,
    ]);

    HistoryPay::create([
        'acc' => 789,
        'fecha' => '2025-08-05',
        'mes' => '2025-09',
        'monto' => 20.00,
    ]);

    $octoberPayment = HistoryPay::create([
        'acc' => 789,
        'fecha' => '2025-08-05',
        'mes' => '2025-10',
        'monto' => 8.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(789);

    expect($debtMap[(int) $octoberPayment->ind])->toBe(-48.00);
});

it('sums unpaid months when a payment is applied to an overdue month', function (): void {
    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 987,
        'nombre' => 'Socio atrasado',
        'ingreso' => '2025-03-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2025-03',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    $marchPayment = HistoryPay::create([
        'acc' => 987,
        'fecha' => '2025-05-05',
        'mes' => '2025-03',
        'monto' => 20.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(987);

    expect($debtMap[(int) $marchPayment->ind])->toBe(100.00);
});

it('keeps historical advance calculations independent from todays month', function (): void {
    Carbon::setTestNow('2026-05-28');

    Schema::table('0cc_socios', function (Blueprint $table): void {
        $table->unique('acc');
    });

    Partner::create([
        'acc' => 654,
        'nombre' => 'Socio historico adelantado',
        'ingreso' => '2025-08-01',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Fee::create([
        'mes' => '2025-08',
        'cuota' => 40.00,
        'impuesto' => 0.00,
    ]);

    HistoryPay::create([
        'acc' => 654,
        'fecha' => '2025-08-05',
        'mes' => '2025-08',
        'monto' => 40.00,
    ]);

    $futurePayment = HistoryPay::create([
        'acc' => 654,
        'fecha' => '2025-08-05',
        'mes' => '2025-10',
        'monto' => 10.00,
    ]);

    $debtMap = app(HistoryPayService::class)->computeRunningDebtMap(654);

    expect($debtMap[(int) $futurePayment->ind])->toBe(-10.00);
});
