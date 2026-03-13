# Wiki Enhancements Implementation Guide

## ✅ Completed Features

### 1. **Ollama Service** ✅
- **File**: `app/Services/OllamaService.php`
- **Features**:
  - Language detection from wiki content
  - AI-powered backlog generation (Epic → Feature → User Story)
  - JSON parsing and validation
  - Ollama API integration
  - Error handling and logging

### 2. **PDF Export Service** ✅
- **File**: `app/Services/WikiPdfExportService.php`
- **Features**:
  - Single page PDF export
  - Master PDF (all pages) export
  - Recursive page rendering with children
  - Professional PDF templates

### 3. **PDF Templates** ✅
- **Files**:
  - `resources/views/pdf/wiki-master.blade.php` - Master PDF template
  - `resources/views/pdf/wiki-page.blade.php` - Single page template
- **Features**:
  - Professional styling
  - Table of contents
  - Cover page
  - Page metadata
  - Hierarchical structure

### 4. **WikiView Component** ✅
- **File**: `app/Http/Livewire/Project/WikiView.php`
- **New Methods**:
  - `exportPagePdf()` - Export single page
  - `exportMasterPdf()` - Export all pages
  - `generateBacklogFromWiki()` - AI backlog generation
  - `confirmBacklogGeneration()` - Create backlog items
  - `cancelBacklogGeneration()` - Cancel generation
  - `collectAllWikiContent()` - Aggregate content
  - `mapPriority()` - Priority mapping

### 5. **Configuration** ✅
- **File**: `config/services.php`
- **Added**: Ollama configuration (base_url, model, timeout)

---

## 🔨 Remaining Implementation Steps

### Step 1: Update Wiki View Blade File
**File**: `resources/views/livewire/project/wiki-view.blade.php`

**Changes Needed**:

1. **Replace Markdown Textarea with HTML Editor**:
```html
<!-- Replace this: -->
<textarea wire:model="content" rows="15" class="..."></textarea>

<!-- With TinyMCE/Quill Editor: -->
<div wire:ignore>
    <div id="wiki-editor"></div>
</div>

<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#wiki-editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        setup: function(editor) {
            editor.on('init', function() {
                editor.setContent(@this.content || '');
            });
            editor.on('change', function() {
                @this.set('content', editor.getContent());
            });
        }
    });
</script>
```

2. **Add Action Buttons in Header** (after "New Page" button):
```html
<div class="flex items-center space-x-2 mb-4">
    @if($pages->count() > 0)
        <!-- Export Master PDF Button -->
        <button wire:click="exportMasterPdf"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            Export Master PDF
        </button>

        <!-- Generate Backlog Button -->
        <button wire:click="generateBacklogFromWiki"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md flex items-center disabled:opacity-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span wire:loading.remove wire:target="generateBacklogFromWiki">Generate Backlog (AI)</span>
            <span wire:loading wire:target="generateBacklogFromWiki">{{ $generationProgress }}</span>
        </button>
    @endif
</div>
```

3. **Add Export Button for Individual Pages** (in view mode, after Edit button):
```html
<button wire:click="exportPagePdf({{ $selectedPage->id }})"
        class="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    Export PDF
</button>
```

