<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Leave Requests';

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
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\EditLeaveRequest),

                        Forms\Components\Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->relationship('leaveType', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->displayFormat('Y-m-d')
                            ->minDate(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::calculateDays($state, $get('end_date'), $set);
                            }),

                        Forms\Components\DatePicker::make('end_date')
                            ->required()
                            ->displayFormat('Y-m-d')
                            ->minDate(fn (callable $get) => $get('start_date') ?? now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::calculateDays($get('start_date'), $state, $set);
                            }),

                        Forms\Components\TextInput::make('days_count')
                            ->label('Days Count')
                            ->required()
                            ->numeric()
                            ->minValue(0.5)
                            ->step(0.5)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($livewire) => $livewire instanceof Pages\EditLeaveRequest),

                        Forms\Components\Textarea::make('reason')
                            ->maxLength(65535)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('notes')
                            ->label('Admin Notes')
                            ->maxLength(65535)
                            ->columnSpan(2)
                            ->visible(fn () => auth()->user()->can('Update leave request')),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function calculateDays($startDate, $endDate, callable $set)
    {
        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $days = $start->diffInDays($end) + 1;
            $set('days_count', $days);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->colors([
                        'primary',
                    ]),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_count')
                    ->label('Days')
                    ->suffix(' days')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->enum([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->default('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship('leaveType', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending'),

                Tables\Filters\Filter::make('my_requests')
                    ->label('My Requests')
                    ->query(fn (Builder $query): Builder => $query->where('user_id', auth()->id())),

                Tables\Filters\Filter::make('my_team_requests')
                    ->label('My Team Requests')
                    ->query(function (Builder $query): Builder {
                        $directReportIds = auth()->user()->directReports()->pluck('user_id');
                        return $query->whereIn('user_id', $directReportIds);
                    })
                    ->visible(fn () => auth()->user()->directReports()->exists()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record) {
                        $record->approve(auth()->id());
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Leave Request Approved')
                            ->body('The leave request has been approved successfully.')
                            ->send();
                    })
                    ->visible(fn (LeaveRequest $record) => 
                        $record->status === 'pending' && 
                        auth()->user()->can('approve', $record)
                    ),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->reject(auth()->id(), $data['rejection_reason']);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Leave Request Rejected')
                            ->body('The leave request has been rejected.')
                            ->send();
                    })
                    ->visible(fn (LeaveRequest $record) => 
                        $record->status === 'pending' && 
                        auth()->user()->can('reject', $record)
                    ),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending'),
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
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'view' => Pages\ViewLeaveRequest::route('/{record}'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user is not HR, show only own requests and team requests
        if (!auth()->user()->can('View all employees')) {
            $directReportIds = auth()->user()->directReports()->pluck('user_id')->toArray();
            $query->where(function ($q) use ($directReportIds) {
                $q->where('user_id', auth()->id())
                  ->orWhereIn('user_id', $directReportIds);
            });
        }

        return $query;
    }
}
