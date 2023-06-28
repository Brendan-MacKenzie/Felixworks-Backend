<?php

namespace App\Http\Controllers\Sync;

use Exception;
use App\Models\Agency;
use App\Models\Posting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Sync\SyncPostingService;

class ApiController extends Controller
{
    private $syncPostingService;

    public function __construct(SyncPostingService $syncPostingService)
    {
        $this->syncPostingService = $syncPostingService;
    }

    public function sync(Request $request, Posting $posting)
    {
        try {
            $agency = $this->getAuth($request->cookie('client_id', $request->header('X-Client-Id')));
            $posting = $this->syncPostingService->get($agency, $posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    private function getAuth($clientId)
    {
        return Agency::findOrFail($clientId);
    }
}
