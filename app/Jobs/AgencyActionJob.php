<?php

namespace App\Jobs;

use Throwable;
use App\Models\Agency;
use App\Mail\FailureMail;
use Illuminate\Bus\Queueable;
use App\Exceptions\SyncException;
use App\Services\Sync\SyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AgencyActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $key;
    public $agency;
    public $type;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct(Agency $agency, string $type, mixed $data)
    {
        $this->agency = $agency;
        $this->key = str_replace(':', '-', str_replace(' ', '-', 'AgencyActionJob'.'-'.$type.''.'-'.$agency->id.'-'.now()));
        $this->onQueue('agencies');
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(SyncService $syncService): void
    {
        Log::info('[SYNC] Agency: '.$this->agency->id.' - '.$this->type);
        try {
            $syncService
                ->notifyAgency(
                    $this->agency,
                    $this->type,
                    $this->data
                );
        } catch (SyncException $syncException) {
            throw $syncException;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $email = (new FailureMail('AgencyActionJob', $this->agency, $this->type, $this->data, $exception))->onQueue('emails');
        Mail::to('support@brendan-mackenzie.com')->queue($email);
    }
}
