<?php

namespace App\Services;

use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;

class ExportService
{
    protected array $export_data;
    protected Builder $query;
    protected array $options = [];

    public function __construct(array $export_data, Builder $query, array $options)
    {
        $this->export_data = $export_data;
        $this->query       = $query;
        $this->options     = $options;
    }

    /**
     * @param null $transformResultItem
     * @param null $prepareRows
     * @return array
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws ReaderNotOpenedException
     * @throws WriterNotOpenedException
     * @throws \Exception
     */
    public function exportProgress($transformResultItem = null, $prepareRows = null, $handelResult = null): array
    {
        $exportData = $this->export_data;
        $query      = $this->query;
        $options    = $this->options;

        if ($exportData && ($exportData['done'] ?? false)) {
            $exportData['progress_info'] = $this->exportProgressInfo($exportData);

            return $exportData;
        }

        $options = array_merge([
            'hash_id'   => '',
            'file_name' => 'Export_',
            'file_dir'  => 'export_data',
            'headers'   => [],
            'limit'     => 1000,
        ], $options);
        list('hash_id' => $hashId, 'file_name' => $fileName, 'file_dir' => $fileDir) = $options;
        if (!$exportData) {
            if ($options['total'] < $options['limit']) {
                $options['limit'] = $options['total'];
            }
            $exportData = [
                'total'        => $options['total'],
                'limit'        => $options['limit'],
                'step'         => $options['limit'] ? floor($options['total'] / $options['limit']) : 0,
                'current_step' => 1,
                'offset'       => 0,
                'processed'    => 0,
                'file_name'    => $fileName,
                'started_at'   => now()->format('Y-m-d H:i:s'),
                'ended_at'     => now()->format('Y-m-d H:i:s'),
            ];
            cache()->add($hashId, $exportData, now()->addHour());
        }

        $results                 = $query->limit($exportData['limit'])->offset($exportData['offset'])->get();
        $totalItem               = $results->count();
        $exportData['processed'] += $totalItem;

        if ($totalItem) {
            if (isset($handelResult)) {
                $results = $handelResult($results);
            } else {
                if ($prepareRows) {
                    $results = $prepareRows($results);
                }
                $results = $results->map(function ($result, $index) use ($transformResultItem) {
                    if ($transformResultItem == null) {
                        return Row::fromValues($result->toArray());
                    }

                    return $transformResultItem($result, $index);
                })
                    ->toArray();
            }

            $results = array_map(function ($item) {
                return $item ? Row::fromValues($item) : false;
            }, $results);
            $results = array_filter($results);

            $exportData['done'] = $exportData['processed'] >= $exportData['total'];

            $this->exportProgressWrite($results, $exportData, $options);
        } else {
            $exportData['done'] = true;
        }

        $exportData['ended_at']     = now()->format('Y-m-d H:i:s');
        $exportData['current_step'] += 1;
        $exportData['offset']       += $exportData['limit'];
        $exportData['percent']      = $exportData['total'] ? round($exportData['processed'] / ($exportData['total'] / 100), 2) : 100;
        $exportData['download']     = route('admin.file.action', [
            'type' => 'download', 'nameFile' => $exportData['file_name'], 'folder' => $fileDir
        ]);
        cache()->put($hashId, $exportData, now()->addHour());

        $exportData['progress_info'] = $this->exportProgressInfo($exportData);

        return $exportData;
    }

