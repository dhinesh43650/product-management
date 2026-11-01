"use strict";

document.addEventListener("DOMContentLoaded", () => {

    const formCard       = document.querySelector(".form-card");
    const formTitle      = document.getElementById("formTitle");
    const editProductId  = document.getElementById("editProductId");
    const productName    = document.getElementById("productName");
    const quantityInput  = document.getElementById("quantityInStock");
    const priceInput     = document.getElementById("pricePerItem");
    const submitBtn      = document.getElementById("submitBtn");
    const submitSpinner  = document.getElementById("submitSpinner");
    const submitIcon     = document.getElementById("submitIcon");
    const submitLabel    = document.getElementById("submitLabel");
    const cancelEditBtn  = document.getElementById("cancelEditBtn");
    const formError      = document.getElementById("formError");
    const formErrorText  = document.getElementById("formErrorText");
    const tableBody      = document.getElementById("productsTableBody");
    const tableFooter    = document.getElementById("tableFooter");
    const grandTotal     = document.getElementById("grandTotal");
    const productCount   = document.getElementById("productCount");
    const toastEl        = document.getElementById("appToast");
    const toastMessage   = document.getElementById("toastMessage");

    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });

    function formatCurrency(value) {
        return "£" + parseFloat(value).toLocaleString("en-GB", {
            minimumFractionDigits : 2,
            maximumFractionDigits : 2,
        });
    }

    function formatQty(value) {
        return parseInt(value).toLocaleString("en-GB");
    }

    function showToast(message, type = "success") {
        toastEl.classList.remove("bg-success", "bg-danger", "text-white");
        if (type === "success") {
            toastEl.classList.add("bg-success", "text-white");
        } else {
            toastEl.classList.add("bg-danger", "text-white");
        }
        toastMessage.textContent = message;
        bsToast.show();
    }

    function setFieldError(input, message) {
        const wrapper = input.closest(".col-12") || input.closest(".input-group")?.closest(".col-12");
        input.classList.toggle("is-invalid", Boolean(message));
        if (wrapper) {
            const fb = wrapper.querySelector(".invalid-feedback");
            if (fb && message) fb.textContent = message;
        }
    }

    function clearFieldErrors() {
        [productName, quantityInput, priceInput].forEach(el => {
            el.classList.remove("is-invalid", "is-valid");
        });
        formError.classList.add("d-none");
    }

    function setLoading(loading) {
        submitBtn.disabled = loading;
        submitSpinner.classList.toggle("d-none", !loading);
        submitIcon.classList.toggle("d-none", loading);
    }

    function validateForm() {
        let valid = true;
        clearFieldErrors();

        if (!productName.value.trim()) {
            setFieldError(productName, "Please enter a product name.");
            valid = false;
        }

        const qty = parseFloat(quantityInput.value);
        if (quantityInput.value === "" || isNaN(qty) || qty < 0 || !Number.isInteger(qty)) {
            setFieldError(quantityInput, "Please enter a whole number ≥ 0.");
            valid = false;
        }

        const price = parseFloat(priceInput.value);
        if (priceInput.value === "" || isNaN(price) || price < 0) {
            setFieldError(priceInput, "Please enter a price ≥ 0.");
            valid = false;
        }

        return valid;
    }

    function buildRow(product, index, isNew = false) {
        const tr = document.createElement("tr");
        tr.setAttribute("data-id", product.id);
        if (isNew) tr.classList.add("row-new");

        tr.innerHTML = `
            <td class="ps-4 text-muted">${index + 1}</td>
            <td class="fw-500">${escapeHtml(product.product_name)}</td>
            <td class="text-end">${formatQty(product.quantity_in_stock)}</td>
            <td class="text-end">${formatCurrency(product.price_per_item)}</td>
            <td class="text-muted small">${product.datetime_submitted}</td>
            <td class="text-end fw-600 text-success">${formatCurrency(product.total_value)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary edit-btn"
                        data-id="${product.id}"
                        data-name="${escapeHtml(product.product_name)}"
                        data-qty="${product.quantity_in_stock}"
                        data-price="${product.price_per_item}"
                        title="Edit this product">
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </td>
        `;

        return tr;
    }

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function renderTable(products) {
        tableBody.innerHTML = "";

        if (!products || products.length === 0) {
            const emptyTr = document.createElement("tr");
            emptyTr.id = "emptyRow";
            emptyTr.innerHTML = `
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                    No products yet. Add one above!
                </td>
            `;
            tableBody.appendChild(emptyTr);
            tableFooter.classList.add("d-none");
            productCount.textContent = "0 items";
            return;
        }

        let grandTotalValue = 0;

        products.forEach((product, index) => {
            const tr = buildRow(product, index);
            tableBody.appendChild(tr);
            grandTotalValue += parseFloat(product.total_value) || 0;
        });

        grandTotal.textContent = formatCurrency(grandTotalValue);
        tableFooter.classList.remove("d-none");

        const count = products.length;
        productCount.textContent = `${count} ${count === 1 ? "item" : "items"}`;
    }

    function resetForm() {
        editProductId.value = "";
        productName.value   = "";
        quantityInput.value = "";
        priceInput.value    = "";
        clearFieldErrors();

        formTitle.innerHTML = '<i class="bi bi-plus-circle me-2 text-primary"></i>Add New Product';
        submitLabel.textContent = "Add";
        submitIcon.className    = "bi bi-plus-lg me-1";
        cancelEditBtn.classList.add("d-none");
        formCard.classList.remove("edit-mode");

        document.querySelectorAll("#productsTableBody tr.editing").forEach(tr => {
            tr.classList.remove("editing");
        });

        productName.focus();
    }

    function enterEditMode(btn) {
        const id    = btn.dataset.id;
        const name  = btn.dataset.name;
        const qty   = btn.dataset.qty;
        const price = btn.dataset.price;

        editProductId.value = id;
        productName.value   = name;
        quantityInput.value = qty;
        priceInput.value    = parseFloat(price).toFixed(2);
        clearFieldErrors();

        formTitle.innerHTML = '<i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Product';
        submitLabel.textContent = "Save Changes";
        submitIcon.className    = "bi bi-check-lg me-1";
        cancelEditBtn.classList.remove("d-none");
        formCard.classList.add("edit-mode");

        document.querySelectorAll("#productsTableBody tr.editing").forEach(tr => {
            tr.classList.remove("editing");
        });
        const targetRow = tableBody.querySelector(`tr[data-id="${id}"]`);
        if (targetRow) targetRow.classList.add("editing");

        formCard.scrollIntoView({ behavior: "smooth", block: "start" });
        setTimeout(() => productName.focus(), 350);
    }

    async function addProduct() {
        if (!validateForm()) return;

        setLoading(true);

        const payload = {
            product_name      : productName.value.trim(),
            quantity_in_stock : parseInt(quantityInput.value),
            price_per_item    : parseFloat(priceInput.value),
            _token            : window.AppConfig.csrfToken,
        };

        try {
            const response = await fetch(window.AppConfig.storeUrl, {
                method : "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept"      : "application/json",
                    "X-CSRF-TOKEN": window.AppConfig.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                handleServerErrors(data);
                return;
            }

            renderTable(data.products);
            resetForm();
            showToast("✓ Product added successfully.");

        } catch (err) {
            showFormError("Network error — please check your connection and try again.");
            console.error("addProduct error:", err);
        } finally {
            setLoading(false);
        }
    }

    async function updateProduct(id) {
        if (!validateForm()) return;

        setLoading(true);

        const payload = {
            product_name      : productName.value.trim(),
            quantity_in_stock : parseInt(quantityInput.value),
            price_per_item    : parseFloat(priceInput.value),
            _method           : "PUT",
            _token            : window.AppConfig.csrfToken,
        };

        try {
            const url = `${window.AppConfig.updateUrlBase}/${id}`;

            const response = await fetch(url, {
                method : "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept"      : "application/json",
                    "X-CSRF-TOKEN": window.AppConfig.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                handleServerErrors(data);
                return;
            }

            renderTable(data.products);
            resetForm();
            showToast("✓ Product updated successfully.");

        } catch (err) {
            showFormError("Network error — please check your connection and try again.");
            console.error("updateProduct error:", err);
        } finally {
            setLoading(false);
        }
    }

    function handleServerErrors(data) {
        if (data.errors) {
            if (data.errors.product_name) {
                setFieldError(productName, data.errors.product_name[0]);
            }
            if (data.errors.quantity_in_stock) {
                setFieldError(quantityInput, data.errors.quantity_in_stock[0]);
            }
            if (data.errors.price_per_item) {
                setFieldError(priceInput, data.errors.price_per_item[0]);
            }
        } else {
            showFormError(data.message || "An unexpected error occurred. Please try again.");
        }
    }

    function showFormError(message) {
        formErrorText.textContent = message;
        formError.classList.remove("d-none");
    }

    submitBtn.addEventListener("click", () => {
        const id = editProductId.value.trim();
        if (id) {
            updateProduct(id);
        } else {
            addProduct();
        }
    });

    [productName, quantityInput, priceInput].forEach(input => {
        input.addEventListener("keydown", e => {
            if (e.key === "Enter") submitBtn.click();
        });
    });

    cancelEditBtn.addEventListener("click", resetForm);

    tableBody.addEventListener("click", e => {
        const btn = e.target.closest(".edit-btn");
        if (btn) enterEditMode(btn);
    });

});
