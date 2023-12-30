<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

enum AuthorStyle: string
{
    use Options;
    use Values;

     case Narrative = 'a narrative';
     case Authoritative = 'an authoritative';
     case Sad = 'a sad';
     case Emotional = 'an emotional';
     case Inspiring = 'an inspiring';
     case Professional = 'a professional';
     case Happy = 'a happy';
}
