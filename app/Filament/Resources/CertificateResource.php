<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Services\TemplateRenderer;
use App\Services\PdfGenerator;
use Illuminate\Support\Facades\Storage;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-badge-check';
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 23;
    protected static ?string $navigationLabel = 'Certificates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')->relationship('user','name')->required()->searchable(),
                Forms\Components\Select::make('certificate_template_id')->label('Template')->options(CertificateTemplate::query()->pluck('name','id'))->required()->searchable(),
                Forms\Components\TextInput::make('type')->default('experience')->required(),
                Forms\Components\DatePicker::make('issued_on')->default(today()),
                Forms\Components\TextInput::make('issued_by')->default(config('app.name')),
                Forms\Components\KeyValue::make('data')->keyLabel('Key')->valueLabel('Value')->columnSpanFull(),
                Forms\Components\Textarea::make('html_snapshot')->columnSpanFull()->rows(10)->disabled()->dehydrated(false),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('issued_on')->date()->sortable(),
                Tables\Columns\IconColumn::make('html_snapshot')->label('Snapshot')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('generate')
                    ->label('Generate HTML')
                    ->icon('heroicon-o-sparkles')
                    ->action(function (Certificate $record) {
                        $data = array_merge([
                            'user' => ['name' => $record->user->name],
                            'issued_on' => optional($record->issued_on)?->toDateString(),
                            'issued_by' => $record->issued_by,
                        ], $record->data ?? []);
                        $html = $record->template?->html ?? '<h2>Certificate</h2>';
                        $record->html_snapshot = TemplateRenderer::render($html, $data);
                        $record->save();
                        \Filament\Notifications\Notification::make()->title('Certificate HTML generated')->success()->send();
                    }),
                Tables\Actions\Action::make('generate_pdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-download')
                    ->requiresConfirmation()
                    ->action(function (Certificate $record) {
                        $data = array_merge([
                            'user' => ['name' => $record->user->name],
                            'issued_on' => optional($record->issued_on)?->toDateString(),
                            'issued_by' => $record->issued_by,
                        ], $record->data ?? []);
                        $html = $record->template?->html ?? '<h2>Certificate</h2>';
                        $rendered = TemplateRenderer::render($html, $data);

                        $pdf = PdfGenerator::htmlToPdf($rendered, 'A4', 'portrait');
                        $dir = 'certificates/' . $record->user_id;
                        $filename = 'certificate-' . $record->id . '.pdf';
                        Storage::disk('public')->put($dir . '/' . $filename, $pdf);
                        $record->pdf_path = $dir . '/' . $filename;
                        $record->html_snapshot = $rendered;
                        $record->save();

                        \Filament\Notifications\Notification::make()->title('PDF generated')->success()->send();
                    }),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download')
                    ->icon('heroicon-o-download')
                    ->visible(fn (Certificate $record) => !empty($record->pdf_path) && Storage::disk('public')->exists($record->pdf_path))
                    ->url(fn (Certificate $record) => Storage::disk('public')->url($record->pdf_path))
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
