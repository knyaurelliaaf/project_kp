        </div><!-- /content -->
    </main>
</div>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global fix: Pastikan semua modal Bootstrap dipindahkan ke <body> saat dibuka agar tidak tertutup backdrop abu-abu (stacking context issue)
document.addEventListener('show.bs.modal', function (event) {
    var modal = event.target;
    if (modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }
});
</script>

</body>
</html>