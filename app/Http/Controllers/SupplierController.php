<?php

namespace App\Http\Controllers;

use App\Exceptions\ImportConflictException;
use App\Http\Requests\ImportSupplierRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Services\SupplierImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
        private readonly SupplierImportExportService $importExportService,
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('suppliers.index', [
            'suppliers' => $this->supplierRepository->paginateWithLayupCount($search)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = $this->supplierRepository->create($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier = $this->supplierRepository->findWithRelationsOrFail($supplier->id);

        return view('suppliers.show', [
            'supplier' => $supplier,
            'importReport' => session('import_report'),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->supplierRepository->update($supplier, $request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->supplierRepository->delete($supplier);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier deleted successfully.');
    }

    public function export(Supplier $supplier): Response
    {
        $payload = $this->importExportService->exportBySupplier($supplier);
        $fileName = 'supplier-'.$supplier->id.'-export.json';

        return response(
            json_encode($payload, JSON_PRETTY_PRINT),
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ],
        );
    }

    public function import(ImportSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $payload = $request->payloadAsArray();

        Validator::make($payload, [
            'layups' => ['required', 'array'],
            'layups.*.name' => ['required', 'string'],
            'layups.*.layers' => ['nullable', 'array'],
            'layups.*.layers.*.layer_order' => ['required', 'integer', 'min:1'],
            'layups.*.layers.*.thickness' => ['required', 'numeric', 'gt:0'],
            'layups.*.layers.*.width' => ['required', 'numeric', 'gt:0'],
            'layups.*.layers.*.angle' => ['required', 'numeric', 'between:-90,90'],
        ])->validate();

        try {
            $report = $this->importExportService->importBySupplier(
                supplier: $supplier,
                payload: $payload,
                strategy: (string) $request->validated('strategy'),
            );
        } catch (ImportConflictException $exception) {
            return redirect()
                ->route('suppliers.conflicts', $supplier)
                ->withErrors(['import' => 'Import rejected because conflict strategy is reject.'])
                ->with('import_report', [
                    'strategy' => SupplierImportExportService::STRATEGY_REJECT,
                    'summary' => [
                        'created_layups' => 0,
                        'created_layers' => 0,
                        'updated_layers' => 0,
                        'skipped_layers' => 0,
                        'conflicts_count' => count($exception->conflicts),
                    ],
                    'conflicts' => $exception->conflicts,
                ])
                ->with('pending_conflicts', $exception->conflicts);
        }

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Import completed successfully.')
            ->with('import_report', $report);
    }

    public function exportIndex(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $suppliers = $this->supplierRepository->paginateWithLayupCount($search, 1000)->items();
        $fileName = 'suppliers-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($suppliers): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['id', 'name', 'code', 'address', 'layups_count', 'created_at']);

            foreach ($suppliers as $supplier) {
                fputcsv($handle, [
                    $supplier->id,
                    $supplier->name,
                    $supplier->code,
                    $supplier->address,
                    $supplier->layups_count,
                    optional($supplier->created_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function conflicts(Supplier $supplier): View|RedirectResponse
    {
        $pendingConflicts = (array) session('pending_conflicts', []);

        if (count($pendingConflicts) === 0) {
            return redirect()
                ->route('suppliers.show', $supplier)
                ->withErrors(['conflicts' => 'No pending conflicts found. Run import with reject strategy first.']);
        }

        return view('suppliers.conflicts', [
            'supplier' => $supplier,
            'pendingConflicts' => $pendingConflicts,
        ]);
    }

    public function applyConflicts(Request $request, Supplier $supplier): RedirectResponse
    {
        $pendingConflicts = (array) session('pending_conflicts', []);

        if (count($pendingConflicts) === 0) {
            return redirect()
                ->route('suppliers.show', $supplier)
                ->withErrors(['conflicts' => 'No pending conflicts to resolve.']);
        }

        $validated = $request->validate([
            'resolutions' => ['required', 'array'],
            'resolutions.*.decision' => ['required', 'in:keep_existing,accept_incoming'],
        ]);

        $report = $this->importExportService->resolveConflicts(
            supplier: $supplier,
            conflicts: $pendingConflicts,
            resolutions: (array) $validated['resolutions'],
        );

        $request->session()->forget('pending_conflicts');

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Conflict resolution applied successfully.')
            ->with('import_report', $report);
    }
}
