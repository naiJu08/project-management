<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Leave Types';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., AL, SL, UL')
                            ->helperText('Short code for this leave type'),

                        Forms\Components\ColorPicker::make('color')
                            ->default('#3b82f6')
                            ->helperText('Color for UI display'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('days_per_year')
                            ->label('Days Per Year')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Annual allocation for this leave type'),

                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-sun')
                            ->helperText('Optional Heroicon name'),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Paid Leave')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Requires Approval')
                            ->default(true)
                            ->inline(false),

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Color'),

                Tables\Columns\TextColumn::make('days_per_year')
                    ->label('Days/Year')
                    ->sortable()
                    ->suffix(' days'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Approval Required')
                    ->boolean()
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
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Paid Leave')
                    ->placeholder('All leave types')
                    ->trueLabel('Paid only')
                    ->falseLabel('Unpaid only'),

                Tables\Filters\TernaryFilter::make('requires_approval')
                    ->label('Requires Approval')
                    ->placeholder('All leave types')
                    ->trueLabel('Requires approval')
                    ->falseLabel('No approval needed'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All leave types')
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
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'view' => Pages\ViewLeaveType::route('/{record}'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}
