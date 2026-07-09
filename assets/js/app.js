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
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000
    };

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
    
})(jQuery);