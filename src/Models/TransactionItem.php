<?php
namespace App\Models;

class TransactionItem
{
    public int $id;
    public int $transactionId;
    public string $receiptNo;
    public int $riceVariantId;
    public int $quantity;
    public float $unitPrice;
    public float $subtotal;
    public string $remarks;
    public string $fulfillmentType;
    public string $createdAt;
    
    public ?TbtRecord $tbtRecord = null;
}
