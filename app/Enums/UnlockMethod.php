<?php

namespace App\Enums;

enum UnlockMethod: string
{
    case EventCompletion = 'event_completion';
    case PhotoProof = 'photo_proof';
    case SelfReport = 'self_report';
    case OrganizerVerification = 'organizer_verification';
    case AdminApproval = 'admin_approval';
    case QrCode = 'qr_code';
}