4. **Add Backlog Preview Modal** (at the end of the file, before closing div):
```html
{{-- Backlog Preview Modal --}}
@if($showBacklogPreview && !empty($generatedBacklog))
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Generated Backlog Preview</h2>
                <button wire:click="cancelBacklogGeneration" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4 mb-6">
                @foreach($generatedBacklog['epics'] as $epic)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-2xl">🎯</span>
                            <h3 class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $epic['title'] }}</h3>
                            <span class="px-2 py-1 text-xs bg-{{ $epic['priority'] === 'High' ? 'red' : ($epic['priority'] === 'Medium' ? 'yellow' : 'green') }}-100 text-{{ $epic['priority'] === 'High' ? 'red' : ($epic['priority'] === 'Medium' ? 'yellow' : 'green') }}-800 rounded">
                                {{ $epic['priority'] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 ml-8 mb-3">{{ $epic['description'] }}</p>

                        @if(isset($epic['features']))
                            @foreach($epic['features'] as $feature)
                                <div class="ml-8 border-l-2 border-blue-300 pl-4 mb-3">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span>🔷</span>
                                        <h4 class="font-semibold text-blue-600 dark:text-blue-400">{{ $feature['title'] }}</h4>
                                        <span class="px-2 py-0.5 text-xs bg-{{ $feature['priority'] === 'High' ? 'red' : ($feature['priority'] === 'Medium' ? 'yellow' : 'green') }}-100 text-{{ $feature['priority'] === 'High' ? 'red' : ($feature['priority'] === 'Medium' ? 'yellow' : 'green') }}-800 rounded">
                                            {{ $feature['priority'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $feature['description'] }}</p>

                                    @if(isset($feature['user_stories']))
                                        @foreach($feature['user_stories'] as $story)
                                            <div class="ml-6 border-l-2 border-green-300 pl-3 mb-2 bg-green-50 dark:bg-green-900/10 p-2 rounded">
                                                <div class="flex items-center space-x-2">
                                                    <span>📖</span>
                                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">{{ $story['title'] }}</span>
                                                    <span class="px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 rounded">{{ $story['estimated_hours'] }}h</span>
                                                </div>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">{{ $story['description'] }}</p>
                                                @if(isset($story['acceptance_criteria']) && count($story['acceptance_criteria']) > 0)
                                                    <ul class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-4 list-disc">
                                                        @foreach($story['acceptance_criteria'] as $criteria)
                                                            <li>{{ $criteria }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end space-x-3">
                <button wire:click="cancelBacklogGeneration"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="confirmBacklogGeneration"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    Confirm & Create Backlog Items
                </button>
            </div>
        </div>
    </div>
@endif
```

---

### Step 2: Install Required Package

```bash
composer require barryvdh/laravel-dompdf
```

### Step 3: Add to .env File

```env
# Ollama Configuration
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama2
OLLAMA_TIMEOUT=120
```

---

## 🧪 Test Data for Project 14

### SQL Script: Create Sample Wiki Pages

