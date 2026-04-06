<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Full = 'full';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
