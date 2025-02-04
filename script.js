function calcularDivisao() {
    let totalGasto = parseFloat(document.getElementById('totalGasto').value);
    let numPessoas = parseInt(document.getElementById('numPessoas').value);
    let resultadoDiv = document.getElementById('resultado');
    let historicoTable = document.getElementById('historico');

    if (isNaN(totalGasto) || isNaN(numPessoas) || numPessoas <= 0) {
        resultadoDiv.classList.remove('d-none');
        resultadoDiv.classList.add('alert-danger');
        resultadoDiv.classList.remove('alert-info');
        resultadoDiv.textContent = 'Por favor, insira valores válidos!';
        return;
    }

    let valorPorPessoa = (totalGasto / numPessoas).toFixed(2);
    resultadoDiv.classList.remove('d-none', 'alert-danger');
    resultadoDiv.classList.add('alert-info');
    resultadoDiv.textContent = `Cada pessoa deve pagar: R$ ${valorPorPessoa}`;

    let novaLinha = `<tr><td>R$ ${totalGasto.toFixed(2)}</td><td>${numPessoas}</td><td>R$ ${valorPorPessoa}</td></tr>`;
    historicoTable.innerHTML += novaLinha;
}

// Verifica se há um valor armazenado no localStorage
window.onload = function() {
    const toggle = document.getElementById('darkModeSwitch');
    
    // Verifica se o switch estava ativado na última vez
    if (localStorage.getItem('switchState') === 'on') {
      toggle.checked = true; // Marca o switch como ativado
    }
  
    // Salva o estado do switch quando ele for alterado
    toggle.addEventListener('change', function() {
      if (toggle.checked) {
        localStorage.setItem('switchState', 'on'); // Armazena o estado como "on"
      } else {
        localStorage.setItem('switchState', 'off'); // Armazena o estado como "off"
      }
    });
  };
    
    // Seleciona o botão de alternância
  const toggleButton = document.getElementById('darkModeSwitch');
  
  // Verifica se o usuário já tem preferência de modo armazenada
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    toggleButton.classList.add('dark-mode');
  }
  
  // Alterna entre os modos e salva a preferência
  toggleButton.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    toggleButton.classList.toggle('dark-mode');
  
    // Salva a preferência no localStorage
    if (document.body.classList.contains('dark-mode')) {
      localStorage.setItem('darkMode', 'enabled');
    } else {
      localStorage.setItem('darkMode', 'disabled');
    }
  });





// Exemplo de validação para o formulário de login
document.querySelector('form').addEventListener('submit', function(e) {
    let username = document.getElementById('username').value;
    let password = document.getElementById('password').value;

    if (username == '' || password == '') {
        alert('Por favor, preencha todos os campos');
        e.preventDefault(); // Impede o envio do formulário
    }
});

