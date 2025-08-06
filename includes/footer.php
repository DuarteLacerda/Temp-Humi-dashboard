
</div> <!-- Fecha .wrapper -->
<footer> 
  <div class="container text-center">
    <p class="mb-0">© 2025 Sensor Sótão.</p>
    <p class="mb-0">
      <i class="fa-brands fa-github"></i> Desenvolvido por <a href="https://github.com/DuarteLacerda" target="_blank">Duarte Lacerda</a>
    </p>
  </div>
</footer>

<!-- Bootstrap JS Bundle (Popper + Bootstrap JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom JS -->
<script src="assets/scripts.js" defer></script>

<script>
  // Toggle sidebar
  const toggleBtn = document.getElementById('toggleSidebar');
  const sidebar = document.querySelector('.sidebar');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
  });
</script>

</body>
</html>