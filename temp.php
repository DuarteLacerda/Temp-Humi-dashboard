<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="content">
  <h1>Médias de Temperatura</h1>

  <div class="row g-4 mt-1">
    <!-- Média Horária -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Horária</h5>
        <div class="chart-container">
          <canvas id="chartTempHora"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('temperatura', 'media_horaria')">
          <i class="fas fa-download"></i> Download Horária
        </button>
      </div>
    </div>

    <!-- Média Diária -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Diária</h5>
        <div class="chart-container">
          <canvas id="chartTempDia"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('temperatura', 'media_diaria')">
          <i class="fas fa-download"></i> Download Diária
        </button>
      </div>
    </div>

    <!-- Média Mensal -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Mensal</h5>
        <div class="chart-container">
          <canvas id="chartTempMes"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('temperatura', 'media_mes')">
          <i class="fas fa-download"></i> Download Mensal
        </button>
      </div>
    </div>
  </div>
</main>

<script>
  // Função para download
  function downloadMedia(sensor, tipo) {
    window.location.href = `api/api.php?ficheiro=${tipo}&sensor=${sensor}`;
  }

  // Buscar dados para os gráficos
  async function fetchMedia(sensor, tipo) {
    try {
      const res = await fetch(`api/api.php?media=${sensor}&tipo=${tipo}`);
      if (!res.ok) throw new Error('Erro a buscar dados');
      return await res.json();
    } catch (e) {
      console.error(e);
      return {};
    }
  }

  // Criar gráfico Chart.js
  function criarGrafico(ctx, labels, dados, label) {
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label,
          data: dados,
          borderColor: 'rgba(13, 110, 253, 0.7)',
          backgroundColor: 'rgba(13, 110, 253, 0.2)',
          fill: true,
          tension: 0.3,
          pointRadius: 3,
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  async function atualizarGraficos() {
    const tipos = ['horaria', 'diaria', 'mensal'];
    const ids = ['chartTempHora', 'chartTempDia', 'chartTempMes'];
    const labelsGrafico = ['Temperatura Horária', 'Temperatura Diária', 'Temperatura Mensal'];

    for(let i=0; i < tipos.length; i++) {
      const dados = await fetchMedia('temperatura', tipos[i]);
      const labels = Object.keys(dados);
      const valores = Object.values(dados);

      if (window['grafTemp' + i]) window['grafTemp' + i].destroy();
      window['grafTemp' + i] = criarGrafico(
        document.getElementById(ids[i]).getContext('2d'),
        labels,
        valores,
        labelsGrafico[i]
      );
    }
  }

  atualizarGraficos();
  setInterval(atualizarGraficos, 60000); // Atualizar a cada 1 minuto
</script>

<?php include 'includes/footer.php'; ?>
