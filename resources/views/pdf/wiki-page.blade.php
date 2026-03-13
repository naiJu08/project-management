<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="html; charset=utf-8"/>
    <title>{{ $page->title }} - {{ $project->name }}</title>
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
            padding: 40px;
        }
        
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 26pt;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .header .project-name {
            font-size: 14pt;
            color: #64748b;
        }
        
        .meta {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
            font-size: 10pt;
            color: #64748b;
        }
        
        .content {
            margin: 30px 0;
        }
        
        .content p {
            margin: 12px 0;
            text-align: justify;
        }
        
        .content ul, .content ol {
            margin: 12px 0 12px 30px;
        }
        
        .content li {
            margin: 6px 0;
        }
        
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .content table th,
        .content table td {
            border: 1px solid #cbd5e1;
            padding: 10px;
            text-align: left;
        }
        
        .content table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        
        .content code {
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 9pt;
        }
        
        .content pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .content pre code {
            background: none;
            padding: 0;
            color: #e2e8f0;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $page->title }}</h1>
        <div class="project-name">{{ $project->name }}</div>
    </div>

    <div class="meta">
        <p><strong>Created by:</strong> {{ $page->creator->name ?? 'Unknown' }} on {{ $page->created_at->format('F d, Y \a\t g:i A') }}</p>
        @if($page->updated_at > $page->created_at)
            <p><strong>Last updated:</strong> {{ $page->updated_at->format('F d, Y \a\t g:i A') }} (Version {{ $page->version }})</p>
        @endif
        @if($page->parent)
            <p><strong>Parent Page:</strong> {{ $page->parent->title }}</p>
        @endif
    </div>

    <div class="content">
        {!! $page->content !!}
    </div>

    <div class="footer">
        <p>{{ $project->name }} - {{ $page->title }} | Generated on {{ now()->format('F d, Y') }}</p>
    </div>
</body>
</html>
