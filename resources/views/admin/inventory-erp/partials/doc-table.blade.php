<div class="table-responsive">
    <table class="table table-head-custom table-vertical-center">
        <thead>
            <tr>
                <th>Document</th>
                <th>Date</th>
                <th>Status</th>
                <th>Lines</th>
                <th>Notes</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
                <tr>
                    <td class="inv-value">{{ $document->document_no }}</td>
                    <td>{{ optional($document->document_date)->format('d M Y') }}</td>
                    <td><span class="badge badge-soft">{{ $document->status }}</span></td>
                    <td>{{ $document->lines_count }}</td>
                    <td class="inv-meta">{{ \Illuminate\Support\Str::limit($document->notes, 40) ?: '—' }}</td>
                    <td class="text-right">
                        <a href="{{ route($showRoute, $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">{{ $empty }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $documents->links() }}
