<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceRecordResource\Pages;
use App\Models\AttendanceRecord;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Attendance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->default(fn () => auth()->id())
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\EditAttendanceRecord),

                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->displayFormat('Y-m-d')
                            ->default(today())
                            ->maxDate(today()),

                        Forms\Components\TimePicker::make('check_in')
                            ->label('Check In')
                            ->withoutSeconds(),

                        Forms\Components\TimePicker::make('check_out')
                            ->label('Check Out')
                            ->withoutSeconds()
                            ->after('check_in'),

                        Forms\Components\TextInput::make('total_hours')
                            ->label('Total Hours')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'half-day' => 'Half Day',
                                'on-leave' => 'On Leave',
                                'holiday' => 'Holiday',
                            ])
                            ->required()
                            ->default('present'),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->placeholder('Optional GPS location'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->time()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('check_out')
                    ->time()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Hours')
                    ->suffix(' hrs')
                    ->sortable()
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->enum([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'half-day' => 'Half Day',
                        'on-leave' => 'On Leave',
                        'holiday' => 'Holiday',
                    ])
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'secondary' => 'half-day',
                        'primary' => 'on-leave',
                        'secondary' => 'holiday',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'half-day' => 'Half Day',
                        'on-leave' => 'On Leave',
                        'holiday' => 'Holiday',
                    ]),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('my_attendance')
                    ->label('My Attendance')
                    ->query(fn (Builder $query): Builder => $query->where('user_id', auth()->id())),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-o-login')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (AttendanceRecord $record) {
                        $record->checkIn();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Checked In')
                            ->body('You have been checked in successfully.')
                            ->send();
                    })
                    ->visible(fn (AttendanceRecord $record) => 
                        !$record->check_in && 
                        $record->date->isToday() &&
                        $record->user_id === auth()->id()
                    ),

                Tables\Actions\Action::make('check_out')
                    ->label('Check Out')
                    ->icon('heroicon-o-logout')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (AttendanceRecord $record) {
                        $record->checkOut();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Checked Out')
                            ->body('You have been checked out successfully.')
                            ->send();
                    })
                    ->visible(fn (AttendanceRecord $record) => 
                        $record->check_in && 
                        !$record->check_out && 
                        $record->date->isToday() &&
                        $record->user_id === auth()->id()
                    ),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListAttendanceRecords::route('/'),
            'create' => Pages\CreateAttendanceRecord::route('/create'),
            'view' => Pages\ViewAttendanceRecord::route('/{record}'),
            'edit' => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user is not HR, show only own attendance
        if (!auth()->user()->can('Manage attendance')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
