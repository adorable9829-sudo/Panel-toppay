<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = \'heroicon-o-users\';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(\'name\')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make(\'email\')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make(\'balance\')
                    ->numeric()
                    ->disabled()
                    ->prefix(\'€\'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(\'name\')
                    ->searchable(),
                Tables\Columns\TextColumn::make(\'email\')
                    ->searchable(),
                Tables\Columns\TextColumn::make(\'balance\')
                    ->numeric()
                    ->sortable()
                    ->prefix(\'€\'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make(\'Depositar\')
                    ->form([
                        TextInput::make(\'amount\')
                            ->label(\'Cantidad a depositar\')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                    ])
                    ->action(function (User $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            $record->deposit($data[\'amount\']);
                            Transaction::create([
                                \'user_id\' => $record->id,
                                \'amount\' => $data[\'amount\'],
                                \'type\' => \'deposit\',
                                \'description\' => \'Depósito manual por administrador\',
                            ]);
                        });
                    })
                    ->modalHeading(\'Depositar en cuenta de usuario\'),
                Action::make(\'Retirar\')
                    ->form([
                        TextInput::make(\'amount\')
                            ->label(\'Cantidad a retirar\')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                    ])
                    ->action(function (User $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            $record->withdraw($data[\'amount\']);
                            Transaction::create([
                                \'user_id\' => $record->id,
                                \'amount\' => $data[\'amount\'],
                                \'type\' => \'withdraw\',
                                \'description\' => \'Retiro manual por administrador\',
                            ]);
                        });
                    })
                    ->modalHeading(\'Retirar de cuenta de usuario\'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make(\'Ver Transacciones\')
                    ->url(fn (User $record): string => UserResource::getUrl(\'view\', [\'record\' => $record]) . \'#transactions\')
                    ->icon(\'heroicon-o-currency-dollar\'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            \'index\' => Pages\ListUsers::route(\'/\'),
            \'create\' => Pages\CreateUser::route(\'/create\'),
            \'view\' => Pages\ViewUser::route(\'/{record}\'),
            \'edit\' => Pages\EditUser::route(\'/{record}/edit\'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

