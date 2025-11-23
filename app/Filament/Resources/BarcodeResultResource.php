<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarcodeResultResource\Pages;
use App\Models\BarcodeResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BarcodeResultResource extends Resource
{
    protected static ?string $model = BarcodeResult::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = 'Баркоды';

    protected static ?string $modelLabel = 'Баркод';

    protected static ?string $pluralModelLabel = 'Баркоды';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('photo_id')
                    ->label('Фото')
                    ->relationship('photo', 'id')
                    ->required(),
                Forms\Components\TextInput::make('symbology')
                    ->label('Тип')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('data')
                    ->label('Код')
                    ->required()
                    ->maxLength(500),
                Forms\Components\TextInput::make('source')
                    ->label('Источник')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('symbology')
                    ->label('Тип')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->label('Код')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Источник')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gg-label' => 'warning',
                        'zbar' => 'info',
                        'opencv-qr' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('photo.batch.correlation_id')
                    ->label('Карточка')
                    ->searchable(),
                Tables\Columns\TextColumn::make('photo_id')
                    ->label('Фото ID')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Источник')
                    ->options([
                        'zbar' => 'ZBar',
                        'opencv-qr' => 'OpenCV QR',
                        'vision-ocr' => 'Vision OCR',
                        'gg-label' => 'GG Label',
                    ]),
                Tables\Filters\SelectFilter::make('symbology')
                    ->label('Тип')
                    ->options([
                        'CODE128' => 'CODE128',
                        'CODE39' => 'CODE39',
                        'EAN13' => 'EAN13',
                        'QRCODE' => 'QR Code',
                        'UPC-A' => 'UPC-A',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBarcodeResults::route('/'),
            'create' => Pages\CreateBarcodeResult::route('/create'),
            'edit' => Pages\EditBarcodeResult::route('/{record}/edit'),
        ];
    }
}
