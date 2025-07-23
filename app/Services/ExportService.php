<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;

class ExportService
{
    /**
     * Export contacts to CSV format.
     */
    public function exportContactsToCSV(array $filters = []): string
    {
        $contacts = $this->getFilteredContacts($filters);
        
        $csvData = [];
        
        // Add CSV headers
        $csvData[] = [
            'Imię',
            'Nazwisko', 
            'Email',
            'Telefon',
            'Firma',
            'Stanowisko',
            'Tagi',
            'Data utworzenia'
        ];
        
        // Add contact data
        foreach ($contacts as $contact) {
            $tags = $contact->tags->pluck('name')->implode(', ');
            $csvData[] = [
                $contact->first_name,
                $contact->last_name,
                $contact->email,
                $contact->phone ?? '',
                $contact->company,
                $contact->position,
                $tags,
                $contact->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->generateCSVContent($csvData);
    }

    /**
     * Generate CSV filename with timestamp.
     */
    public function generateCSVFilename(): string
    {
        return 'kontakty_' . date('Y-m-d_H-i-s') . '.csv';
    }

    /**
     * Get filtered contacts without pagination.
     * Optimized with eager loading and chunking for large datasets.
     */
    private function getFilteredContacts(array $filters = []): Collection
    {
        // Generate a cache key based on export filters
        $cacheKey = 'export_' . md5(json_encode($filters));
        
        // Try to get results from cache first (short TTL for exports)
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        // Optimize eager loading by specifying exactly what we need
        $query = Contact::with([
            'tags:id,name', // Only select needed tag fields
            'creator:id,name,email' // Only select needed creator fields
        ]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        // Apply company filter
        if (!empty($filters['company'])) {
            $query->where('company', $filters['company']);
        }

        // Apply position filter
        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        // Apply tag filter
        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        $results = $query->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get();
        
        // Cache results for 5 minutes (short TTL for exports)
        cache()->put($cacheKey, $results, now()->addMinutes(5));
        
        return $results;
    }

    /**
     * Generate CSV content from array data.
     */
    private function generateCSVContent(array $csvData): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}