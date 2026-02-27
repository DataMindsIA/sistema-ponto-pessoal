# 📊 Sistema de Ponto Pessoal

Sistema simples e intuitivo para controle de ponto pessoal. Registre entrada, saída para almoço, volta do almoço e saída final. Acompanhe seu saldo de horas diário, semanal e mensal.

## ✨ Funcionalidades

- ✅ **Registro de Ponto**: Entrada, Saída Almoço, Volta Almoço, Saída
- ⏱️ **Timer em Tempo Real**: Veja quanto tempo já trabalhou hoje
- 📈 **Saldo de Horas**: Calcule automaticamente se está devendo ou com saldo positivo
- 📅 **Tipos de Dia**: Normal, Feriado Trabalhado, Abonado, Folga, Falta
- 🎉 **Feriados Nacionais**: Cadastro automático de feriados 2025/2026
- 📊 **Relatorios**: Visualize seu historico e saldo mensal
- ✏️ **Edicao Retroativa**: Corrija registros de dias anteriores
- ⚙️ **Configuravel**: Defina sua carga horaria

## 🚀 Instalacao Rapida

### Requisitos
- PHP 8.0+
- SQLite (já vem com PHP)
- Servidor web (XAMPP, Apache, Nginx)

### Passo a Passo

1. **Clone o repositorio**
   ```bash
   git clone https://github.com/DataMindsIA/sistema-ponto-pessoal.git
   cd sistema-ponto-pessoal
   ```

2. **Configure no XAMPP**
   - Coloque a pasta em `C:\xampp\htdocs\ponto`
   - Ou crie um VirtualHost

3. **Acesse no navegador**
   ```
   http://localhost/ponto
   ```

4. **Pronto!** O banco `ponto.db` será criado automaticamente na primeira execução.

## 📁 Estrutura do Projeto

```
sistema-ponto-pessoal/
├── index.php              # Dashboard principal
├── relatorio.php          # Relatorios mensais
├── editar.php             # Edicao de registros
├── config.php             # Configuracoes do sistema
├── ponto.db               # Banco SQLite (criado automaticamente)
├── db/
│   └── conexao.php        # Conexao com banco
├── includes/
│   └── functions.php      # Funcoes auxiliares
├── api/
│   ├── registrar.php      # API para registrar ponto
│   ├── tempo_hoje.php     # API retorna tempo trabalhado
│   └── atualizar_tipo.php # API atualiza tipo do dia
└── sql/
    └── schema.sql         # Schema do banco
```

## 🎯 Como Usar

### Registro de Ponto

1. Acesse `index.php`
2. Clique no botao grande da acao atual:
   - **🟢 Entrada** - Ao chegar
   - **🟡 Saida Almoco** - Ao sair para almocar
   - **🔵 Volta Almoco** - Ao voltar do almoco
   - **🔴 Saida** - Ao sair definitivamente

### Alterar Tipo do Dia

Clique no botao "Tipo do Dia" para alternar entre:
- Normal
- Feriado Trabalhado (horas contam 100%)
- Feriado Folga (dia neutro)
- Abonado (dia contabilizado como completo)
- Folga (dia neutro)
- Falta (desconta carga horaria)

### Ver Relatorios

1. Clique em **📈 Relatorios**
2. Visualize:
   - Saldo diario de cada dia
   - Total do mes
   - Dias com deficit
   - Grafico de evolucao

### Editar Registros Antigos

1. Clique em **✏️ Editar**
2. Selecione a data
3. Corrija os horarios
4. Salve

## ⚙️ Configuracoes

Acesse `config.php` para ajustar:
- Carga horaria diaria (padrao: 08:00)
- Intervalo de almoco (padrao: 01:00)
- Dias uteis da semana
- Seu nome

## 💾 Banco de Dados

O sistema usa SQLite (sem necessidade de MySQL/PostgreSQL).

### Tabelas

- **config**: Configuracoes do sistema
- **registros**: Todos os pontos registrados
- **feriados**: Cadastro de feriados nacionais

### Backup

Para fazer backup, basta copiar o arquivo `ponto.db`.

## 🔒 Seguranca

- Acesso local apenas (sem autenticacao)
- Banco SQLite protegido por permissoes de arquivo
- Sem exposicao de dados sensiveis

## 🛠️ Tecnologias

- **Backend**: PHP 8.0+
- **Banco**: SQLite 3
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Design**: Mobile-First, Gradientes modernos

## 📱 Mobile

O sistema é totalmente responsivo e funciona perfeitamente em celular!

## 🐛 Problemas Comuns

### "Erro ao conectar com banco"
- Verifique se a pasta tem permissao de escrita
- No Linux/Mac: `chmod 755 pasta`

### "Funcao strftime nao existe"
- Atualize para PHP 8.1 ou use `date()` no lugar

### "Timer nao atualiza"
- Verifique se JavaScript está habilitado
- Veja o console do navegador (F12)

## 📝 Licenca

MIT License - Use como quiser!

## 👤 Autor

**Michael** - Sistema desenvolvido para controle pessoal de horas trabalhadas.

---

⭐ Se gostou, deixe uma estrela no repositorio!
