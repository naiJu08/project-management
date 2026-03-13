<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\PayslipTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Default Payslip Template
        PayslipTemplate::updateOrCreate(
            ['slug' => 'default-payslip'],
            [
                'name' => 'Default Payslip',
                'description' => 'Clean two-column salary slip layout',
                'html' => <<<HTML
                <style>
                    body { font-family: ui-sans-serif, system-ui; }
                    .wrap{max-width:800px;margin:0 auto;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden}
                    .head{background:#111827;color:#fff;padding:16px}
                    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
                    .sec{padding:16px}
                    table{width:100%;border-collapse:collapse}
                    th,td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}
                    .tot{font-weight:bold}
                </style>
                <div class="wrap">
                    <div class="head">
                        <h2>Payslip - {{ period_start }} to {{ period_end }}</h2>
                        <div>{{ user.name }} ({{ user.employee_code }})</div>
                    </div>
                    <div class="sec grid">
                        <div>
                            <h3>Earnings</h3>
                            <table>
                                <tr><td>Basic</td><td class="tot">{{ basic }}</td></tr>
                                <!-- Render allowances if provided -->
                            </table>
                        </div>
                        <div>
                            <h3>Deductions</h3>
                            <table>
                                <tr><td>Standard</td><td>0.00</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="sec">
                        <table>
                            <tr><th>Gross</th><td>{{ gross }}</td></tr>
                            <tr><th>Net Pay</th><td class="tot">{{ net }}</td></tr>
                        </table>
                    </div>
                </div>
                HTML,
                'placeholders' => [
                    'user.name' => 'Employee full name',
                    'user.employee_code' => 'Employee code',
                    'period_start' => 'Pay period start date',
                    'period_end' => 'Pay period end date',
                    'basic' => 'Basic salary amount',
                    'gross' => 'Gross salary amount',
                    'net' => 'Net salary amount',
                ],
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Default Experience Certificate Template
        CertificateTemplate::updateOrCreate(
            ['slug' => 'default-experience-certificate'],
            [
                'name' => 'Experience Certificate',
                'description' => 'Standard experience certificate template',
                'html' => <<<HTML
                <style>
                    body { font-family: ui-sans-serif, system-ui; }
                    .wrap{max-width:800px;margin:0 auto;border:1px solid #e5e7eb;border-radius:8px;padding:24px}
                    .title{text-align:center;font-size:24px;font-weight:700;margin-bottom:16px}
                    .meta{margin:12px 0;color:#374151}
                </style>
                <div class="wrap">
                    <div class="title">Experience Certificate</div>
                    <p>To whom it may concern,</p>
                    <p class="meta">This is to certify that <strong>{{ user.name }}</strong> was employed with <strong>{{ company.name }}</strong> from <strong>{{ employment.start_date }}</strong> to <strong>{{ employment.end_date }}</strong> as <strong>{{ employment.position }}</strong>.</p>
                    <p>During their tenure, {{ user.name }} exhibited exemplary performance and professionalism.</p>
                    <p class="meta">Issued on: {{ issued_on }}</p>
                    <p>Authorized Signatory: {{ issued_by }}</p>
                </div>
                HTML,
                'placeholders' => [
                    'user.name' => 'Employee full name',
                    'company.name' => 'Company name',
                    'employment.start_date' => 'Employment start date',
                    'employment.end_date' => 'Employment end date',
                    'employment.position' => 'Position title',
                    'issued_on' => 'Issuance date',
                    'issued_by' => 'Issuer name',
                ],
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