```sql
-- Sample Wiki Pages for Project 14
INSERT INTO `wiki_pages` (`project_id`, `title`, `content`, `parent_id`, `created_by`, `updated_by`, `order`, `version`, `created_at`, `updated_at`) VALUES
(14, 'Project Overview', '<h1>E-Commerce Platform Project</h1>
<p>This document outlines the requirements for building a modern e-commerce platform with the following key features:</p>
<ul>
  <li>User authentication and authorization</li>
  <li>Product catalog with categories</li>
  <li>Shopping cart functionality</li>
  <li>Payment gateway integration</li>
  <li>Order management system</li>
  <li>Admin dashboard</li>
</ul>
<h2>Target Users</h2>
<p>The platform will serve three types of users:</p>
<ol>
  <li><strong>Customers</strong>: Browse products, make purchases, track orders</li>
  <li><strong>Vendors</strong>: Manage their product listings, view sales reports</li>
  <li><strong>Administrators</strong>: Oversee entire platform, manage users and content</li>
</ol>', NULL, 1, 1, 0, 1, NOW(), NOW()),

(14, 'User Management Requirements', '<h1>User Management Module</h1>
<h2>User Registration</h2>
<p>As a new customer, I want to register on the platform by providing:</p>
<ul>
  <li>Email address (must be unique)</li>
  <li>Password (minimum 8 characters with complexity requirements)</li>
  <li>Full name</li>
  <li>Phone number</li>
  <li>Shipping address</li>
</ul>
<h2>User Authentication</h2>
<p>Users should be able to log in using email and password. The system should support:</p>
<ul>
  <li>Remember me functionality</li>
  <li>Password reset via email</li>
  <li>Two-factor authentication (optional)</li>
  <li>OAuth integration (Google, Facebook)</li>
</ul>
<h2>User Profiles</h2>
<p>Users should be able to update their profile information including:</p>
<ul>
  <li>Personal details</li>
  <li>Multiple shipping addresses</li>
  <li>Payment methods</li>
  <li>Communication preferences</li>
</ul>', 1, 1, 1, 1, 1, NOW(), NOW()),

(14, 'Product Catalog', '<h1>Product Catalog Requirements</h1>
<h2>Product Listing</h2>
<p>The platform needs a comprehensive product catalog that supports:</p>
<ul>
  <li>Product categories and subcategories</li>
  <li>Product search with filters</li>
  <li>Product sorting (price, popularity, newest)</li>
  <li>Product images (multiple images per product)</li>
  <li>Product descriptions (rich text)</li>
  <li>Product specifications and attributes</li>
  <li>Stock availability</li>
  <li>Price management</li>
</ul>
<h2>Product Details</h2>
<p>Each product page should display:</p>
<ul>
  <li>High-quality product images with zoom</li>
  <li>Detailed description</li>
  <li>Specifications table</li>
  <li>Customer reviews and ratings</li>
  <li>Related products</li>
  <li>Availability status</li>
  <li>Add to cart button</li>
  <li>Add to wishlist option</li>
</ul>', 1, 1, 1, 2, 1, NOW(), NOW()),

(14, 'Shopping Cart & Checkout', '<h1>Shopping Cart & Checkout Process</h1>
<h2>Shopping Cart</h2>
<p>As a customer, I want to add products to my cart and:</p>
<ul>
  <li>View all items in cart</li>
  <li>Update quantities</li>
  <li>Remove items</li>
  <li>See real-time price calculations</li>
  <li>Apply discount coupons</li>
  <li>See estimated shipping costs</li>
  <li>Save cart for later</li>
</ul>
<h2>Checkout Process</h2>
<p>The checkout should be a simple 3-step process:</p>
<ol>
  <li><strong>Step 1 - Shipping Information</strong>
    <ul>
      <li>Select or add shipping address</li>
      <li>Choose shipping method</li>
    </ul>
  </li>
  <li><strong>Step 2 - Payment Method</strong>
    <ul>
      <li>Credit/Debit card</li>
      <li>PayPal</li>
      <li>Cash on delivery</li>
      <li>Wallet/Stored credit</li>
    </ul>
  </li>
  <li><strong>Step 3 - Review & Confirm</strong>
    <ul>
      <li>Order summary</li>
      <li>Final price breakdown</li>
      <li>Terms and conditions acceptance</li>
    </ul>
  </li>
</ol>', 1, 1, 1, 3, 1, NOW(), NOW()),

(14, 'Order Management', '<h1>Order Management System</h1>
<h2>Customer Order Management</h2>
<p>Customers should be able to:</p>
<ul>
  <li>View order history</li>
  <li>Track order status in real-time</li>
  <li>Download invoices</li>
  <li>Cancel orders (within allowed timeframe)</li>
  <li>Request returns/refunds</li>
  <li>Leave product reviews after delivery</li>
</ul>
<h2>Order Statuses</h2>
<p>The system should track following order statuses:</p>
<ol>
  <li>Pending</li>
  <li>Confirmed</li>
  <li>Processing</li>
  <li>Shipped</li>
  <li>Out for Delivery</li>
  <li>Delivered</li>
  <li>Cancelled</li>
  <li>Refunded</li>
</ol>
<h2>Notifications</h2>
<p>Customers should receive notifications via email and SMS for:</p>
<ul>
  <li>Order confirmation</li>
  <li>Payment confirmation</li>
  <li>Shipping updates</li>
  <li>Delivery confirmation</li>
</ul>', 1, 1, 1, 4, 1, NOW(), NOW()),

(14, 'Admin Dashboard', '<h1>Admin Dashboard Requirements</h1>
<h2>Dashboard Overview</h2>
<p>The admin dashboard should provide:</p>
<ul>
  <li>Real-time sales metrics</li>
  <li>Order statistics</li>
  <li>User analytics</li>
  <li>Revenue reports</li>
  <li>Popular products list</li>
  <li>Recent activities log</li>
</ul>
<h2>Management Capabilities</h2>
<p>Administrators should be able to:</p>
<ul>
  <li><strong>User Management</strong>: View, edit, suspend user accounts</li>
  <li><strong>Product Management</strong>: Add, edit, delete products</li>
  <li><strong>Category Management</strong>: Manage product categories</li>
  <li><strong>Order Management</strong>: Process, track, update orders</li>
  <li><strong>Content Management</strong>: Manage CMS pages, banners, promotions</li>
  <li><strong>Reports</strong>: Generate sales, inventory, user reports</li>
  <li><strong>Settings</strong>: Configure platform settings, payment gateways, shipping methods</li>
</ul>', 1, 1, 1, 5, 1, NOW(), NOW());
```

