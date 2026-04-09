<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Edit Layup</h2>
            <p class="page-subtitle mt-1">Supplier: {{ $supplier->name }}</p>
        </div>
    </x-slot>

    <div class="pb-10">
        <div class="page-wrap max-w-3xl">
            <div class="app-card fade-rise">
                <form method="POST" action="{{ route('suppliers.layups.update', [$supplier, $layup]) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('layups.partials.form')
                    <div class="flex gap-2">
                        <button class="btn-primary" type="submit">Update</button>
                        <a class="btn-secondary" href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
