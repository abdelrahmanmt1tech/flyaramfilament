<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\NewUsers as BaseNewUsers;
use App\Filament\Resources\UserResource;

class NewUsers extends BaseNewUsers
{
    protected static string $resource = UserResource::class;

    protected static bool $shouldRegisterNavigation = false;
}
