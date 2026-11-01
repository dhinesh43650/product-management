<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Management</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<header class="app-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <h1 class="mb-0">Product Management</h1>
                <p class="mb-0 header-sub">Track your inventory in real time</p>
            </div>
        </div>
    </div>
</header>

<main class="container py-5">

    <div class="card form-card mb-5 shadow-sm">
        <div class="card-body p-4">
            <h2 class="card-title mb-4" id="formTitle">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Add New Product
            </h2>

            {{-- Hidden field used when editing an existing record --}}
            <input type="hidden" id="editProductId">

            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="productName" class="form-label fw-500">Product Name</label>
                    <input type="text"
                           class="form-control"
                           id="productName"
                           placeholder="e.g. Wireless Keyboard"
                           maxlength="255"
                           required>
                    <div class="invalid-feedback">Please enter a product name.</div>
                </div>

                <div class="col-12 col-md-3">
                    <label for="quantityInStock" class="form-label fw-500">Quantity in Stock</label>
                    <input type="number"
                           class="form-control"
                           id="quantityInStock"
                           placeholder="0"
                           min="0"
                           step="1"
                           required>
                    <div class="invalid-feedback">Please enter a valid quantity (≥ 0).</div>
                </div>

                <div class="col-12 col-md-3">
                    <label for="pricePerItem" class="form-label fw-500">Price per Item (£)</label>
                    <div class="input-group">
                        <span class="input-group-text">£</span>
                        <input type="number"
                               class="form-control"
                               id="pricePerItem"
                               placeholder="0.00"
                               min="0"
                               step="0.01"
                               required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid price (≥ 0).</div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" id="submitBtn" type="button">
                        <span id="submitSpinner"
                              class="spinner-border spinner-border-sm me-1 d-none"
                              role="status"></span>
                        <i class="bi bi-plus-lg me-1" id="submitIcon"></i>
                        <span id="submitLabel">Add</span>
                    </button>
                    <button class="btn btn-outline-secondary d-none" id="cancelEditBtn" type="button">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="alert alert-danger mt-3 d-none" id="formError" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="formErrorText"></span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <h2 class="mb-0 fs-5 fw-600">
                    <i class="bi bi-table me-2 text-primary"></i>Submitted Products
                </h2>
                <span class="badge bg-primary" id="productCount">
                    {{ count($products) }} {{ Str::plural('item', count($products)) }}
                </span>
            </div>

            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
                <div id="appToast" class="toast align-items-center border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body fw-500" id="toastMessage"></div>
                        <button type="button"
                                class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="productsTable">
                    <thead class="table-head">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Product Name</th>
                            <th class="text-end">Qty in Stock</th>
                            <th class="text-end">Price / Item</th>
                            <th>Date &amp; Time Submitted</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @forelse ($products as $index => $product)
                            <tr data-id="{{ $product['id'] }}">
                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                <td class="fw-500">{{ $product['product_name'] }}</td>
                                <td class="text-end">{{ number_format($product['quantity_in_stock']) }}</td>
                                <td class="text-end">£{{ number_format($product['price_per_item'], 2) }}</td>
                                <td class="text-muted small">{{ $product['datetime_submitted'] }}</td>
                                <td class="text-end fw-600 text-success">
                                    £{{ number_format($product['total_value'], 2) }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-id="{{ $product['id'] }}"
                                            data-name="{{ $product['product_name'] }}"
                                            data-qty="{{ $product['quantity_in_stock'] }}"
                                            data-price="{{ $product['price_per_item'] }}"
                                            title="Edit this product">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    No products yet. Add one above!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot id="tableFooter" class="{{ count($products) === 0 ? 'd-none' : '' }}">
                        <tr class="total-row">
                            <td colspan="5" class="ps-4 fw-700 text-end">
                                Grand Total
                            </td>
                            <td class="text-end fw-700 fs-5 text-success" id="grandTotal">
                                £{{ number_format(array_sum(array_column($products, 'total_value')), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>

<script>
    window.AppConfig = {
        csrfToken    : '{{ csrf_token() }}',
        storeUrl     : '{{ route('products.store') }}',
        updateUrlBase: '{{ url('/products') }}',
        listUrl      : '{{ route('products.list') }}',
    };
</script>

</body>
</html>
