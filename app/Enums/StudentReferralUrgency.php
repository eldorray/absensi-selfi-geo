<?php

namespace App\Enums;

enum StudentReferralUrgency: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';
}
