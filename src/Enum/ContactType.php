<?php declare(strict_types=1);

namespace App\Enum;

enum ContactType: string
{
    case PHONE = 'phone';
    case EMAIL = 'email';
    case FAX = 'fax';
    case MOBILE = 'mobile';
    case PAGER = 'pager';
    case OTHER = 'other';
}
