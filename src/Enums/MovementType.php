<?php

namespace App\Enums;

enum MovementType: string
{
    case STOCK_IN = 'STOCK_IN';
    case SALE = 'SALE';
    case ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    case ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    case SALE_REVERSAL = 'SALE_REVERSAL';
    case STOCK_IN_REVERSAL = 'STOCK_IN_REVERSAL';
}
