# MESIGO ERP - UI Rules and Standards

## Version: 1.0
## Last Updated: 2026-07-07
## Project: MESIGO ERP Enterprise Edition

---

## 1. UI FRAMEWORK STANDARDS

### 1.1 Technology Stack
- **Bootstrap 5.3** - CSS framework for responsive design
- **jQuery 3.7** - JavaScript library for DOM manipulation
- **AJAX** - Asynchronous data loading
- **Font Awesome 6** - Icon library
- **Select2** - Enhanced select dropdowns
- **DataTables** - Table sorting and pagination
- **Moment.js** - Date/time formatting

### 1.2 Browser Support
- **Chrome** - Latest 2 versions
- **Firefox** - Latest 2 versions
- **Safari** - Latest 2 versions
- **Edge** - Latest version
- **Mobile browsers** - iOS Safari, Android Chrome

---

## 2. DESIGN SYSTEM

### 2.1 Color Palette

#### Primary Colors
| Color | Variable | Usage |
|-------|----------|-------|
| #198754 | `--bs-success` | Primary action, success states |
| #0d6efd | `--bs-primary` | Links, secondary actions |
| #6c757d | `--bs-secondary` | Disabled states, secondary text |
| #dc3545 | `--bs-danger` | Errors, destructive actions |
| #ffc107 | `--bs-warning` | Warnings, pending states |
| #0dcaf0 | `--bs-info` | Information, neutral states |

#### Agricultural Theme Colors
| Color | Variable | Usage |
|-------|----------|-------|
| #28a745 | `--agri-green` | Agriculture, growth, fresh |
| #20c997 | `--agri-teal` | Organic, natural |
| #198754 | `--agri-forest` | Forest, sustainability |
| #6f42c1 | `--agri-purple` | Premium products |
| #fd7e14 | `--agri-orange` | Spices, warmth |
| #0d6efd | `--agri-blue` | Water, trust |

### 2.2 Typography
| Element | Font | Size | Weight |
|---------|------|------|--------|
| Body | System UI | 1rem (16px) | 400 |
| Headings | System UI | 1.25rem - 2rem | 600 |
| Table | System UI | 0.875rem | 400 |
| Buttons | System UI | 0.875rem | 500 |
| Labels | System UI | 0.75rem | 600 |

### 2.3 Spacing System
Use Bootstrap spacing scale:
- `m-0/p-0` - 0px
- `m-1/p-1` - 0.25rem (4px)
- `m-2/p-2` - 0.5rem (8px)
- `m-3/p-3` - 1rem (16px)
- `m-4/p-4` - 1.5rem (24px)
- `m-5/p-5` - 3rem (48px)

---

## 3. LAYOUT STANDARDS

### 3.1 Page Structure
```html
<!-- Standard page layout -->
<div class="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar">
        <!-- Navigation menu -->
    </nav>
    
    <!-- Main content -->
    <div class="main-content">
        <!-- Header/Navbar -->
        <header class="navbar">
            <!-- User menu, notifications -->
        </header>
        
        <!-- Page content -->
        <main class="content">
            <div class="container-fluid">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <!-- Breadcrumb items -->
                    </ol>
                </nav>
                
                <!-- Page title -->
                <div class="page-header">
                    <h1>Page Title</h1>
                    <div class="page-actions">
                        <!-- Action buttons -->
                    </div>
                </div>
                
                <!-- Page body -->
                <div class="page-body">
                    <!-- Content here -->
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <!-- Copyright, version -->
        </footer>
    </div>
</div>
```

### 3.2 Grid System
- **Container**: `container-fluid` for full width, `container` for fixed width
- **Row**: Always use `row` with `g-3` (gutter 16px)
- **Columns**: Use responsive classes (`col-12 col-md-6 col-lg-4`)
- **Breakpoints**:
  - `xs`: < 576px
  - `sm`: ≥ 576px
  - `md`: ≥ 768px
  - `lg`: ≥ 992px
  - `xl`: ≥ 1200px
  - `xxl`: ≥ 1400px

