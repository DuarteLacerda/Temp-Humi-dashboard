<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="content">
  <h1>Dashboard - Temperatura e Humidade</h1>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body d-flex align-items-center">
          <i class="fas fa-temperature-high fa-3x text-primary me-3"></i>
          <div>
            <h5>Temperatura Atual</h5>
            <p class="display-5" id="temperatura">-- °C</p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body d-flex align-items-center">
          <i class="fas fa-tint fa-3x text-info me-3"></i>
          <div>
            <h5>Humidade Atual</h5>
            <p class="display-5" id="humidade">-- %</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mt-4">
    <div class="col-md-12 text-center text-muted" style="font-weight: 300;">
      <p>Atualiza de 5 em 5 segundos</p>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>