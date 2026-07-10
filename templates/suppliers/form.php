<?php
/**
 * MESIGO ERP — Export Supplier CRM Form
 */
$s = $supplier ?? [];
$h = fn($str) => htmlspecialchars((string)($str ?? ''));
$isEdit = !empty($s['id']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold">
        <i class="fas fa-truck-loading me-2 text-primary"></i><?= $h($title) ?>
    </h4>
    <a href="/suppliers" class="btn btn-light shadow-sm border"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger shadow-sm">
        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= $h($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $isEdit ? '/suppliers/' . $s['id'] : '/suppliers' ?>" class="needs-validation" novalidate id="supplierForm">
    <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">

    <div class="row g-4">
        <!-- ── Left Column: Main Details ────────────────────────────────────── -->
        <div class="col-lg-8">
            
            <!-- SECTION 3: Business Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-building me-2"></i>Company & Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="supplier_code" class="form-label fw-semibold">Supplier Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="supplier_code" name="supplier_code" value="<?= $h($s['supplier_code'] ?? '') ?>" required readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="<?= $h($s['company_name'] ?? '') ?>" required>
                        </div>

                        <!-- Tax & Reg Info -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="<?= $h($s['gst_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" value="<?= $h($s['pan_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">IEC Code (Export/Import)</label>
                            <input type="text" name="iec_code" class="form-control" value="<?= $h($s['iec_code'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control" value="<?= $h($s['registration_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">FSSAI Number</label>
                            <input type="text" name="fssai" class="form-control" value="<?= $h($s['fssai'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">APEDA Registration</label>
                            <input type="text" name="apeda" class="form-control" value="<?= $h($s['apeda'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">ISO Certification</label>
                            <input type="text" name="iso" class="form-control" value="<?= $h($s['iso'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">HACCP Certification</label>
                            <input type="text" name="haccp" class="form-control" value="<?= $h($s['haccp'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Contact Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">Primary Contact</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control" value="<?= $h($s['contact_person'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= $h($s['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone / Mobile <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="<?= $h($s['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="text" name="website" class="form-control" value="<?= $h($s['website'] ?? '') ?>" placeholder="www.example.com">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-muted mb-0">Additional Contacts</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addContactBtn"><i class="fas fa-plus"></i> Add Contact</button>
                    </div>
                    
                    <div id="contactsContainer">
                        <?php $contacts = $s['contacts'] ?? []; ?>
                        <?php foreach ($contacts as $i => $c): ?>
                            <div class="row g-2 mb-2 contact-row align-items-center">
                                <div class="col-md-3"><input type="text" name="contacts[<?= $i ?>][name]" class="form-control form-control-sm" value="<?= $h($c['name']) ?>" placeholder="Name"></div>
                                <div class="col-md-3"><input type="text" name="contacts[<?= $i ?>][designation]" class="form-control form-control-sm" value="<?= $h($c['designation']) ?>" placeholder="Designation"></div>
                                <div class="col-md-3"><input type="email" name="contacts[<?= $i ?>][email]" class="form-control form-control-sm" value="<?= $h($c['email']) ?>" placeholder="Email"></div>
                                <div class="col-md-2"><input type="text" name="contacts[<?= $i ?>][mobile]" class="form-control form-control-sm" value="<?= $h($c['mobile']) ?>" placeholder="Mobile/WhatsApp"></div>
                                <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
                            </div>
                        <?php endforeach; ?>
                        <!-- Template for JS will go at bottom -->
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location & Address</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Country</label>
                            <select name="country_id" id="country_id" class="form-select select2">
                                <option value="" disabled selected>Select Country...</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($s['country_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $h($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">State/Region</label>
                            <select name="state_id" id="state_id" class="form-select select2">
                                <option value="" disabled selected>Select State...</option>
                                <?php foreach ($states ?? [] as $st): ?>
                                    <option value="<?= $st['id'] ?>" data-country="<?= $st['country_id'] ?>" <?= ($s['state_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= $h($st['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold w-100 d-flex justify-content-between align-items-center"><span>City</span><a href="#" class="text-primary small text-decoration-none openCityModalBtn"><i class="fas fa-plus"></i> Add</a></label>
                            <select name="city_id" class="form-select select2">
                                <option value="" disabled selected>Select City...</option>
                                <?php foreach ($cities ?? [] as $city): ?>
                                    <option value="<?= $city['id'] ?>" <?= ($s['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>><?= $h($city['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-muted mb-0">Detailed Addresses (Billing / Shipping / Works)</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addAddressBtn"><i class="fas fa-plus"></i> Add Address</button>
                    </div>
                    
                    <div id="addressesContainer">
                        <?php $addresses = $s['addresses'] ?? []; ?>
                        <?php foreach ($addresses as $i => $a): ?>
                            <div class="row g-2 mb-2 address-row align-items-center bg-light p-2 rounded">
                                <div class="col-md-2">
                                    <select name="addresses[<?= $i ?>][address_type]" class="form-select form-select-sm">
                                        <option value="billing" <?= $a['address_type'] == 'billing' ? 'selected' : '' ?>>Billing</option>
                                        <option value="shipping" <?= $a['address_type'] == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                                        <option value="factory" <?= $a['address_type'] == 'factory' ? 'selected' : '' ?>>Factory/Works</option>
                                    </select>
                                </div>
                                <div class="col-md-4"><input type="text" name="addresses[<?= $i ?>][address_line1]" class="form-control form-control-sm" value="<?= $h($a['address_line1']) ?>" placeholder="Address Line 1"></div>
                                <div class="col-md-3"><input type="text" name="addresses[<?= $i ?>][address_line2]" class="form-control form-control-sm" value="<?= $h($a['address_line2']) ?>" placeholder="Address Line 2"></div>
                                <div class="col-md-2"><input type="text" name="addresses[<?= $i ?>][pin_code]" class="form-control form-control-sm" value="<?= $h($a['pin_code']) ?>" placeholder="PIN Code"></div>
                                <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: Bank Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-university me-2"></i>Bank Details</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addBankBtn"><i class="fas fa-plus"></i> Add Bank</button>
                </div>
                <div class="card-body">
                    <div id="banksContainer">
                        <?php $banks = $s['banks'] ?? []; ?>
                        <?php foreach ($banks as $i => $b): ?>
                            <div class="row g-2 mb-2 bank-row bg-light p-2 rounded align-items-center">
                                <div class="col-md-3"><input type="text" name="banks[<?= $i ?>][bank_name]" class="form-control form-control-sm" value="<?= $h($b['bank_name']) ?>" placeholder="Bank Name"></div>
                                <div class="col-md-2"><input type="text" name="banks[<?= $i ?>][account_name]" class="form-control form-control-sm" value="<?= $h($b['account_name']) ?>" placeholder="Account Name"></div>
                                <div class="col-md-2"><input type="text" name="banks[<?= $i ?>][account_number]" class="form-control form-control-sm" value="<?= $h($b['account_number']) ?>" placeholder="A/C Number"></div>
                                <div class="col-md-2"><input type="text" name="banks[<?= $i ?>][ifsc_code]" class="form-control form-control-sm" value="<?= $h($b['ifsc_code']) ?>" placeholder="IFSC/Routing"></div>
                                <div class="col-md-2"><input type="text" name="banks[<?= $i ?>][swift_code]" class="form-control form-control-sm" value="<?= $h($b['swift_code']) ?>" placeholder="SWIFT"></div>
                                <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION 7: Purchase Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-shopping-cart me-2"></i>Purchase & Logistics Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="<?= $h($s['payment_terms'] ?? '') ?>" placeholder="e.g. 30 Days Net, 50% Advance">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Default Currency</label>
                            <input type="text" name="default_currency" class="form-control" value="<?= $h($s['default_currency'] ?? '') ?>" placeholder="USD, INR...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Default Incoterm</label>
                            <input type="text" name="incoterm" class="form-control" value="<?= $h($s['incoterm'] ?? '') ?>" placeholder="FOB, CIF, EXW">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lead Time (Days)</label>
                            <input type="number" name="lead_time_days" class="form-control" value="<?= $h($s['lead_time_days'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Minimum Order Qty (MOQ)</label>
                            <input type="text" name="moq" class="form-control" value="<?= $h($s['moq'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Container Capacity</label>
                            <input type="text" name="container_capacity" class="form-control" value="<?= $h($s['container_capacity'] ?? '') ?>" placeholder="e.g. 20ft / 40ft">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Default Port of Loading</label>
                            <input type="text" name="default_port" class="form-control" value="<?= $h($s['default_port'] ?? '') ?>" placeholder="Port Name">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Right Column: CRM Details ────────────────────────────────────── -->
        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm mb-4 border-top border-3 border-primary">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3 shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Supplier
                    </button>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Supplier Type</label>
                        <select name="supplier_type" class="form-select bg-light">
                            <option value="domestic" <?= ($s['supplier_type'] ?? '') === 'domestic' ? 'selected' : '' ?>>Domestic Supplier</option>
                            <option value="international" <?= ($s['supplier_type'] ?? '') === 'international' ? 'selected' : '' ?>>International Supplier</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Status</label>
                        <select name="status" class="form-select bg-light">
                            <option value="1" <?= ($s['status'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($s['status'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 8: CRM -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>CRM & Qualifications</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low" <?= ($s['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= ($s['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= ($s['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="critical" <?= ($s['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Rating (0-5)</label>
                        <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="<?= $h($s['rating'] ?? '0.00') ?>">
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_preferred" id="is_preferred" value="1" <?= !empty($s['is_preferred']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold text-warning" for="is_preferred"><i class="fas fa-star text-warning"></i> Preferred Supplier</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_approved" id="is_approved" value="1" <?= !empty($s['is_approved']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold text-success" for="is_approved"><i class="fas fa-check-circle text-success"></i> Approved for Export</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_blocked" id="is_blocked" value="1" <?= !empty($s['is_blocked']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold text-danger" for="is_blocked"><i class="fas fa-ban text-danger"></i> Blocked (Do Not Use)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Last Contact Date</label>
                        <input type="date" name="last_contact_date" class="form-control" value="<?= $h($s['last_contact_date'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Next Follow-up Date</label>
                        <input type="date" name="next_followup_date" class="form-control" value="<?= $h($s['next_followup_date'] ?? '') ?>">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Remarks & Notes</label>
                        <textarea name="remarks" class="form-control" rows="5" placeholder="Any special instructions or vendor history..."><?= $h($s['remarks'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<style>
.select2-results__options {
    max-height: 250px !important;
    overflow-y: auto !important;
}
.select2-results__option--highlighted[aria-selected],
.select2-results__option:hover {
    background-color: #0d6efd !important;
    color: white !important;
}
</style>

<div id="noCitiesAlert" class="mt-2 text-danger fw-bold" style="display: none;">
    No cities found. 
    <button type="button" class="btn btn-sm btn-outline-primary ms-2 openCityModalBtn">
        <i class="fas fa-plus"></i> Add New City
    </button>
</div>

<!-- Modal for Adding New City -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-city me-2 text-primary"></i>Add New City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Country</label>
            <input type="text" id="modalCountryName" class="form-control bg-light" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">State/Region</label>
            <input type="text" id="modalStateName" class="form-control bg-light" readonly>
            <input type="hidden" id="modalStateId">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
            <input type="text" id="modalCityName" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveCityBtn">
            <i class="fas fa-save me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.querySelector('select[name="city_id"]');
    const noCitiesAlert = document.getElementById('noCitiesAlert');
    const citySelectContainer = citySelect.parentElement;
    
    // Setup alert inside container
    citySelectContainer.appendChild(noCitiesAlert);

    // Initial disable states if empty
    if (!countrySelect.value) {
        stateSelect.disabled = true;
        citySelect.disabled = true;
    } else if (!stateSelect.value) {
        citySelect.disabled = true;
    }

    if (countrySelect && stateSelect && citySelect) {
        // Cache all states internally
        const allStates = Array.from(stateSelect.options).filter(opt => opt.value !== '').map(opt => ({
            value: opt.value,
            text: opt.text,
            countryId: opt.getAttribute('data-country'),
            selected: opt.selected
        }));
        
        function updateSelect2(el) {
            if (typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                jQuery(el).select2('destroy');
            }
            if (typeof jQuery !== 'undefined') {
                jQuery(el).select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        }

        function bindStateSelect2Events() {
            if (typeof jQuery !== 'undefined') {
                jQuery(stateSelect).off('select2:select').on('select2:select', function() {
                    citySelect.value = '';
                    loadCities(stateSelect.value);
                });
            }
        }

        function filterStates() {
            const selectedCountry = countrySelect.value;
            const currentState = stateSelect.value;
            
            // Clear current options except first
            while (stateSelect.options.length > 1) {
                stateSelect.remove(1);
            }
            
            if (!selectedCountry) {
                stateSelect.disabled = true;
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(stateSelect);
                bindStateSelect2Events();
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            stateSelect.disabled = false;
            
            // Add matching states
            allStates.forEach(state => {
                if (state.countryId === selectedCountry) {
                    const option = new Option(state.text, state.value, false, state.value === currentState);
                    option.setAttribute('data-country', state.countryId);
                    stateSelect.add(option);
                }
            });
            updateSelect2(stateSelect);
            bindStateSelect2Events();
            
            // If country changed manually and no state selected, disable city
            if (!stateSelect.value) {
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
            } else {
                // Fetch cities for currently selected state
                loadCities(stateSelect.value, citySelect.value);
            }
        }

        function loadCities(stateId, selectedCityId = null) {
            if (!stateId) {
                citySelect.disabled = true;
                citySelect.value = '';
                while (citySelect.options.length > 1) {
                    citySelect.remove(1);
                }
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            // AJAX fetch cities
            const formData = new FormData();
            formData.append('ajax_action', 'get_cities');
            formData.append('state_id', stateId);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    while (citySelect.options.length > 1) {
                        citySelect.remove(1);
                    }
                    
                    if (data.cities.length === 0) {
                        citySelect.disabled = true;
                        noCitiesAlert.style.display = 'block';
                    } else {
                        citySelect.disabled = false;
                        noCitiesAlert.style.display = 'none';
                        data.cities.forEach(city => {
                            const option = new Option(city.name, city.id, false, city.id == selectedCityId);
                            citySelect.add(option);
                        });
                    }
                    updateSelect2(citySelect);
                }
            });
        }
        
        // Listen to native change and select2 change for Country
        countrySelect.addEventListener('change', function() {
            stateSelect.value = ''; // clear state
            filterStates();
        });
        if (typeof jQuery !== 'undefined') {
            jQuery(countrySelect).on('select2:select', function() {
                stateSelect.value = '';
                filterStates();
            });
        }
        
        // Listen to native change for State
        stateSelect.addEventListener('change', function() {
            citySelect.value = ''; // clear city
            loadCities(stateSelect.value);
        });
        
        // Initial Select2 binding for state
        bindStateSelect2Events();
        
        // Enable select2 for city
        if (typeof jQuery !== 'undefined') {
            jQuery(citySelect).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Select City..."
            });
        }
        
        // Initial setup
        filterStates();

        // Modal Logic
        const addCityModal = new bootstrap.Modal(document.getElementById('addCityModal'));
        document.querySelectorAll('.openCityModalBtn').forEach(btn => btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!stateSelect.value) return;
            document.getElementById('modalCountryName').value = countrySelect.options[countrySelect.selectedIndex].text;
            document.getElementById('modalStateName').value = stateSelect.options[stateSelect.selectedIndex].text;
            document.getElementById('modalStateId').value = stateSelect.value;
            document.getElementById('modalCityName').value = '';
            addCityModal.show();
        }));

        document.getElementById('saveCityBtn').addEventListener('click', function() {
            const cityName = document.getElementById('modalCityName').value.trim();
            const stateId = document.getElementById('modalStateId').value;
            
            if (!cityName) return alert("City name is required");
            
            const formData = new FormData();
            formData.append('ajax_action', 'add_city');
            formData.append('state_id', stateId);
            formData.append('city_name', cityName);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    addCityModal.hide();
                    loadCities(stateId, data.city.id);
                } else {
                    alert(data.message || 'Error saving city');
                }
            });
        });
    }
});
</script>

<!-- Templates for JS dynamic rows -->
<template id="tplContact">
    <div class="row g-2 mb-2 contact-row align-items-center">
        <div class="col-md-3"><input type="text" name="contacts[__IDX__][name]" class="form-control form-control-sm" placeholder="Name"></div>
        <div class="col-md-3"><input type="text" name="contacts[__IDX__][designation]" class="form-control form-control-sm" placeholder="Designation"></div>
        <div class="col-md-3"><input type="email" name="contacts[__IDX__][email]" class="form-control form-control-sm" placeholder="Email"></div>
        <div class="col-md-2"><input type="text" name="contacts[__IDX__][mobile]" class="form-control form-control-sm" placeholder="Mobile/WhatsApp"></div>
        <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
    </div>
</template>

<template id="tplAddress">
    <div class="row g-2 mb-2 address-row align-items-center bg-light p-2 rounded">
        <div class="col-md-2">
            <select name="addresses[__IDX__][address_type]" class="form-select form-select-sm">
                <option value="billing">Billing</option>
                <option value="shipping">Shipping</option>
                <option value="factory">Factory/Works</option>
            </select>
        </div>
        <div class="col-md-4"><input type="text" name="addresses[__IDX__][address_line1]" class="form-control form-control-sm" placeholder="Address Line 1"></div>
        <div class="col-md-3"><input type="text" name="addresses[__IDX__][address_line2]" class="form-control form-control-sm" placeholder="Address Line 2"></div>
        <div class="col-md-2"><input type="text" name="addresses[__IDX__][pin_code]" class="form-control form-control-sm" placeholder="PIN Code"></div>
        <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
    </div>
</template>

<template id="tplBank">
    <div class="row g-2 mb-2 bank-row bg-light p-2 rounded align-items-center">
        <div class="col-md-3"><input type="text" name="banks[__IDX__][bank_name]" class="form-control form-control-sm" placeholder="Bank Name"></div>
        <div class="col-md-2"><input type="text" name="banks[__IDX__][account_name]" class="form-control form-control-sm" placeholder="Account Name"></div>
        <div class="col-md-2"><input type="text" name="banks[__IDX__][account_number]" class="form-control form-control-sm" placeholder="A/C Number"></div>
        <div class="col-md-2"><input type="text" name="banks[__IDX__][ifsc_code]" class="form-control form-control-sm" placeholder="IFSC/Routing"></div>
        <div class="col-md-2"><input type="text" name="banks[__IDX__][swift_code]" class="form-control form-control-sm" placeholder="SWIFT"></div>
        <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let contactIdx = <?= count($s['contacts'] ?? []) ?>;
    let addressIdx = <?= count($s['addresses'] ?? []) ?>;
    let bankIdx    = <?= count($s['banks'] ?? []) ?>;

    function addRow(btnId, containerId, tplId, idxVar) {
        document.getElementById(btnId).addEventListener('click', () => {
            const html = document.getElementById(tplId).innerHTML.replace(/__IDX__/g, idxVar++);
            document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
        });
    }

    addRow('addContactBtn', 'contactsContainer', 'tplContact', contactIdx);
    addRow('addAddressBtn', 'addressesContainer', 'tplAddress', addressIdx);
    addRow('addBankBtn', 'banksContainer', 'tplBank', bankIdx);

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.row').remove();
        }
    });

    // Bootstrap validation
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
});
</script>
