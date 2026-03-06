<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Models\BacklogItem;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                            Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->relationship('project', 'name')
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateHydrated(function ($component, $state) {
                                        if (!$state && request()->get('project')) {
                                            $component->state(request()->get('project'));
                                        }
                                    })
                                    ->disabled(fn ($livewire) => request()->has('project'))
                                    ->extraAttributes(fn () => request()->has('project') ? ['style' => 'pointer-events:none'] : [])
                                    ->afterStateUpdated(function ($get, $set) {
                                        $project = Project::where('id', $get('project_id'))->first();
                                        if ($project?->status_type === 'custom') {
                                            $set(
                                                'status_id',
                                                TicketStatus::where('project_id', $project->id)
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        } else {
                                            $set(
                                                'status_id',
                                                TicketStatus::whereNull('project_id')
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        }
                                    })
                                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                                        ->orWhereHas('users', function ($query) {
                                            return $query->where('users.id', auth()->user()->id);
                                        })->pluck('name', 'id')->toArray()
                                    )
                                    ->default(fn ($livewire) => request()->get('project_id'))
                                    ->disabled(fn() => request()->has('project_id'))
                                    ->required(),
                                Forms\Components\Select::make('epic_id')
                                    ->label(__('Epic'))
                                    ->searchable()
                                    ->reactive()
                                    ->options(function ($get,) {
                                        return Epic::where('project_id', $get('project_id'))->pluck('name', 'id')->toArray();
                                    }),
                            ]),
                            
                        // Backlog Integration Section
                        Forms\Components\Section::make('Backlog Integration')
                            ->description(__('Link this ticket to a specific backlog hierarchy'))
                            ->collapsible()
                            ->collapsed(fn() => !request()->has('backlog_parent'))
                            ->visible(fn() => true)
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('backlog_epic_id')
                                            ->label(__('Backlog Epic'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($set) {
                                                $set('backlog_feature_id', null);
                                                $set('backlog_user_story_id', null);
                                            })
                                            ->options(function ($get) {
                                                $projectId = $get('project_id');
                                                if (!$projectId) return [];
                                                return BacklogItem::where('project_id', $projectId)
                                                    ->where('type', BacklogItem::TYPE_EPIC)
                                                    ->pluck('title', 'id')
                                                    ->toArray();
                                            })
                                            ->helperText(__('Select the Epic this task belongs to')),
                                        
                                        Forms\Components\Select::make('backlog_feature_id')
                                            ->label(__('Backlog Feature'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($set) {
                                                $set('backlog_user_story_id', null);
                                            })
                                            ->options(function ($get) {
                                                $epicId = $get('backlog_epic_id');
                                                if (!$epicId) {
                                                    return ['_placeholder' => 'Please select an Epic first'];
                                                }
                                                $features = BacklogItem::where('parent_id', $epicId)
                                                    ->where('type', BacklogItem::TYPE_FEATURE)
                                                    ->pluck('title', 'id')
                                                    ->toArray();
                                                
                                                if (empty($features)) {
                                                    return ['_placeholder' => 'No Features found under this Epic'];
                                                }
                                                return $features;
                                            })
                                            ->helperText(function ($get) {
                                                $epicId = $get('backlog_epic_id');
                                                if (!$epicId) {
                                                    return '⚠️ Select an Epic first';
                                                }
                                                return 'Select the Feature under the Epic';
                                            })
                                            ->placeholder('Select a Feature'),
                                        
                                        Forms\Components\Select::make('backlog_user_story_id')
                                            ->label(__('Backlog User Story'))
                                            ->searchable()
                                            ->reactive()
                                            ->options(function ($get) {
                                                $featureId = $get('backlog_feature_id');
                                                if (!$featureId) {
                                                    return ['_placeholder' => 'Please select a Feature first'];
                                                }
                                                $stories = BacklogItem::where('parent_id', $featureId)
                                                    ->where('type', BacklogItem::TYPE_USER_STORY)
                                                    ->pluck('title', 'id')
                                                    ->toArray();
                                                
                                                if (empty($stories)) {
                                                    return ['_placeholder' => 'No User Stories found under this Feature'];
                                                }
                                                return $stories;
                                            })
                                            ->helperText(function ($get) {
                                                $featureId = $get('backlog_feature_id');
                                                if (!$featureId) {
                                                    return '⚠️ Select a Feature first';
                                                }
                                                return 'Select the User Story under the Feature';
                                            })
                                            ->placeholder('Select a User Story'),
                                    ]),
                                    
                                Forms\Components\Placeholder::make('backlog_info')
                                    ->label('')
                                    ->content(new HtmlString('
                                        <div class="text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                            <strong>How it works:</strong><br>
                                            1. Select Epic → Feature → User Story hierarchy<br>
                                            2. A backlog Task item will be created automatically<br>
                                            3. It will be linked to this ticket and appear in the backlog tree
                                        </div>
                                    ')),
                            ]),
                            
                        Forms\Components\Card::make()
                            ->schema([
                                Forms\Components\Grid::make()
                                 ->columns(12)
                                    ->columnSpan(2)                               
                                    ->schema([
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('Ticket code'))
                                            ->visible(fn() => true)
                                            ->columnSpan(2)
                                            ->disabled(),

                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Ticket name'))
                                            ->required()
                                            ->columnSpan(
                                                fn($livewire) => !($livewire instanceof CreateRecord) ? 10 : 12
                                            )
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Select::make('owner_id')
                                    ->label(__('Ticket owner'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->default(fn() => auth()->user()->id)
                                    ->required(),

                                Forms\Components\Select::make('responsible_id')
                                    ->label(__('Ticket responsible'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->multiple()
                                    ->searchable()
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('status_id')
                                            ->label(__('Ticket status'))
                                            ->searchable()
                                            ->options(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                            })
                                            ->default(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                }
                                            })
                                            ->required(),

                                        Forms\Components\Select::make('type_id')
                                            ->label(__('Ticket type'))
                                            ->searchable()
                                            ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketType::where('is_default', true)->first()?->id)
                                            ->required(),

                                        Forms\Components\Select::make('priority_id')
                                            ->label(__('Ticket priority'))
                                            ->searchable()
                                            ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketPriority::where('is_default', true)->first()?->id)
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('Ticket content'))
                            ->required()
                            ->columnSpan(2),

                        // AI Generation Section (Create only)
                        Forms\Components\Section::make('AI Task Generation')
                            ->description(__('Generate detailed tasks automatically using AI based on your prompt'))
                            ->visible(fn() => true)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\Toggle::make('ai_generate')
                                    ->label(__('Generate sub-tasks with AI'))
                                    ->helperText(__('When enabled, AI will analyze your prompt and generate detailed sub-tasks'))
                                    ->default(false)
                                    ->reactive()
                                    ->dehydrated(false),

                                Forms\Components\Textarea::make('ai_prompt')
                                    ->label(__('AI Prompt'))
                                    ->helperText(__('Describe what you want to accomplish. AI will analyze existing tickets to avoid duplicates.'))
                                    ->placeholder(__('Example: Implement user authentication with email verification, password reset, and 2FA support'))
                                    ->rows(4)
                                    ->visible(fn($get) => $get('ai_generate'))
                                    ->dehydrated(false),

                                Forms\Components\Select::make('ai_responsible_id')
                                    ->label(__('Assign AI-generated tasks to'))
                                    ->helperText(__('All AI-generated sub-tasks will be assigned to this user'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->visible(fn($get) => $get('ai_generate'))
                                    ->dehydrated(false),

                                Forms\Components\Placeholder::make('ai_info')
                                    ->label('')
                                    ->content(new HtmlString('<div class="text-sm text-gray-600"><strong>How it works:</strong><ul class="list-disc ml-4 mt-2"><li>AI analyzes your prompt and existing project tickets</li><li>Generates hierarchical tasks with priorities and estimates</li><li>Automatically detects and skips duplicate tasks</li><li>Creates sub-tasks linked to this parent ticket</li></ul></div>'))
                                    ->visible(fn($get) => $get('ai_generate')),
                            ])
                            ->columnSpan(2),

                        Forms\Components\Grid::make()
                            ->columnSpan(2)
                            ->columns(12)
                            ->schema([
                                Forms\Components\TextInput::make('estimation')
                                    ->label(__('Estimation time'))
                                    ->numeric()
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Repeater::make('relations')
                            ->itemLabel(function (array $state) {
                                $ticketRelation = TicketRelation::find($state['id'] ?? 0);
                                if ($ticketRelation) {
                                    return __(config('system.tickets.relations.list.' . $ticketRelation->type))
                                        . ' '
                                        . $ticketRelation->relation->name
                                        . ' (' . $ticketRelation->relation->code . ')';
                                }
                                return null;
                            })
                            ->relationship()
                            ->collapsible()
                            ->collapsed()
                            ->orderable()
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Relation type'))
                                            ->required()
                                            ->searchable()
                                            ->options(config('system.tickets.relations.list'))
                                            ->default(fn() => config('system.tickets.relations.default')),

                                        Forms\Components\Select::make('relation_id')
                                            ->label(__('Related ticket'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(2)
                                            ->options(function ($livewire) {
                                                $query = Ticket::query();
                                                if ($livewire instanceof EditRecord && $livewire->record) {
                                                    $query->where('id', '<>', $livewire->record->id);
                                                }
                                                return $query->get()->pluck('name', 'id')->toArray();
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function tableColumns(bool $withProject = true): array
    {
        $columns = [];
        if ($withProject) {
            $columns[] = Tables\Columns\TextColumn::make('project.name')
                ->label(__('Project'))
                ->sortable()
                ->searchable();
        }
        $columns = array_merge($columns, [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket name'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Owner'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->owner]))
                ->searchable(),

            Tables\Columns\TextColumn::make('responsible.name')
                ->label(__('Responsible'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->responsible]))
                ->searchable(),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md"
                                    style="background-color: ' . $record->status->color . '"></span>
                                <span>' . $record->status->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('type.name')
                ->label(__('Type'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.ticket-type', ['state' => $record->type])
                )
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md"
                                    style="background-color: ' . $record->priority->color . '"></span>
                                <span>' . $record->priority->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Created at'))
                ->dateTime()
                ->sortable()
                ->searchable(),
        ]);
        return $columns;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->multiple()
                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                        ->orWhereHas('users', function ($query) {
                            return $query->where('users.id', auth()->user()->id);
                        })->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('owner_id')
                    ->label(__('Owner'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('responsible_id')
                    ->label(__('Responsible'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('type_id')
                    ->label(__('Type'))
                    ->multiple()
                    ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('priority_id')
                    ->label(__('Priority'))
                    ->multiple()
                    ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }


        public static function getFormSchema(): array
        {
            return static::form(app(Form::class))->getSchema();
        }
}

