<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoBufferResource\Pages;
use App\Models\PhotoBuffer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhotoBufferResource extends Resource
{
    protected static ?string $model = PhotoBuffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Буфер фото';

    protected static ?string $modelLabel = 'Фото в буфере';

    protected static ?string $pluralModelLabel = 'Фото в буфере';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('processed', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Изображение')
                    ->image()
                    ->directory('buffer')
                    ->disk('public')
                    ->required(),
                Forms\Components\TextInput::make('gg_label')
                    ->label('GG лейбл')
                    ->maxLength(50),
                Forms\Components\TextInput::make('barcode')
                    ->label('Баркод')
                    ->maxLength(200),
                Forms\Components\TextInput::make('group_id')
                    ->label('Группа')
                    ->numeric(),
                Forms\Components\TextInput::make('group_order')
                    ->label('Порядок в группе')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('processed')
                    ->label('Обработано'),
                Forms\Components\Toggle::make('sent_to_bot')
                    ->label('Отправлено в бота'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->disk('public')
                    ->width(120)
                    ->height(120)
                    ->square(),
                Tables\Columns\TextColumn::make('gg_label')
                    ->label('GG лейбл')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Баркод')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('group_id')
                    ->label('Группа')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('processed')
                    ->label('✓')
                    ->boolean(),
                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Загружено')
                    ->dateTime('d.m H:i')
                    ->sortable(),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('processed')
                    ->label('Обработано'),
                Tables\Filters\Filter::make('today')
                    ->label('Сегодня')
                    ->query(fn ($query) => $query->whereDate('uploaded_at', today())),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_processed')
                    ->label('✓')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['processed' => true]))
                    ->visible(fn ($record) => !$record->processed),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_all_processed')
                        ->label('Отметить обработанными')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['processed' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('10s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoBuffers::route('/'),
            'create' => Pages\CreatePhotoBuffer::route('/create'),
            'edit' => Pages\EditPhotoBuffer::route('/{record}/edit'),
        ];
    }
}
