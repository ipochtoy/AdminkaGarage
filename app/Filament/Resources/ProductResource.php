<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Гараж';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(5),
                Forms\Components\TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('brand')
                    ->label('Бренд'),
                Forms\Components\TextInput::make('size')
                    ->label('Размер'),
                Forms\Components\TextInput::make('color')
                    ->label('Цвет'),
                Forms\Components\TextInput::make('material')
                    ->label('Материал'),
                Forms\Components\Select::make('condition')
                    ->label('Состояние')
                    ->options([
                        'new' => 'New',
                        'used' => 'Used',
                    ]),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photos.image_path')
                    ->label('')
                    ->disk('public')
                    ->limit(1)
                    ->size(48),
                Tables\Columns\TextColumn::make('title')
                    ->label('Товар')
                    ->searchable()
                    ->limit(45)
                    ->description(fn(Product $record): ?string => $record->brand),
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'published' => 'Активно',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn(Product $record): string => Pages\ViewProduct::getUrl(['record' => $record]));
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make()
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\Group::make([
                                    \Filament\Infolists\Components\ViewEntry::make('photos')
                                        ->hiddenLabel()
                                        ->view('filament.infolists.components.product-photos'),
                                ])->columnSpan(1),
                                \Filament\Infolists\Components\Group::make([
                                    \Filament\Infolists\Components\TextEntry::make('brand')
                                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                        ->color('gray')
                                        ->hiddenLabel(),
                                    \Filament\Infolists\Components\TextEntry::make('title')
                                        ->size(\Filament\Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                        ->hiddenLabel(),
                                    \Filament\Infolists\Components\TextEntry::make('price')
                                        ->money()
                                        ->size(\Filament\Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->color('success')
                                        ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                        ->hiddenLabel(),
                                    \Filament\Infolists\Components\TextEntry::make('size')
                                        ->label('Размер')
                                        ->inlineLabel(),
                                    \Filament\Infolists\Components\TextEntry::make('condition')
                                        ->label('Состояние')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'new' => 'success',
                                            'used' => 'warning',
                                        })
                                        ->inlineLabel(),
                                    \Filament\Infolists\Components\TextEntry::make('description')
                                        ->label('Описание')
                                        ->markdown()
                                        ->prose(),
                                ])->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
