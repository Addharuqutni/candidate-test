<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Layers</h2>
            <p class="page-subtitle mt-1">Layup: {{ $layup->name }}</p>
        </div>
    </x-slot>

    <div class="pb-10">
        <div class="page-wrap">
            <div class="app-card fade-rise">
                <div class="mb-4"><a class="app-link" href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}">Back to Layup</a></div>
                <div class="table-shell">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Thickness</th>
                            <th>Width</th>
                            <th>Angle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($layers as $layer)
                            <tr>
                                <td>{{ $layer->layer_order }}</td>
                                <td>{{ $layer->thickness }}</td>
                                <td>{{ $layer->width }}</td>
                                <td>{{ $layer->angle }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="muted-copy py-4 text-center">No layers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
