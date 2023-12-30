<?php

declare(strict_types=1);

namespace App\Enums;

enum CacheStore: string
{
    case Article = 'article_result';
    case ImagePrompts = 'image_prompts' ;
    case Images = 'images_results' ;
}
