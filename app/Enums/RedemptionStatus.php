<?php

namespace App\Enums;

enum RedemptionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Claimed = 'claimed';
    case Rejected = 'rejected';
}
