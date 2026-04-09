<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="page-title">Layups</h2>
                <p class="page-subtitle mt-1">Supplier: {{ $supplier->name }}</p>
            </div>
            <a href="{{ route('suppliers.layups.create', $supplier) }}" class="btn-primary">New Layup</a>
        </div>
    </x-slot>

    <div class="pb-10">
        <div class="page-wrap">
            <div class="app-card fade-rise">
                <div class="mb-4"><a class="app-link" href="{{ route('suppliers.show', $supplier) }}">Back to Supplier</a></div>
                <div class="table-shell">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Layers</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($layups as $layup)
                            <tr>
                                <td class="font-semibold">{{ $layup->name }}</td>
                                <td>{{ $layup->description ?? '-' }}</td>
                                <td><span class="badge">{{ $layup->layers_count }} layers</span></td>
                                <td class="text-right">
                                    <a class="app-link" href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="muted-copy py-4 text-center">No layups yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
