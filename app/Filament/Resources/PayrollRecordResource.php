<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollRecordResource\Pages;
use App\Models\PayrollRecord;
use App\Models\PayslipTemplate;
use App\Services\TemplateRenderer;
use App\Services\PdfGenerator;
use Illuminate\Support\Facades\Storage;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PayrollRecordResource extends Resource
{
    protected static ?string $model = PayrollRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 22;
    protected static ?string $navigationLabel = 'Payroll Records';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->required()->searchable(),
                Forms\Components\DatePicker::make('period_start')->required(),
                Forms\Components\DatePicker::make('period_end')->required(),
                Forms\Components\TextInput::make('basic')->numeric()->required(),
                Forms\Components\KeyValue::make('allowances')->keyLabel('Name')->valueLabel('Amount'),
                Forms\Components\KeyValue::make('deductions')->keyLabel('Name')->valueLabel('Amount'),
                Forms\Components\TextInput::make('gross')->numeric()->helperText('Optional; auto-calc in future'),
                Forms\Components\TextInput::make('net')->numeric()->helperText('Optional; auto-calc in future'),
                Forms\Components\Select::make('payslip_template_id')->label('Template')->options(PayslipTemplate::query()->pluck('name','id'))->searchable(),
                Forms\Components\Textarea::make('html_snapshot')->columnSpanFull()->rows(10)->disabled()->dehydrated(false),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('period_start')->date()->sortable(),
                Tables\Columns\TextColumn::make('net')->money('inr', true)->sortable(),
                Tables\Columns\IconColumn::make('html_snapshot')->label('Snapshot')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->action(function (PayrollRecord $record) {
                        $data = [
                            'user' => [
                                'name' => $record->user->name,
                                'employee_code' => optional($record->user->employeeProfile)->employee_code,
                            ],
                            'period_start' => $record->period_start?->toDateString(),
                            'period_end' => $record->period_end?->toDateString(),
                            'basic' => $record->basic,
                            'allowances' => $record->allowances,
                            'deductions' => $record->deductions,
                            'gross' => $record->gross,
                            'net' => $record->net,
                        ];
                        $html = $record->template?->html ?? '<h2>Payslip</h2>';
                        $record->html_snapshot = TemplateRenderer::render($html, $data);
                        $record->generated_at = now();
                        $record->save();
                        \Filament\Notifications\Notification::make()->title('Preview generated')->success()->send();
                    }),
                Tables\Actions\Action::make('generate_pdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-download')
                    ->requiresConfirmation()
                    ->action(function (PayrollRecord $record) {
                        // Prepare HTML
                        $data = [
                            'user' => [
                                'name' => $record->user->name,
                                'employee_code' => optional($record->user->employeeProfile)->employee_code,
                            ],
                            'period_start' => $record->period_start?->toDateString(),
                            'period_end' => $record->period_end?->toDateString(),
                            'basic' => $record->basic,
                            'allowances' => $record->allowances,
                            'deductions' => $record->deductions,
                            'gross' => $record->gross,
                            'net' => $record->net,
                        ];
                        $html = $record->template?->html ?? '<h2>Payslip</h2>';
                        $rendered = TemplateRenderer::render($html, $data);

                        // Generate PDF
                        $pdf = PdfGenerator::htmlToPdf($rendered, 'A4', 'portrait');
                        $dir = 'payslips/' . $record->user_id;
                        $filename = 'payslip-' . $record->id . '.pdf';
                        Storage::disk('public')->put($dir . '/' . $filename, $pdf);
                        $record->pdf_path = $dir . '/' . $filename;
                        $record->html_snapshot = $rendered;
                        $record->generated_at = now();
                        $record->save();

                        \Filament\Notifications\Notification::make()->title('PDF generated')->success()->send();
                    }),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download')
                    ->icon('heroicon-o-download')
                    ->visible(fn (PayrollRecord $record) => !empty($record->pdf_path) && Storage::disk('public')->exists($record->pdf_path))
                    ->url(fn (PayrollRecord $record) => Storage::disk('public')->url($record->pdf_path))
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
            'index' => Pages\ListPayrollRecords::route('/'),
            'create' => Pages\CreatePayrollRecord::route('/create'),
            'edit' => Pages\EditPayrollRecord::route('/{record}/edit'),
        ];
    }
}
