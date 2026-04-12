<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TitularDebtExport implements FromArray, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
protected $data;
protected $year;

public function __construct(array $data, int $year)
{
$this->data = $data;
$this->year = $year;
}

public function array(): array
{
return $this->data;
}

public function headings(): array
{
return [
'ACC', 'NOMBRE', 'DEUDA',
'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'
];
}

/**
* Aquí transformamos cada objeto del JSON en una fila de Excel.
* Importante: Casteamos a (float) para que Excel lo reconozca como número.
*/
public function map($partner): array
{
$monthsData = [];
// Generamos las llaves de los 12 meses (2026-01, 2026-02...)
for ($m = 1; $m <= 12; $m++) {
$monthKey = sprintf("%04d-%02d", $this->year, $m);
// Si el mes no existe en el JSON de deuda, ponemos 0
$monthsData[] = (float) ($partner['deuda'][$monthKey] ?? 0.0);
}

return array_merge([
$partner['acc'],
$partner['name'],
(float) $partner['total'],
], $monthsData);
}

public function columnFormats(): array
{
// Formato contable para las columnas de dinero (C hasta O)
// Esto garantiza que el Excel use la coma o punto según la región del usuario.
return [
'C' => NumberFormat::FORMAT_NUMBER_00,
'D' => NumberFormat::FORMAT_NUMBER_00,
'E' => NumberFormat::FORMAT_NUMBER_00,
'F' => NumberFormat::FORMAT_NUMBER_00,
'G' => NumberFormat::FORMAT_NUMBER_00,
'H' => NumberFormat::FORMAT_NUMBER_00,
'I' => NumberFormat::FORMAT_NUMBER_00,
'J' => NumberFormat::FORMAT_NUMBER_00,
'K' => NumberFormat::FORMAT_NUMBER_00,
'L' => NumberFormat::FORMAT_NUMBER_00,
'M' => NumberFormat::FORMAT_NUMBER_00,
'N' => NumberFormat::FORMAT_NUMBER_00,
'O' => NumberFormat::FORMAT_NUMBER_00,
];
}

public function styles(Worksheet $sheet)
{
$lastRow = count($this->data) + 1;

return [
// Estilo para la cabecera (Basado en tu imagen: Fondo oscuro, texto blanco)
1 => [
'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
'fill' => [
'fillType' => Fill::FILL_SOLID,
'startColor' => ['rgb' => '222222']
],
],
// Bordes para toda la tabla
"A1:O{$lastRow}" => [
'borders' => [
'allBorders' => [
'borderStyle' => Border::BORDER_THIN,
'color' => ['rgb' => '444444'],
],
],
],
];
}
}
