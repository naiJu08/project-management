<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeProfileResource\Pages;
use App\Models\EmployeeProfile;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class EmployeeProfileResource extends Resource
{
    protected static ?string $model = EmployeeProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Employees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('employee_code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->rules(['regex:/^[A-Za-z0-9-]+$/'])
                                    ->default(fn () => 'EMP-' . strtoupper(uniqid())),

                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->searchable(),

                                Forms\Components\Select::make('position_id')
                                    ->label('Position')
                                    ->relationship('position', 'title')
                                    ->searchable()
                                    ->getOptionLabelFromRecordUsing(fn (\App\Models\Position $record) => "{$record->title} - {$record->level}"),

                                Forms\Components\Select::make('manager_id')
                                    ->label('Manager')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable(),

                                Forms\Components\DatePicker::make('hire_date')
                                    ->label('Hire Date')
                                    ->displayFormat('Y-m-d')
                                    ->default(now())
                                    ->maxDate(now()),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Employment Details')
                            ->schema([
                                Forms\Components\Select::make('employment_type')
                                    ->options([
                                        'full-time' => 'Full-Time',
                                        'part-time' => 'Part-Time',
                                        'contract' => 'Contract',
                                        'intern' => 'Intern',
                                    ])
                                    ->required()
                                    ->default('full-time'),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'on-leave' => 'On Leave',
                                        'terminated' => 'Terminated',
                                    ])
                                    ->required()
                                    ->default('active'),

                                Forms\Components\TextInput::make('salary')
                                    ->label('Salary')
                                    ->numeric()
                                    ->prefix('$')
                                    ->maxValue(10000000) 
                                    ->visible(fn () => auth()->user()->can('Manage payroll') || auth()->user()->hasRole('HR Manager')),
                            ])
                            ->columns(3),

                        Forms\Components\Section::make('Personal Information')
                            ->schema([
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('Date of Birth')
                                    ->displayFormat('Y-m-d')
                                    ->maxDate(now()->subYears(18)),

                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(10)
                                    ->rules(['regex:/^[0-9]{10}$/', 'min:10'])
                                    ->helperText('phone number should be 10 digits'),

                                Forms\Components\Textarea::make('address')
                                    ->maxLength(65535)
                                    ->columnSpan(2),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Emergency Contact')
                            ->schema([
                                Forms\Components\TextInput::make('emergency_contact_name')
                                    ->label('Name')
                                    ->maxLength(255)
                                    ->rules(['regex:/^[A-Za-z\s\-\'\.]+$/'])
                                    ->helperText('Only letters, spaces, hyphens, apostrophes, and periods allowed'),

                                Forms\Components\TextInput::make('emergency_contact_phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(10)
                                    ->rules(['regex:/^[0-9]{10}$/', 'min:10'])
                                    ->helperText('phone number should be 10 digits'),

                                Forms\Components\TextInput::make('emergency_contact_relationship')
                                    ->label('Relationship')
                                    ->maxLength(255)
                                    ->rules(['regex:/^[A-Za-z\s\-\'\.]+$/'])
                                    ->helperText('Only letters, spaces, hyphens, apostrophes, and periods allowed'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('position.title')
                    ->label('Position')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('position.level')
                    ->label('Level')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('employment_type')
                    ->label('Type')
                    ->enum([
                        'full-time' => 'Full-Time',
                        'part-time' => 'Part-Time',
                        'contract' => 'Contract',
                        'intern' => 'Intern',
                    ])
                    ->colors([
                        'success' => 'full-time',
                        'warning' => 'part-time',
                        'primary' => 'contract',
                        'secondary' => 'intern',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->enum([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on-leave' => 'On Leave',
                        'terminated' => 'Terminated',
                    ])
                    ->colors([
                        'success' => 'active',
                        'warning' => 'on-leave',
                        'secondary' => 'inactive',
                        'danger' => 'terminated',
                    ]),

                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tenure_years')
                    ->label('Tenure')
                    ->suffix(' years')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),

                Tables\Filters\SelectFilter::make('position_id')
                    ->label('Position')
                    ->relationship('position', 'title'),

                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'full-time' => 'Full-Time',
                        'part-time' => 'Part-Time',
                        'contract' => 'Contract',
                        'intern' => 'Intern',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on-leave' => 'On Leave',
                        'terminated' => 'Terminated',
                    ])
                    ->default('active'),

                Tables\Filters\Filter::make('has_manager')
                    ->label('Has Manager')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('manager_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListEmployeeProfiles::route('/'),
            'create' => Pages\CreateEmployeeProfile::route('/create'),
            'view' => Pages\ViewEmployeeProfile::route('/{record}'),
            'edit' => Pages\EditEmployeeProfile::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'department', 'position', 'manager']);
    }
}