### 3.3 Card Layout
```html
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Card Title</h5>
        <div class="card-actions">
            <!-- Action buttons -->
        </div>
    </div>
    <div class="card-body">
        <!-- Card content -->
    </div>
    <div class="card-footer">
        <!-- Footer content -->
    </div>
</div>
```

---

## 4. COMPONENT STANDARDS

### 4.1 Forms

#### Form Structure
```html
<form id="form-id" method="POST" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="field-name" class="form-label">Field Label <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="field-name" name="field_name" required>
            <div class="invalid-feedback">Error message</div>
        </div>
        
        <div class="col-md-6">
            <label for="select-field" class="form-label">Select Field</label>
            <select class="form-select select2" id="select-field" name="select_field">
                <option value="">Select option</option>
                <option value="1">Option 1</option>
            </select>
        </div>
    </div>
    
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo me-2"></i>Reset
        </button>
    </div>
</form>
```

#### Form Validation Rules
- **Client-side**: HTML5 validation + custom JavaScript
- **Server-side**: PHP validation (always required)
- **Real-time**: AJAX validation for critical fields
- **Error display**: Below each field, not at top

#### Form Field Types
| Field Type | Component | Validation |
|------------|-----------|------------|
| Text | `form-control` | Required, min/max length |
| Email | `form-control` + `type="email"` | Valid email format |
| Number | `form-control` + `type="number"` | Min/max, decimal places |
| Select | `form-select` + `select2` | Required, valid option |
| Date | `form-control` + `type="date"` | Valid date, range |
| Textarea | `form-control` | Required, min/max length |
| File | `form-control` + `type="file"` | File type, size limit |
| Checkbox | `form-check-input` | Boolean validation |
| Radio | `form-check-input` | Required selection |

### 4.2 Tables

#### Standard Table
```html
<div class="table-responsive">
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>#</th>
                <th>Column 1</th>
                <th>Column 2</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Data 1</td>
                <td>Data 2</td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

#### Table Features
- **Pagination**: Server-side for > 100 records, client-side for < 100
- **Sorting**: Click column headers
- **Search**: Filter box above table
- **Export**: CSV, Excel, PDF buttons
- **Responsive**: Horizontal scroll on mobile

### 4.3 Buttons

#### Button Types
| Type | Class | Usage |
|------|-------|-------|
| Primary | `btn btn-primary` | Main action |
| Secondary | `btn btn-secondary` | Alternative action |
| Success | `btn btn-success` | Positive action |
| Danger | `btn btn-danger` | Delete, destructive |
| Warning | `btn btn-warning` | Warning action |
| Info | `btn btn-info` | Information action |
| Light | `btn btn-light` | Light action |
| Dark | `btn btn-dark` | Dark action |

#### Button Sizes
- `btn-sm` - Small buttons
- `btn-lg` - Large buttons
- Default - Medium buttons

#### Button States
- `disabled` - Disabled state
- `loading` - Loading spinner
- `active` - Active/toggle state

---

## 5. NAVIGATION STANDARDS

### 5.1 Sidebar Navigation
```html
<nav class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="/dashboard" class="nav-link">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#crm-submenu" class="nav-link collapsed" data-bs-toggle="collapse">
                <i class="fas fa-users me-2"></i>
                <span>CRM</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse" id="crm-submenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/buyer-crm" class="nav-link">Buyer CRM</a>
                    </li>
                    <li class="nav-item">
                        <a href="/supplier-crm" class="nav-link">Supplier CRM</a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</nav>
```

### 5.2 Breadcrumb Navigation
```html
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/buyer-crm">Buyer CRM</a></li>
        <li class="breadcrumb-item active" aria-current="page">View Buyer</li>
    </ol>
