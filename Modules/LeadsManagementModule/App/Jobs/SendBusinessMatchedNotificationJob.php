<?php

namespace Modules\LeadsManagementModule\App\Jobs;

use App\Services\SendPulseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SendBusinessMatchedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $businesses;

    /**
     * Create a new job instance.
     *
     * @param array $businesses
     */
    public function __construct(Collection $businesses)
    {
        $this->businesses = $businesses;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SendPulseService $sendPulseService)
    {
        foreach ($this->businesses as $business) {
            try {
                $org_name = $business->org_name;
                $org_id = $business->id;
                $url = "https://fyndah.com/businessDashboard/{$org_id}/{$org_name}/search-request";
                $htmlContent = view('emails.business.matched', ['url' => $url, 'org_name' => $org_name])->render();

                // Sending email notification using SendPulseService
                $sendPulseService->sendEmail(
                    $business->email,
                    'You have been matched in a search',
                    $htmlContent
                );

                // Log successful email notification
                Log::info('Email sent to business', [
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->org_name
                    ]
                ]);
            } catch (\Exception $e) {
                // Log error if sending email fails
                Log::error('Error sending email notification', [
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->org_name
                    ],
                    'exception_message' => $e->getMessage()
                ]);
            }
        }
    }
}
