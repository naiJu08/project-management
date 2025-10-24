<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WikiPageResource\Pages;
use App\Models\WikiPage;
use App\Models\Project;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class WikiPageResource extends Resource
{
    protected static ?string $model = WikiPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Wiki Pages');
    }

    public static function getPluralLabel(): string
    {
        return __('Wiki Pages');
    }

    public static function getLabel(): string
    {
        return __('Wiki Page');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('title')
                            ->label('Page Title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Page')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->nullable()
                            ->helperText('Select a parent page to create a sub-page'),

                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Content')
                            ->columnSpan('full')
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'edit',
                                'italic',
                                'link',
                                'orderedList',
                                'preview',
                                'strike',
                                'table',
                                'redo',
                                'undo',
                            ]),

                        Forms\Components\TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Page Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Parent Page')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('version')
                    ->label('Version')
                    ->colors([
                        'primary',
                    ]),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),

                Tables\Filters\Filter::make('root_pages')
                    ->label('Root Pages Only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('sub_pages')
                    ->label('Sub-pages Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListWikiPages::route('/'),
            'create' => Pages\CreateWikiPage::route('/create'),
            'view' => Pages\ViewWikiPage::route('/{record}'),
            'edit' => Pages\EditWikiPage::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('List wiki pages');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('Create wiki page');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('Update wiki page');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('Delete wiki page');
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('View wiki page');
    }
}
