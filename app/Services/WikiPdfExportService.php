<?php

namespace App\Services;

use App\Models\WikiPage;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class WikiPdfExportService
{
    /**
     * Export single wiki page to PDF
     */
    public function exportPage(WikiPage $page): \Barryvdh\DomPDF\PDF
    {
        $html = $this->generatePageHtml($page);
        
        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('enable-local-file-access', true);
    }

    /**
     * Export all wiki pages for a project to master PDF
     */
    public function exportProjectWiki(Project $project): \Barryvdh\DomPDF\PDF
    {
        $pages = $project->wikiPages()
            ->whereNull('parent_id')
            ->with('children', 'creator', 'updater')
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $html = $this->generateMasterPdfHtml($project, $pages);
        
        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('enable-local-file-access', true);
    }

    /**
     * Generate HTML for single page
     */
    private function generatePageHtml(WikiPage $page): string
    {
        return view('pdf.wiki-page', [
            'page' => $page,
            'project' => $page->project,
        ])->render();
    }

    /**
     * Generate HTML for master PDF with all pages
     */
    private function generateMasterPdfHtml(Project $project, $pages): string
    {
        return view('pdf.wiki-master', [
            'project' => $project,
            'pages' => $pages,
            'generatedAt' => now(),
        ])->render();
    }

    /**
     * Render page content with children recursively
     */
    public function renderPageWithChildren(WikiPage $page, int $level = 1): string
    {
        $html = '';
        
        // Page header
        $html .= '<div class="wiki-page level-' . $level . '">';
        $html .= '<h' . min($level, 6) . '>' . e($page->title) . '</h' . min($level, 6) . '>';
        
        // Page metadata
        $html .= '<div class="page-meta">';
        $html .= '<p>Created by: ' . e($page->creator->name ?? 'Unknown') . ' on ' . $page->created_at->format('M d, Y') . '</p>';
        if ($page->updated_at > $page->created_at) {
            $html .= '<p>Last updated: ' . $page->updated_at->format('M d, Y') . ' (Version ' . $page->version . ')</p>';
        }
        $html .= '</div>';
        
        // Page content
        $html .= '<div class="page-content">';
        $html .= $this->convertContentToHtml($page->content);
        $html .= '</div>';
        
        // Children pages
        if ($page->children && $page->children->count() > 0) {
            foreach ($page->children as $child) {
                $html .= $this->renderPageWithChildren($child, $level + 1);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Convert content to HTML (handles both HTML and Markdown)
     */
    private function convertContentToHtml(string $content): string
    {
        // Check if content appears to be HTML already
        if (strip_tags($content) !== $content) {
            // Already HTML, return as-is
            return $content;
        }
        
        // If it's markdown or plain text, convert line breaks
        return nl2br(e($content));
    }
}
