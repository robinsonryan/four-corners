<?php

declare(strict_types=1);

namespace RobinsonRyan\FourCorners\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use RobinsonRyan\FourCorners\Services\TrainingDataExporter;

final class ExportTrainingDataCommand extends Command
{
    protected $signature = 'four-corners:export-training
        {--from= : Start date (Y-m-d)}
        {--to= : End date (Y-m-d)}
        {--document-type= : Document type code}
        {--include-rejected : Include rejected annotations}
        {--output=training-data.jsonl : Output file path}';

    protected $description = 'Export annotation data for ML training';

    public function handle(TrainingDataExporter $exporter): int
    {
        $fromOption = $this->option('from');
        $toOption = $this->option('to');
        $documentType = $this->option('document-type');
        $includeRejected = (bool) $this->option('include-rejected');
        $output = $this->option('output');

        $from = is_string($fromOption) ? Carbon::parse($fromOption) : null;
        $to = is_string($toOption) ? Carbon::parse($toOption) : null;

        $this->info('Exporting training data...');

        $count = $exporter->export(
            outputPath: is_string($output) ? $output : 'training-data.jsonl',
            from: $from,
            to: $to,
            documentTypeCode: is_string($documentType) ? $documentType : null,
            includeRejected: $includeRejected,
        );

        $outputPath = is_string($output) ? $output : 'training-data.jsonl';
        $this->info("Exported {$count} annotations to {$outputPath}");

        return self::SUCCESS;
    }
}