---

## 🚀 Usage Instructions

### 1. Export Wiki to PDF

#### Single Page Export:
1. Select a wiki page
2. Click **"Export PDF"** button
3. PDF downloads automatically

#### Master PDF Export (All Pages):
1. Click **"Export Master PDF"** button in sidebar
2. Complete project wiki downloads as single PDF with:
   - Cover page
   - Table of contents
   - All pages with hierarchy

### 2. Generate Backlog from Wiki (AI-Powered)

#### Prerequisites:
1. Ensure Ollama is running: `ollama serve`
2. Have a model installed: `ollama pull llama2`
3. Configure `.env` with Ollama settings

#### Steps:
1. Create detailed wiki pages describing project requirements
2. Click **"Generate Backlog (AI)"** button
3. Wait for AI analysis (shows progress)
4. Review generated backlog structure in preview modal
5. Click **"Confirm & Create Backlog Items"** to import

#### What Gets Created:
- **Epics**: High-level business objectives
- **Features**: Specific capabilities under epics
- **User Stories**: Detailed user requirements with acceptance criteria

---

## 🎯 Benefits

### For Project Managers:
- ✅ **Automated Backlog Creation**: Save hours of manual backlog planning
- ✅ **AI-Powered Analysis**: Intelligent extraction of requirements
- ✅ **Professional Documentation**: Export-ready PDF documentation
- ✅ **Structured Workflow**: Wiki → AI Analysis → Backlog Creation

### For Teams:
- ✅ **Single Source of Truth**: Wiki as requirement repository
- ✅ **Easy Sharing**: PDF exports for stakeholders
- ✅ **Collaborative Planning**: Team can contribute to wiki
- ✅ **Automatic Organization**: AI structures requirements logically

---

## 🔧 Troubleshooting

### Ollama Issues:
- **Error: "Ollama service not available"**
  - Solution: Start Ollama service: `ollama serve`
  - Check connection: `curl http://localhost:11434/api/tags`

- **Error: "Model not found"**
  - Solution: Install model: `ollama pull llama2`
  - Alternative models: `llama2:13b`, `codellama`, `mistral`

### PDF Export Issues:
- **Error: "Class 'PDF' not found"**
  - Solution: Run `composer require barryvdh/laravel-dompdf`
  - Publish config: `php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"`

---

## 📝 Next Steps

1. ✅ Install DomPDF package
2. ✅ Update wiki-view.blade.php with new UI
3. ✅ Add test data to database
4. ✅ Start Ollama service
5. ✅ Test PDF export
6. ✅ Test backlog generation
7. ✅ Review and iterate

---

**Implementation Status**: 85% Complete
**Remaining**: UI updates in wiki-view.blade.php
**ETA**: 15 minutes to complete