</nav>
```

### 5.3 Pagination
```html
<nav aria-label="Page navigation">
    <ul class="pagination">
        <li class="page-item disabled">
            <span class="page-link">Previous</span>
        </li>
        <li class="page-item active">
            <span class="page-link">1</span>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">2</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">Next</a>
        </li>
    </ul>
</nav>
```

---

## 6. AJAX STANDARDS

### 6.1 AJAX Request Format
```javascript
// Standard AJAX request
$.ajax({
    url: '/api/v1/buyers',
    method: 'POST',
    data: {
        buyer_name: $('#buyer-name').val(),
        email: $('#email').val()
    },
    beforeSend: function() {
        $('#submit-btn').prop('disabled', true).addClass('loading');
        $('.loading-spinner').show();
    },
    success: function(response) {
        if (response.status === 'success') {
            toastr.success(response.message);
            setTimeout(function() {
                window.location.href = '/buyer-crm';
            }, 1000);
        } else {
            toastr.error(response.message);
        }
    },
    error: function(xhr) {
        toastr.error('An error occurred. Please try again.');
        console.error(xhr.responseText);
    },
    complete: function() {
        $('#submit-btn').prop('disabled', false).removeClass('loading');
        $('.loading-spinner').hide();
    }
});
```

### 6.2 Loading States
- **Button loading**: Add `loading` class, disable button
- **Spinner**: Show loading spinner during request
- **Overlay**: Modal overlay for page-level loading
- **Progress bar**: For file uploads

### 6.3 Error Handling
- **User-friendly messages**: Never show system errors
- **Field errors**: Highlight invalid fields
- **Retry logic**: For network failures
- **Timeout**: 30 second timeout for all requests

---

## 7. RESPONSIVE DESIGN RULES

### 7.1 Mobile First Approach
- **Mobile**: Primary design target
- **Tablet**: Secondary breakpoints
- **Desktop**: Full feature set

### 7.2 Responsive Breakpoints
```css
/* Mobile first */
.element { /* Mobile styles */ }

@media (min-width: 576px) {
    .element { /* Small devices */ }
}

@media (min-width: 768px) {
    .element { /* Medium devices */ }
}

@media (min-width: 992px) {
    .element { /* Large devices */ }
}

@media (min-width: 1200px) {
    .element { /* Extra large devices */ }
}
```

### 7.3 Mobile-Specific Rules
- **Touch targets**: Minimum 44px x 44px
- **Font size**: Minimum 16px for readability
- **Navigation**: Collapsible sidebar
- **Forms**: Single column layout
- **Tables**: Horizontal scroll or card view

---

## 8. ACCESSIBILITY STANDARDS

### 8.1 WCAG 2.1 Compliance
- **Level AA** compliance required
- **Color contrast**: Minimum 4.5:1
- **Keyboard navigation**: All features accessible
- **Screen readers**: Proper ARIA labels

### 8.2 ARIA Attributes
```html
<button aria-label="Close" aria-describedby="modal-description">
    <i class="fas fa-times"></i>
</button>

<div id="modal-description" class="sr-only">
    Close this dialog and return to the page
</div>
```

### 8.3 Form Accessibility
- **Labels**: Every input must have a label
- **Error messages**: `aria-invalid="true"` and `aria-describedby`
- **Required fields**: `aria-required="true"`
- **Instructions**: `aria-describedby` for help text

---

## 9. PRINT STANDARDS

### 9.1 Print Styles
```css
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .page-break { page-break-after: always; }
}
```

### 9.2 Invoice/Quotation Print
- **A4 size** - Standard paper size
- **No background colors** - Save ink
- **Company header** - Logo and details
- **Page numbers** - For multi-page documents
- **Border** - Clean, professional look

---

## 10. NOTIFICATIONS AND ALERTS

### 10.1 Toastr Notifications
```javascript
// Success
toastr.success('Record saved successfully');

// Error
toastr.error('Failed to save record');

// Warning
toastr.warning('Please check the form');

