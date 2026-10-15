<?php
namespace App\Models;

class Transaction
{
    public int $id;
    public string $transactionDate;
    public int $createdBy;
    public string $createdAt;
    
    /** @var TransactionItem[] */
    public array $items = [];
}
