<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = 'Фото';

    protected static ?string $modelLabel = 'Фото';

    protected static ?string $pluralModelLabel = 'Фото';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('photo_batch_id')
                    ->label('Карточка товара')
                    ->relationship('batch', 'correlation_id')
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label('Изображение')
                    ->image()
                    ->directory('photos')
                    ->disk('public')
                    ->required(),
                Forms\Components\Toggle::make('is_main')
                    ->label('Главное фото'),
                Forms\Components\TextInput::make('order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('file_id')
                    ->label('Telegram File ID')
                    ->maxLength(255),
                Forms\Components\TextInput::make('message_id')
                    ->label('Message ID')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch.correlation_id')
                    ->label('Карточка')
                    ->searchable()
                    ->url(fn (Photo $record): string => route('filament.admin.resources.photo-batches.edit', $record->photo_batch_id)),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->disk('public')
                    ->width(60)
                    ->height(60),
                Tables\Columns\IconColumn::make('is_main')
                    ->label('Главное')
                    ->boolean(),
                Tables\Columns\TextColumn::make('barcodes_count')
                    ->label('Баркоды')
                    ->counts('barcodes'),
                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Загружено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('batch')
                    ->relationship('batch', 'correlation_id')
                    ->label('Карточка'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('create_vision_task')
                    ->label('Vision')
                    ->icon('heroicon-o-eye')
                    ->action(function (Photo $record) {
                        $record->processingTasks()->firstOrCreate(
                            ['api_name' => 'google-vision'],
                            ['status' => 'pending']
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }
}
