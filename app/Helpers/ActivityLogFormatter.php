<?php

namespace App\Helpers;

use App\Models\Application;
use Illuminate\Support\Collection;

class ActivityLogFormatter
{
    private const DOC_TYPE_LABELS = [
        'export'      => 'Application Export PDF',
        'submission'  => 'Submission PDF',
        'loan_deed'   => 'Loan Deed PDF',
        'guarantor'   => 'Guarantor Form PDF',
        'declaration' => 'Business Declaration PDF',
        'signing'     => 'Signed Document PDF',
    ];

    /**
     * Get sorted, limited activity logs for display.
     */
    public static function forApplication(Application $application, int $limit = 10): Collection
    {
        return $application->activityLogs
            ->sortByDesc('created_at')
            ->take($limit)
            ->map(fn($log) => [
                'id'          => $log->id,
                'datetime'    => DateFormatter::datetime($log->created_at),
                'iso'         => DateFormatter::iso($log->created_at),
                'user'        => $log->user->name ?? 'System',
                'description' => $log->description,
                'action'      => $log->action ?? null,
            ]);
    }

    /**
     * Return all document_generated activity log entries for an application,
     * newest first, formatted for the document-timeline partial.
     */
    public static function forDocuments(Application $application): Collection
    {
        return $application->activityLogs
            ->where('action', 'document_generated')
            ->sortByDesc('created_at')
            ->values()
            ->map(function ($log) {
                $docType  = $log->new_values['doc_type'] ?? 'unknown';
                $docLabel = self::DOC_TYPE_LABELS[$docType]
                    ?? ($log->new_values['doc_label'] ?? 'Document');

                return [
                    'id'           => $log->id,
                    'datetime'     => DateFormatter::datetime($log->created_at),
                    'iso'          => DateFormatter::iso($log->created_at),
                    'user'         => $log->user->name ?? 'System',
                    'doc_type'     => $docType,
                    'doc_label'    => $docLabel,
                    'saved'        => $log->new_values['saved'] ?? null,
                    'storage_path' => $log->new_values['storage_path'] ?? null,
                ];
            });
    }
}