<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DailySystemReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $reportData;
    public Carbon $fromDate;
    public Carbon $toDate;

    /**
     * Create a new message instance.
     */
    public function __construct(array $reportData, Carbon $fromDate, Carbon $toDate)
    {
        $this->reportData = $reportData;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Harian Sistem Irigasi - ' . $this->fromDate->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-system-report',
            with: [
                'reportData' => $this->reportData,
                'fromDate' => $this->fromDate,
                'toDate' => $this->toDate,
                'summary' => $this->reportData['summary'] ?? [],
                'rows' => $this->reportData['rows'] ?? [],
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
