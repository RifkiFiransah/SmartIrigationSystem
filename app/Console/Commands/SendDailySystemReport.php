<?php

namespace App\Console\Commands;

use App\Mail\DailySystemReportMail;
use App\Services\SystemReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SendDailySystemReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:report-email {recipients?* : Email recipients}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily system report via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipients = $this->argument('recipients');
        if (empty($recipients)) {
            $recipients = config('mail.report_recipients', []);
        }

        if (empty($recipients)) {
            $this->error('No recipients specified. Set mail.report_recipients in config or pass as arguments.');
            return Command::FAILURE;
        }

        $this->info('Generating system report for yesterday...');
        
        $yesterday = Carbon::now()->subDay();
        $from = $yesterday->startOfDay();
        $to = $yesterday->endOfDay();
        
        $service = app(SystemReportService::class);
        
        // Generate report for yesterday with all active devices
        $result = $service->buildAggregation($from, $to, [], true);
        
        if (empty($result['rows'])) {
            $this->warn('No data found for yesterday. Email not sent.');
            return Command::SUCCESS;
        }

        $this->info("Found {$result['summary']['total_records']} records from {$result['summary']['total_devices']} devices");

        // Send email to each recipient
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new DailySystemReportMail($result, $from, $to));
                $this->info("Report sent to: {$recipient}");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$recipient}: " . $e->getMessage());
            }
        }

        $this->info('Daily system report email task completed.');
        return Command::SUCCESS;
    }
}
