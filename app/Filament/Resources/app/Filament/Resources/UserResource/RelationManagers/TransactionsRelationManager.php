<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = \'transactions\';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make(\'amount\')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make(\'type\')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make(\'description\')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute(\'amount\')
            ->columns([
                Tables\Columns\TextColumn::make(\'amount\')
                    ->numeric()
                    ->sortable()
                    ->prefix(\'€\'),
                Tables\Columns\TextColumn::make(\'type\')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        \'deposit\' => \'success\',
                        \'withdraw\' => \'danger\',
                        default => \'gray\',
                    }),
                Tables\Columns\TextColumn::make(\'description\'),
                Tables\Columns\TextColumn::make(\'created_at\')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