// Info
toastr.info('New update available');
```

### 10.2 Alert Types
| Type | Class | Usage |
|------|-------|-------|
| Success | `alert alert-success` | Operation completed |
| Error | `alert alert-danger` | Error occurred |
| Warning | `alert alert-warning` | Warning message |
| Info | `alert alert-info` | Information message |

---

## 11. ICON STANDARDS

### 11.1 Icon Usage
- **Font Awesome 6** - Primary icon library
- **Consistent sizing** - `fa-sm`, `fa-md`, `fa-lg`
- **Meaningful icons** - Clear action representation
- **Accessibility** - `aria-hidden="true"` for decorative

### 11.2 Common Icons
| Action | Icon | Class |
|--------|------|-------|
| Add | `fa-plus` | `fa-plus` |
| Edit | `fa-edit` | `fa-edit` |
| Delete | `fa-trash` | `fa-trash` |
| View | `fa-eye` | `fa-eye` |
| Save | `fa-save` | `fa-save` |
| Print | `fa-print` | `fa-print` |
| Download | `fa-download` | `fa-download` |
| Search | `fa-search` | `fa-search` |
| Filter | `fa-filter` | `fa-filter` |
| Export | `fa-file-export` | `fa-file-export` |

---

## 12. MODAL STANDARDS

### 12.1 Modal Structure
```html
<div class="modal fade" id="modal-id" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
```

### 12.2 Modal Sizes
- `modal-sm` - Small (300px)
- `modal-md` - Medium (500px) - Default
- `modal-lg` - Large (800px)
- `modal-xl` - Extra large (1140px)

---

## 13. FILE UPLOAD STANDARDS

### 13.1 Upload Restrictions
- **Documents**: PDF, DOC, DOCX, XLS, XLSX
- **Images**: JPG, JPEG, PNG, GIF
- **Certificates**: PDF, max 5MB
- **Invoices**: PDF, max 10MB

### 13.2 Upload UI
```html
<div class="upload-area" id="drop-zone">
    <input type="file" id="file-input" class="d-none" accept=".pdf,.doc,.docx">
    <label for="file-input" class="btn btn-primary">
        <i class="fas fa-upload me-2"></i>Choose File
    </label>
    <p class="text-muted mt-2">Or drag and drop file here</p>
    <div class="progress mt-2 d-none">
        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
    </div>
</div>
```

---

## 14. DASHBOARD WIDGETS

### 14.1 Widget Types
- **Statistics cards** - Key metrics
- **Charts** - Data visualization
- **Tables** - Recent records
- **Calendars** - Important dates
- **Notifications** - Alerts and updates

### 14.2 Widget Structure
```html
<div class="card widget">
    <div class="card-header">
        <h5 class="card-title mb-0">Widget Title</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-chart-line fa-2x text-primary"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h3 class="mb-0">1,234</h3>
                <p class="text-muted mb-0">Total Buyers</p>
            </div>
        </div>
    </div>
</div>
```

---

## 15. THEME CUSTOMIZATION

### 15.1 Custom CSS Variables
```css
:root {
    --agri-primary: #198754;
    --agri-secondary: #28a745;
    --agri-success: #20c997;
    --agri-info: #0dcaf0;
    --agri-warning: #ffc107;
    --agri-danger: #dc3545;
    --agri-light: #f8f9fa;
    --agri-dark: #212529;
}
```

### 15.2 Dark Mode (Future)
- **CSS variables** for theme switching
- **User preference** stored in settings
- **System preference** detection
- **Smooth transition** between themes

---

## 16. PERFORMANCE RULES

### 16.1 Asset Optimization
- **Minification**: All CSS/JS minified
- **Compression**: Gzip enabled
- **CDN**: For static assets
- **Lazy loading**: Images and data

### 16.2 JavaScript Optimization
- **Defer loading**: Non-critical scripts
- **Event delegation**: For dynamic content
- **Debounce**: For search inputs
- **Throttle**: For scroll events

---

*This document defines the UI standards for MESIGO ERP. All frontend development must comply with these rules.*