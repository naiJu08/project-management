<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $project->name }} - Wiki Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        
        .cover-page {
            page-break-after: always;
            text-align: center;
            padding-top: 200px;
        }
        
        .cover-page h1 {
            font-size: 32pt;
            margin-bottom: 20px;
            color: #2563eb;
        }
        
        .cover-page h2 {
            font-size: 20pt;
            color: #64748b;
            margin-bottom: 40px;
        }
        
        .cover-page .meta {
            font-size: 12pt;
            color: #94a3b8;
            margin-top: 60px;
        }
        
        .toc {
            page-break-after: always;
            padding: 40px 20px;
        }
        
        .toc h2 {
            font-size: 22pt;
            margin-bottom: 30px;
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        
        .toc ul {
            list-style: none;
        }
        
        .toc li {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .toc li.level-2 {
            padding-left: 40px;
            font-size: 10pt;
        }
        
        .toc a {
            color: #1e40af;
            text-decoration: none;
        }
        
        .wiki-page {
            page-break-before: always;
            padding: 20px;
        }
        
        .wiki-page:first-of-type {
            page-break-before: auto;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #1e293b;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        
        h1 { font-size: 24pt; border-bottom: 3px solid #2563eb; padding-bottom: 8px; }
        h2 { font-size: 20pt; border-bottom: 2px solid #3b82f6; padding-bottom: 6px; }
        h3 { font-size: 17pt; }
        h4 { font-size: 14pt; }
        h5 { font-size: 12pt; }
        h6 { font-size: 11pt; }
        
        .page-meta {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 9pt;
            color: #64748b;
        }
        
        .page-content {
            margin: 20px 0;
        }
        
        .page-content p {
            margin: 10px 0;
            text-align: justify;
        }
        
        .page-content ul, .page-content ol {
            margin: 10px 0 10px 30px;
        }
        
        .page-content li {
            margin: 5px 0;
        }
        
        .page-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .page-content table th,
        .page-content table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        
        .page-content table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        
        .page-content code {
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 9pt;
        }
        
        .page-content pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .page-content pre code {
            background: none;
            padding: 0;
            color: #e2e8f0;
        }
        
        .level-2 h2 {
            font-size: 18pt;
            color: #334155;
        }
        
        .level-3 h3 {
            font-size: 15pt;
            color: #475569;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    {{-- Cover Page --}}
    <div class="cover-page">
        <h1>{{ $project->name }}</h1>
        <h2>Wiki Documentation</h2>
        <div class="meta">
            <p>Generated on: {{ $generatedAt->format('F d, Y') }}</p>
            <p>Total Pages: {{ $pages->count() }}</p>
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="toc">
        <h2>Table of Contents</h2>
        <ul>
            @foreach($pages as $page)
                <li>
                    <a href="#page-{{ $page->id }}">{{ $page->title }}</a>
                    @if($page->children->count() > 0)
                        <ul>
                            @foreach($page->children as $child)
                                <li class="level-2">
                                    <a href="#page-{{ $child->id }}">{{ $child->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Wiki Pages Content --}}
    @foreach($pages as $page)
        <div id="page-{{ $page->id }}">
            {!! app('App\Services\WikiPdfExportService')->renderPageWithChildren($page) !!}
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $project->name }} - Wiki Documentation | Page <span class="pagenum"></span></p>
    </div>
</body>
</html>
