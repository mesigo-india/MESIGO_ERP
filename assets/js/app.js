/**
 * MESIGO ERP - Main JavaScript File
 */

(function($) {
    'use strict';
    
    // Document ready
    $(document).ready(function() {
        // Initialize components
        initSidebar();
        initSelect2();
        initDataTables();
        initFormValidation();
        initMasterDataQuickAdd();
    });
    
    // Sidebar toggle for mobile
    function initSidebar() {
        $('#sidebarToggle').on('click', function() {
            $('.sidebar').toggleClass('show');
        });
    }
    
    // Initialize Select2
    function initSelect2() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
    
    // Initialize DataTables
    function initDataTables() {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    }
    
    // Form validation
    function initFormValidation() {
        // Add form validation class
        $('.needs-validation').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    }
    
    // AJAX helper
    function ajaxRequest(options) {
        var defaults = {
            method: 'GET',
            timeout: 30000,
            dataType: 'json',
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                hideLoading();
                handleAjaxResponse(response);
            },
            error: function(xhr) {
                hideLoading();
                toastr.error('An error occurred. Please try again.');
            }
        };
        
        return $.ajax($.extend(defaults, options));
    }
    
    // Show loading overlay
    function showLoading() {
        if ($('.loading-overlay').length === 0) {
            $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
        }
        $('.loading-overlay').show();
    }
    
    // Hide loading overlay
    function hideLoading() {
        $('.loading-overlay').hide();
    }
    
    // Handle AJAX response
    function handleAjaxResponse(response) {
        if (response.status === 'success') {
            toastr.success(response.message || 'Operation completed successfully');
        } else {
            toastr.error(response.message || 'Operation failed');
        }
    }
    
    // Toastr configuration
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000
        };
    }

    function initMasterDataQuickAdd() {
        const selects = document.querySelectorAll('select[data-master]');
        if (!selects.length) return;

        ensureMasterModal();
        selects.forEach(select => {
            loadMasterOptions(select);
            if (!select.querySelector('option[value="__add_new__"]')) {
                select.add(new Option('➕ Add New', '__add_new__'));
            }
            select.addEventListener('change', function() {
                if (this.value !== '__add_new__') return;
                this.value = this.dataset.previousValue || '0';
                openMasterModal(this);
            });
            select.addEventListener('focus', function() { this.dataset.previousValue = this.value; });
        });
    }

    function ensureMasterModal() {
        if (document.getElementById('masterQuickAddModal')) return;
        document.body.insertAdjacentHTML('beforeend', '<div class="modal fade" id="masterQuickAddModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="masterQuickAddForm"><div class="modal-header"><h5 class="modal-title">Add Master Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-danger d-none" id="masterQuickAddError"></div><input type="hidden" name="csrf_token" value="' + (document.querySelector('input[name="csrf_token"]')?.value || '') + '"><div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div><div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" required></div><div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div><input type="hidden" name="status" value="1"></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>');
        document.getElementById('masterQuickAddForm').addEventListener('submit', saveMasterQuickAdd);
    }

    function loadMasterOptions(select) {
        const current = select.value;
        fetch('/settings/master-data/' + select.dataset.master + '/options', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(response => {
                if (response.status !== 'success') return;
                response.data.options.forEach(option => {
                    if (!select.querySelector('option[value="' + CSS.escape(option.value) + '"]')) {
                        select.add(new Option(option.label, option.value));
                    }
                });
                if (current) select.value = current;
            })
            .catch(() => {});
    }

    function openMasterModal(select) {
        const form = document.getElementById('masterQuickAddForm');
        form.reset();
        form.dataset.master = select.dataset.master;
        form.dataset.targetName = select.name;
        const codeInput = form.querySelector('input[name="code"], input[name="hs_code"]');
        if (codeInput) codeInput.name = select.dataset.master === 'hs-codes' ? 'hs_code' : 'code';
        document.querySelector('#masterQuickAddModal .modal-title').textContent = 'Add ' + (select.dataset.masterTitle || 'Master Data');
        document.getElementById('masterQuickAddError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('masterQuickAddModal')).show();
    }

    function saveMasterQuickAdd(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const errorBox = document.getElementById('masterQuickAddError');
        const data = new FormData(form);
        fetch('/settings/master-data/' + form.dataset.master + '/quick-store', { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(response => {
                if (response.status !== 'success') {
                    errorBox.textContent = response.message || 'Unable to save master data.';
                    errorBox.classList.remove('d-none');
                    return;
                }
                const option = response.data.option;
                document.querySelectorAll('select[data-master="' + form.dataset.master + '"]').forEach(select => {
                    if (!select.querySelector('option[value="' + CSS.escape(option.value) + '"]')) {
                        const addOption = select.querySelector('option[value="__add_new__"]');
                        select.insertBefore(new Option(option.label, option.value), addOption);
                    }
                    if (select.name === form.dataset.targetName) select.value = option.value;
                });
                bootstrap.Modal.getInstance(document.getElementById('masterQuickAddModal')).hide();
                if (window.toastr) toastr.success(response.message || 'Master data saved.');
            })
            .catch(() => {
                errorBox.textContent = 'Unable to save master data.';
                errorBox.classList.remove('d-none');
            });
    }

    // =========================================================================
    // GLOBAL AUTO FETCH ENGINE (STABILIZATION PHASE)
    // =========================================================================
    function initGlobalAutoFetch() {
        // 1. Buyer Auto Fetch
        $(document).on('change', '#buyer_id', function() {
            const buyerId = $(this).val();
            if (!buyerId || buyerId === '0') return;
            
            $.ajax({
                url: '/buyers/' + buyerId + '/details',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.buyer) {
                        const b = res.buyer;
                        if (b.payment_terms && $('#payment_terms_id').length) {
                            $('#payment_terms_id').val(b.payment_terms).trigger('change');
                        }
                        if (b.currency_id && $('#currency_id').length) {
                            $('#currency_id').val(b.currency_id).trigger('change');
                        }
                        if (b.country && $('#destination_country_id').length) {
                            $('#destination_country_id').val(b.country).trigger('change');
                        }
                    }
                }
            });
        });

        // 2. Product Auto Fetch (Grid)
        $(document).on('change', 'select[name="product_id[]"]', function() {
            console.log("[DEBUG] Product Select Changed!");
            const productId = $(this).val();
            console.log("[DEBUG] Selected productId:", productId);
            if (!productId || productId === '0') return;
            
            // Find the parent tbody group since we restructured the grid
            const tbody = $(this).closest('.product-line-group');
            console.log("[DEBUG] Found tbody group:", tbody.length);
            
            $.ajax({
                url: '/products/' + productId + '/details',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log("[DEBUG] AJAX Success response:", res);
                    if (res.success && res.product) {
                        const p = res.product;
                        // Map Product fields to Quotation/Grid fields precisely matching our HTML
                        if (p.hsn_code) tbody.find('input[name="hsn_code[]"]').val(p.hsn_code);
                        if (p.description) tbody.find('input[name="description[]"]').val(p.description);
                        if (p.specification) tbody.find('input[name="specification[]"]').val(p.specification);
                        
                        console.log("[DEBUG] Populated basic input fields.");
                        
                        if (p.unit_id) {
                            const unitSelect = tbody.find('select[name="unit_id[]"]');
                            if (unitSelect.length) {
                                unitSelect.val(p.unit_id);
                                if(unitSelect.hasClass('select2-hidden-accessible')) {
                                    unitSelect.trigger('change.select2');
                                } else {
                                    unitSelect.trigger('change');
                                }
                            }
                        }
                        
                        if (p.packing_type_id) {
                            const packingSelect = tbody.find('select[name="packing_type_id[]"]');
                            if (packingSelect.length) packingSelect.val(p.packing_type_id).trigger('change');
                        }
                        
                        // Note: Product master might not have grade_id or warehouse_id natively, 
                        // but if it does, we map them here.
                        if (p.grade_id) {
                            const gradeSelect = tbody.find('select[name="grade_id[]"]');
                            if (gradeSelect.length) gradeSelect.val(p.grade_id).trigger('change');
                        }
                        
                        if (p.country_of_origin || p.origin_id) {
                            const originSelect = tbody.find('select[name="origin_id[]"]');
                            const originVal = p.origin_id || p.country_of_origin;
                            if (originSelect.length && originVal) originSelect.val(originVal).trigger('change');
                        }

                        // Trigger calculation update via the globally available calculate function
                        if (typeof window.calculate === 'function') {
                            window.calculate();
                        }
                    }
                }
            });
        });
    }

    // Initialize Auto Fetch on ready
    $(document).ready(function() {
        initGlobalAutoFetch();
    });

})(jQuery);