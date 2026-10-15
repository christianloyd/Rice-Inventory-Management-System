<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Slip — <?= e($return['return_no']) ?></title>
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; background: #fff !important; color: #000 !important; }
            .print-border { border: 1px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4 sm:p-8 font-sans text-gray-900">

    <!-- Top Toolbar -->
    <div class="max-w-2xl mx-auto mb-6 flex justify-between items-center no-print">
        <a href="<?= url('/stock-returns/' . $return['id']) ?>" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
            &larr; Back to Return Details
        </a>
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700">
            Print Return Slip
        </button>
    </div>

    <!-- Printable Slip Document -->
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow border border-gray-300 print:shadow-none print:border-none print:p-0">
        <!-- Document Header -->
        <div class="text-center pb-6 border-b-2 border-gray-900">
            <h1 class="text-2xl font-black uppercase tracking-wider text-gray-900">
                <?= e(\App\Models\Setting::get('app_name', 'MAE CONSUMER')) ?>
            </h1>
            <p class="text-xs text-gray-600 mt-0.5">Rice Wholesale & Retail Distribution</p>
            <div class="mt-3 inline-block bg-gray-900 text-white px-4 py-1 text-sm font-bold uppercase tracking-widest">
                Supplier Return Slip
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 gap-4 py-6 border-b border-gray-300 text-sm">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Supplier</p>
                <p class="font-bold text-base text-gray-900 mt-0.5"><?= e($return['supplier_name']) ?></p>
                <?php if(!empty($return['stock_in_reference'])): ?>
                    <p class="text-xs text-gray-600 mt-1">Delivery Ref: <span class="font-medium"><?= e($return['stock_in_reference']) ?></span></p>
                <?php endif; ?>
                <p class="text-xs text-gray-600">Origin Shipment: <span class="font-medium">STI-<?= str_pad($return['stock_in_id'], 5, '0', STR_PAD_LEFT) ?></span></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Return Slip No</p>
                <p class="font-mono font-bold text-base text-red-600 mt-0.5"><?= e($return['return_no']) ?></p>
                <p class="text-xs text-gray-600 mt-1">Date: <span class="font-medium"><?= date('M j, Y', strtotime($return['return_date'] ?? $return['created_at'])) ?></span></p>
                <p class="text-xs text-gray-600">Processed By: <span class="font-medium"><?= e($return['returned_by_name'] ?? 'Staff') ?></span></p>
            </div>
        </div>

        <!-- Returned Items Table -->
        <div class="py-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-900 text-xs font-bold uppercase tracking-wider text-gray-900">
                        <th class="py-2">Rice Variety / Item</th>
                        <th class="py-2 text-center">Packaging</th>
                        <th class="py-2 text-right">Quantity Returned</th>
                        <th class="py-2 text-right">Unit Cost</th>
                        <th class="py-2 text-right">Credit Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    <tr>
                        <td class="py-3 font-semibold text-gray-900">
                            <?= e($return['variant_name']) ?>
                        </td>
                        <td class="py-3 text-center text-gray-700">
                            <?= e($return['variant_unit'] ?? 'sack') ?>
                        </td>
                        <td class="py-3 text-right font-bold text-red-600">
                            <?= number_format($return['quantity']) ?> sacks
                        </td>
                        <td class="py-3 text-right text-gray-700">
                            ₱<?= number_format($return['cost_per_unit'], 2) ?>
                        </td>
                        <td class="py-3 text-right font-bold text-gray-900">
                            ₱<?= number_format($return['quantity'] * $return['cost_per_unit'], 2) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Reason & Remarks Box -->
        <div class="p-4 bg-gray-50 rounded border border-gray-300 text-xs space-y-2">
            <div>
                <span class="font-bold text-gray-700 uppercase tracking-wider">Reason for Return:</span>
                <span class="text-gray-900 font-medium ml-1"><?= e($return['reason']) ?></span>
            </div>
            <?php if(!empty($return['notes'])): ?>
                <div>
                    <span class="font-bold text-gray-700 uppercase tracking-wider">Remarks / Notes:</span>
                    <span class="text-gray-900 ml-1"><?= nl2br(e($return['notes'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acknowledgement & Signature Blocks -->
        <div class="grid grid-cols-2 gap-8 pt-12 mt-8 text-xs text-center border-t border-gray-300">
            <div>
                <div class="border-b border-gray-800 pb-1 mb-2 font-semibold text-gray-900">
                    <?= e($return['returned_by_name'] ?? 'Authorized Staff') ?>
                </div>
                <p class="text-gray-500 uppercase tracking-wider">Released / Prepared By</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Warehouse Staff</p>
            </div>
            <div>
                <div class="border-b border-gray-800 pb-1 mb-2 font-semibold text-gray-900">
                    &nbsp;
                </div>
                <p class="text-gray-500 uppercase tracking-wider">Received / Acknowledged By</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Supplier Driver / Representative</p>
            </div>
        </div>

        <div class="text-center text-[10px] text-gray-400 pt-8 mt-4 border-t border-dashed border-gray-200">
            Generated via RISMS Inventory Management &bull; <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Uncomment to auto print on page load if requested
            // window.print();
        });
    </script>
</body>
</html>
