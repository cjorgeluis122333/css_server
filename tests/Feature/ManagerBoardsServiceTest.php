<?php

use App\Enum\PartnerCategory;
use App\Http\Requests\ManagerBoardsRequest;
use App\Models\Manager;
use App\Models\ManagerBoards;
use App\Models\Partner;
use App\Service\ManagerBoardsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('persists board roles and returns the related managers', function (): void {
    Manager::create([
        'acc' => 101,
        'cedula' => 15979176,
        'nombre' => 'Presidente de prueba',
    ]);

    Manager::create([
        'acc' => 102,
        'cedula' => 1366427,
        'nombre' => 'Vicepresidente de prueba',
    ]);

    Manager::create([
        'acc' => 103,
        'cedula' => 17031196,
        'nombre' => 'Secretario de prueba',
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

it('rejects board role cedulas that are socios but not registered managers', function (): void {
    Partner::create([
        'acc' => 500,
        'cedula' => 15979176,
        'nombre' => 'Socio titular sin directivo',
        'categoria' => PartnerCategory::TITULAR->value,
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
