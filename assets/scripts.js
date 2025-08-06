async function atualizarSensores() {
  try {
    const res = await fetch('api/api.php?get=1');
    const dados = await res.json();

    const elTemp = document.getElementById('temperatura');
    if (elTemp && dados.temperatura !== undefined) {
      elTemp.textContent = dados.temperatura + ' °C';
    }

    const elHum = document.getElementById('humidade');
    if (elHum && dados.humidade !== undefined) {
      elHum.textContent = dados.humidade + ' %';
    }

  } catch (e) {
    console.error('Erro ao obter dados:', e);
  }
}
atualizarSensores();
setInterval(atualizarSensores, 5000);

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('toggleSidebar');
  const sidebar = document.getElementById('sidebar');

  if (btn && sidebar) {
    // Toggle da sidebar
    btn.addEventListener('click', function (event) {
      event.stopPropagation(); // Evita fechar imediatamente
      sidebar.classList.toggle('hidden');

      if (!sidebar.classList.contains('hidden')) {
        // Ativa listener para fechar ao clicar fora
        document.addEventListener('click', outsideClickListener);
      } else {
        document.removeEventListener('click', outsideClickListener);
      }
    });

    // Função para fechar sidebar ao clicar fora
    function outsideClickListener(event) {
      if (!sidebar.contains(event.target) && !btn.contains(event.target)) {
        sidebar.classList.add('hidden');
        document.removeEventListener('click', outsideClickListener);
      }
    }
  }
});

