<?php

namespace App\Enums;

enum StudentReferralStatus: string
{
    case New = 'new';
    case InHandling = 'in_handling';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
