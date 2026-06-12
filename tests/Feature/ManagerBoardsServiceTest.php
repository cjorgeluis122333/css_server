<?php

use App\Enum\PartnerCategory;
use App\Http\Requests\ManagerBoardsRequest;
use App\Models\partners\ManagerBoards;
use App\Models\partners\Partner;
use App\Service\partner\ManagerBoardsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('persists board roles and returns the related managers', function (): void {
    Partner::create([
        'acc' => 101,
        'cedula' => 15979176,
        'nombre' => 'Presidente de prueba',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Partner::create([
        'acc' => 102,
        'cedula' => 1366427,
        'nombre' => 'Vicepresidente de prueba',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    Partner::create([
        'acc' => 103,
        'cedula' => 17031196,
        'nombre' => 'Secretario de prueba',
        'categoria' => PartnerCategory::TITULAR->value,
    ]);

    $board = app(ManagerBoardsService::class)->upsertBoard([
        'year' => 2027,
        'presidente' => 15979176,
        'vicepresidente' => 1366427,
        'secretario' => 17031196,
        'vicesecretario' => null,
        'tesorero' => null,
        'vicetesorero' => null,
        'bibliotecario' => null,
        'actas' => null,
        'viceactas' => null,
        'actos' => null,
        'deportes' => null,
        'vocal1' => null,
        'vocal2' => null,
    ]);

    expect($board->presidente)->toBe(15979176)
        ->and($board->rel_presidente?->nombre)->toBe('Presidente de prueba')
        ->and($board->rel_vicepresidente?->nombre)->toBe('Vicepresidente de prueba')
        ->and($board->rel_secretario?->nombre)->toBe('Secretario de prueba');

    $persistedBoard = ManagerBoards::findOrFail(2027);

    expect($persistedBoard->presidente)->toBe(15979176)
        ->and($persistedBoard->vicepresidente)->toBe(1366427)
        ->and($persistedBoard->secretario)->toBe(17031196);
});

it('rejects board role cedulas that are not registered titular partners', function (): void {
    // A familiar partner should be rejected
    Partner::create([
        'acc' => 500,
        'cedula' => 15979176,
        'nombre' => 'Socio familiar sin directivo',
        'categoria' => PartnerCategory::FAMILIAR->value,
    ]);

    $validator = Validator::make(
        [
            'year' => 2027,
            'presidente' => 15979176,
        ],
        (new ManagerBoardsRequest)->rules()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('presidente'))->toBeTrue();
});
