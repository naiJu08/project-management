<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayslipTemplateResource\Pages;
use App\Models\PayslipTemplate;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PayslipTemplateResource extends Resource
{
    protected static ?string $model = PayslipTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Payslip Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Textarea::make('html')
                    ->label('Template HTML')
                    ->rows(18)
                    ->helperText('Use placeholders like {{ user.name }}, {{ period_start }}, {{ basic }}, {{ net }}')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\KeyValue::make('placeholders')
                    ->keyLabel('Placeholder key (e.g. user.name)')
                    ->valueLabel('Description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_default')->inline(false),
                Forms\Components\Toggle::make('is_active')->inline(false)->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('slug')->searchable(),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PayslipTemplate $record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayslipTemplates::route('/'),
            'create' => Pages\CreatePayslipTemplate::route('/create'),
            'view' => Pages\ViewPayslipTemplate::route('/{record}'),
            'edit' => Pages\EditPayslipTemplate::route('/{record}/edit'),
        ];
    }
}
