<?php
namespace App\Models;

class StockDelivery
{
    public int $id;
    public string $stockInDate;
    public int $riceVariantId;
    public int $quantity;
    public ?string $referenceNote;
    public int $createdBy;
    public string $createdAt;
}
