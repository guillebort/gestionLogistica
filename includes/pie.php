<!-- includes/pie.php -->
<footer class="mt-auto py-5">
    <div class="container">
        <div class="row align-items-center flex-column flex-md-row">
            
            <!-- Branding y Copyright -->
            <div class="col-md-4 text-center text-md-start mb-4 mb-md-0">
                <span class="fw-bold text-primary fs-5 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                    🚚 LogisTFG
                </span>
                <p class="text-muted small mt-2 mb-0">&copy; <?= date("Y") ?>. Tu socio logístico de confianza.</p>
            </div>
            
            <!-- Redes Sociales -->
            <div class="col-md-4 text-center mb-4 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="https://twitter.com" target="_blank" class="text-decoration-none text-secondary fw-medium px-2">
                        🐦 Twitter
                    </a>
                    <a href="https://instagram.com" target="_blank" class="text-decoration-none text-secondary fw-medium px-2">
                        📸 Instagram
                    </a>
                </div>
            </div>
            
            <!-- Acceso Oculto a la Administración -->
            <div class="col-md-4 text-center text-md-end">
                <a href="../admin/loginAdmin.php" class="btn btn-light btn-sm rounded-pill px-4 fw-medium text-muted shadow-sm">
                    ⚙️ Panel Admin
                </a>
            </div>
            
        </div>
    </div>
</footer>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>