<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromptResource\Pages;
use App\Filament\Resources\PromptResource\RelationManagers;
use App\Models\Prompt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromptResource extends Resource
{
    protected static ?string $model = Prompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('Уникальный ключ для использования в коде'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(200)
                    ->helperText('Название промпта'),
                Forms\Components\Textarea::make('prompt')
                    ->required()
                    ->rows(10)
                    ->helperText('Текст промпта. Используйте {placeholder} для переменных'),
                Forms\Components\Select::make('model')
                    ->options([
                        'gpt-4o' => 'GPT-4o',
                        'gpt-4o-mini' => 'GPT-4o Mini',
                        'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                    ])
                    ->default('gpt-4o')
                    ->required(),
                Forms\Components\TextInput::make('max_tokens')
                    ->numeric()
                    ->default(2000)
                    ->minValue(100)
                    ->maxValue(8000),
                Forms\Components\TextInput::make('temperature')
                    ->numeric()
                    ->default(0.3)
                    ->minValue(0)
                    ->maxValue(2)
                    ->step(0.1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePrompts::route('/'),
        ];
    }
}
