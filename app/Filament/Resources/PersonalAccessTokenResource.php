<?php

declare(strict_types=1);

namespace Modules\User\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Modules\User\Models\OauthAccessToken;
use Modules\Xot\Filament\Resources\XotBaseResource;

use Filament\Forms\Components\Field;
final class PersonalAccessTokenResource extends XotBaseResource
{
    protected static ?string $model = OauthAccessToken::class;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @return array<string, mixed>
     */
    //#[\Override]
    public static function getFormSchemaOld(): array
    {
        return [
            'name' => TextInput::make('name')
                ->required()
                ->maxLength(255),
        ];
    }

    public static function getPages(): array
    {
        return [];
    }
}
