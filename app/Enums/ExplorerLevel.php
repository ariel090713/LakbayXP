<?php

namespace App\Enums;

enum ExplorerLevel: string
{
    case BeginnerExplorer = 'beginner_explorer';
    case WeekendWanderer = 'weekend_wanderer';
    case TrailHunter = 'trail_hunter';
    case SummitCollector = 'summit_collector';
}
