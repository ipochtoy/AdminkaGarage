<?php

namespace App\Filament\Resources\PhotoBatchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Фото';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Изображение')
                    ->image()
                    ->directory('photos')
                    ->required(),
                Forms\Components\Toggle::make('is_main')
                    ->label('Главное фото'),
                Forms\Components\TextInput::make('order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->disk('public')
                    ->width(80)
                    ->height(80),
                Tables\Columns\IconColumn::make('is_main')
                    ->label('Главное')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Порядок')
                    ->sortable(),
                Tables\Columns\TextColumn::make('barcodes_count')
                    ->label('Баркоды')
                    ->counts('barcodes'),
                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Загружено')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->defaultSort('order')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('set_main')
                    ->label('Сделать главным')
                    ->icon('heroicon-o-star')
                    ->action(function ($record) {
                        $record->update(['is_main' => true]);
                    })
                    ->visible(fn ($record) => !$record->is_main),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
