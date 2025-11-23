<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessingTaskResource\Pages;
use App\Models\ProcessingTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProcessingTaskResource extends Resource
{
    protected static ?string $model = ProcessingTask::class;

    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = 'Задачи обработки';

    protected static ?string $modelLabel = 'Задача обработки';

    protected static ?string $pluralModelLabel = 'Задачи обработки';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('photo_id')
                    ->label('Фото')
                    ->relationship('photo', 'id')
                    ->required(),
                Forms\Components\TextInput::make('api_name')
                    ->label('API')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'processing' => 'Обрабатывается',
                        'completed' => 'Завершено',
                        'failed' => 'Ошибка',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('error_message')
                    ->label('Ошибка')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Завершено'),
                Forms\Components\KeyValue::make('request_data')
                    ->label('Запрос'),
                Forms\Components\KeyValue::make('response_data')
                    ->label('Ответ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('api_name')
                    ->label('API')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('photo.batch.correlation_id')
                    ->label('Карточка')
                    ->searchable(),
                Tables\Columns\TextColumn::make('photo_id')
                    ->label('Фото')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'processing' => 'Обрабатывается',
                        'completed' => 'Завершено',
                        'failed' => 'Ошибка',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Завершено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'processing' => 'Обрабатывается',
                        'completed' => 'Завершено',
                        'failed' => 'Ошибка',
                    ]),
                Tables\Filters\SelectFilter::make('api_name')
                    ->label('API')
                    ->options([
                        'google-vision' => 'Google Vision',
                        'azure-cv' => 'Azure CV',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('process')
                    ->label('Обработать')
                    ->icon('heroicon-o-play')
                    ->visible(fn (ProcessingTask $record) => $record->status === 'pending')
                    ->action(function (ProcessingTask $record) {
                        // TODO: Implement actual processing
                        $record->update(['status' => 'processing']);
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
            'index' => Pages\ListProcessingTasks::route('/'),
            'create' => Pages\CreateProcessingTask::route('/create'),
            'edit' => Pages\EditProcessingTask::route('/{record}/edit'),
        ];
    }
}