    /**
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws InvalidArgumentException
     * @throws ReaderNotOpenedException
     */
    function exportProgressWrite($results, $exportData, $options): string
    {
        Storage::makeDirectory($options['file_dir']);
        $fileFolder  = "app/{$options['file_dir']}/";
        $filePath    = storage_path($fileFolder . $exportData['file_name']);
        $fileCSV     = Str::endsWith($filePath, '.csv');
        $multiHeader = $options['multi_header'] ?? false;

        $defaultStyle = new Style();
        $defaultStyle->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setBorder(new Border(
                new BorderPart(name: Border::TOP, width: Border::WIDTH_THIN),
                new BorderPart(name: Border::RIGHT, width: Border::WIDTH_THIN),
                new BorderPart(name: Border::BOTTOM, width: Border::WIDTH_THIN),
                new BorderPart(name: Border::LEFT, width: Border::WIDTH_THIN),
            ));

        if ($exportData['current_step'] == 1) {
            if ($fileCSV) {
                $writer = new \OpenSpout\Writer\CSV\Writer();
            } else {
                $xlsxOptions                    = new \OpenSpout\Writer\XLSX\Options();
                $xlsxOptions->DEFAULT_ROW_STYLE = $defaultStyle;

                $writer = new \OpenSpout\Writer\XLSX\Writer($xlsxOptions);
            }
            $writer->openToFile($filePath);
            if ($options['headers']) {
                if (!$fileCSV && $multiHeader) {
                    $headers = $this->parseHeaders($options);
                    foreach ($headers as $header) {
                        $writer->addRow($header);
                    }
                } else {
                    $writer->addRow(Row::fromValues($options['headers']));
                }
            }
            $writer->addRows($results);

            if (!$fileCSV && $exportData['done'] && isset($xlsxOptions)) {
                if ($options['merge_cells'] ?? []) {
                    foreach ($options['merge_cells'] as $merge_cell) {
                        $xlsxOptions->mergeCells(
                            $merge_cell[0],
                            $merge_cell[1],
                            $merge_cell[2],
                            $merge_cell[3]
                        );
                    }
                }
            }

            $writer->close();
        } else {
            $tmpFilePath = storage_path($fileFolder . 'tmp_' . $exportData['file_name']);
            if ($fileCSV) {
                $reader = new \OpenSpout\Reader\CSV\Reader();
                $reader->open($filePath);

                $writer = new \OpenSpout\Writer\CSV\Writer();
            } else {
                $readerOptions = new \OpenSpout\Reader\XLSX\Options();
//                $readerOptions->SHOULD_FORMAT_DATES = true; // this is to be able to copy dates

                $reader = new \OpenSpout\Reader\XLSX\Reader($readerOptions);
                $reader->open($filePath);

                $xlsxOptions                    = new \OpenSpout\Writer\XLSX\Options();
                $xlsxOptions->DEFAULT_ROW_STYLE = $defaultStyle;

                $writer = new \OpenSpout\Writer\XLSX\Writer($xlsxOptions);
            }
            $writer->openToFile($tmpFilePath);

            foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                // Add sheets in the new file, as we read new sheets in the existing one
                if ($sheetIndex !== 1) {
                    $writer->addNewSheetAndMakeItCurrent();
                }

                if (!$fileCSV && $multiHeader) {
                    $headers = $this->parseHeaders($options);
                    foreach ($headers as $header) {
                        $writer->addRow($header);
                    }
                }

                foreach ($sheet->getRowIterator() as $i => $row) {
                    if (!$fileCSV && $multiHeader && $i <= count($options['headers'])) {
                        continue;
                    }
                    // ... and copy each row into the new spreadsheet
                    $writer->addRow($row);
                }
            }
            $writer->addRows($results);

            if (!$fileCSV && $exportData['done'] && isset($xlsxOptions)) {
                if ($options['merge_cells'] ?? []) {
                    foreach ($options['merge_cells'] as $merge_cell) {
                        $xlsxOptions->mergeCells(
                            $merge_cell[0],
                            $merge_cell[1],
                            $merge_cell[2],
                            $merge_cell[3]
                        );
                    }
                }
            }
            $reader->close();
            $writer->close();

            unlink($filePath);
            rename($tmpFilePath, $filePath);
        }
        return $filePath;
    }

    function parseHeaders($options): array
    {
        $style = new Style();
        $style->setCellAlignment(CellAlignment::CENTER);
        $style->setCellVerticalAlignment(CellAlignment::CENTER);
        $style->setBackgroundColor(Color::ORANGE);

        return collect($options['headers'])
            ->map(function ($items) use ($style) {
                $header = array_map(function ($item) {
                    return is_array($item) ? ($item['value'] ?? '') : $item;
                }, $items);

                return Row::fromValues($header, $style);
            })
            ->toArray();
    }

    public function exportProgressInfo($export_data): string
    {
        $end = $export_data['done'] ? sprintf('<span>Kết thúc:</span><span>%s</span>', $export_data['ended_at']) : '';
        return sprintf('
        <span>Tổng số:</span><span>%s</span>
        <span>Đã xử lý:</span><span>%s</span>
        <span>File:</span><span>%s</span>
        <span>Bắt đầu:</span><span>%s</span>
        %s
        ', number_format($export_data['total'], 0, ',', '.')
            , number_format($export_data['processed'], 0, ',', '.')
            , $export_data['done'] ? sprintf('<a class="text-success" href="%s">%s</a>'
                , $export_data['download']
                , $export_data['file_name']) : $export_data['file_name']
            , $export_data['started_at']
            , $end
        );
    }
}
