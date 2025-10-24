<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpan(2),

                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable(),

                        Forms\Components\Select::make('level')
                            ->options([
                                'Intern' => 'Intern',
                                'Junior' => 'Junior',
                                'Mid' => 'Mid',
                                'Senior' => 'Senior',
                                'Lead' => 'Lead',
                                'Manager' => 'Manager',
                                'Director' => 'Director',
                                'VP' => 'VP',
                                'C-Level' => 'C-Level',
                            ])
                            ->searchable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Employees')
                    ->counts('employees')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->enum([
                        true => 'Active',
                        false => 'Inactive',
                    ])
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),

                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'Intern' => 'Intern',
                        'Junior' => 'Junior',
                        'Mid' => 'Mid',
                        'Senior' => 'Senior',
                        'Lead' => 'Lead',
                        'Manager' => 'Manager',
                        'Director' => 'Director',
                        'VP' => 'VP',
                        'C-Level' => 'C-Level',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All positions')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'view' => Pages\ViewPosition::route('/{record}'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
