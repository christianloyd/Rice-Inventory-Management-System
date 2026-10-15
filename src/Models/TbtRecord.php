<?php
namespace App\Models;

class TbtRecord
{
    public int $id;
    public int $transactionItemId;
    public string $customerName;
    public ?string $contactNumber;
    public string $status;
    public string $recordedAt;
    public ?int $releasedBy;
    public ?string $releasedAt;
}
