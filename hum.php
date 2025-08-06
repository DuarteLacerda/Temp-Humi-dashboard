<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="content">
  <h1>Médias de Humidade</h1>
  
  <div class="row g-4 mt-1">
    <!-- Média Horária -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Horária</h5>
        <div class="chart-container">
          <canvas id="chartHumHora"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('humidade', 'media_horaria')">
          <i class="fas fa-download"></i> Download Horária
        </button>
      </div>
    </div>

    <!-- Média Diária -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Diária</h5>
        <div class="chart-container">
          <canvas id="chartHumDia"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('humidade', 'media_diaria')">
          <i class="fas fa-download"></i> Download Diária
        </button>
      </div>
    </div>

    <!-- Média Mensal -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <h5>Média Mensal</h5>
        <div class="chart-container">
          <canvas id="chartHumMes"></canvas>
        </div>
        <button class="btn btn-sm btn-primary mt-2" onclick="downloadMedia('humidade', 'media_mes')">
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
    const ids = ['chartHumHora', 'chartHumDia', 'chartHumMes'];
    const labelsGrafico = ['Humidade Horária', 'Humidade Diária', 'Humidade Mensal'];

    for(let i=0; i < tipos.length; i++) {
      const dados = await fetchMedia('humidade', tipos[i]);
      const labels = Object.keys(dados);
      const valores = Object.values(dados);

      if (window['grafHum' + i]) window['grafHum' + i].destroy();
      window['grafHum' + i] = criarGrafico(
        document.getElementById(ids[i]).getContext('2d'),
        labels,
        valores,
        labelsGrafico[i]
      );
    }
  }

  atualizarGraficos();
  setInterval(atualizarGraficos, 60000);
</script>

<?php include 'includes/footer.php'; ?>
