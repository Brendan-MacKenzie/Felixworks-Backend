<?php

namespace App\Exceptions;

use Exception;

class SyncException extends Exception
{
    public $agencyId;
    public $type;
    public $data;
    public $message;

    public function __construct(string $message, int $agencyId, string $type, mixed $data)
    {
        $this->message = $message;
        $this->agencyId = $agencyId;
        $this->type = $type;
        $this->data = $data;

        parent::__construct();
    }

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'message' => $this->message,
            'agency_id' => $this->agencyId,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
