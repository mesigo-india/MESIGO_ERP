                </div>
            </main>
            
            <footer class="footer bg-white border-top py-3 d-print-none mt-auto">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-start">
                            <span class="text-muted small">&copy; <?= date('Y') ?> <strong>MESIGO INDIA PRIVATE LIMITED</strong>. Enterprise Resource Planning Edition.</span>
                        </div>
                        <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                            <span class="badge bg-light text-secondary border py-2">System Version 2.0.0</span>
                            <span class="badge bg-light text-success border py-2 ms-2"><i class="fas fa-circle-notch fa-spin me-1 text-success"></i> Cloud Node Connected</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
    
    <!-- Toastr for notifications -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Sidebar Toggle Event Script -->
    <script>
        $(document).ready(function() {
            $('#sidebarToggle').on('click', function() {
                $('.sidebar').toggleClass('show');
            });
        });
    </script>
</body>
</html>