<?php

namespace App\Enums;

enum PlaceCategory: string
{
    case Mountain = 'mountain';
    case Beach = 'beach';
    case Island = 'island';
    case Falls = 'falls';
    case River = 'river';
    case Lake = 'lake';
    case Campsite = 'campsite';
    case Historical = 'historical';
    case FoodDestination = 'food_destination';
    case RoadTrip = 'road_trip';
    case HiddenGem = 'hidden_gem';
}
