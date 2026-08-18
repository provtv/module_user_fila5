<?php

/**
 * @see https://github.com/ryangjchandler/filament-user-resource/blob/main/src/resources/UserResource.php
 * @see https://github.com/3x1io/filament-user/blob/main/src/resources/UserResource.php
 */

declare(strict_types=1);

namespace Modules\User\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Field;
use Modules\User\Filament\Resources\UserResource\Schemas\UserForm;
use Modules\User\Filament\Resources\UserResource\Widgets\UserOverview;
use Modules\Xot\Datas\XotData;
use Modules\Xot\Filament\Resources\XotBaseResource;

class UserResource extends XotBaseResource
{
    public static function getWidgets(): array
    {
        return [
            UserOverview::class,
        ];
    }

    /**
     * @return array<int|string, \Filament\Schemas\Components\Component>
     */
    #[\Override]
    public static function getFormSchemaOld(): array
    {
        return UserForm::getFormSchema();
    }

    // public static function extendForm(\Closure $callback): void
    // {
    //    static::$extendFormCallback = $callback;
    // }

   

    // public static function enablePasswordUpdates(bool|Closure $condition = true): void
    // {
    //     static::$enablePasswordUpdates = $condition;
    // }

    /*
     * public static function getModel(): string
     * {
     * return config('filament-user-resource.model');
     * }
     */

    #[\Override]
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    /**
     * @return class-string<Model>
     */
    #[\Override]
    public static function getModel(): string
    {
        return XotData::make()->getUserClass();
    }
}
