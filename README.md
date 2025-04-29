# 📊 Aplicação ERP Taiksu

Bem-vindo à aplicação **ERP Taiksu**, uma solução completa para gestão empresarial.

---

## 🚀 Primeiros Passos

### 1️⃣ Clone o repositório

```bash
git clone https://github.com/Dragonxt022/erp.git
cd erp
```

---

### 2️⃣ Instale o Node.js

📅 Baixe e instale o Node.js diretamente pelo site:

🔗 [https://nodejs.org/pt/download](https://nodejs.org/pt/download)

---

### 3️⃣ Instale as dependências do frontend

```bash
npm install
```

---

### 4️⃣ Configure o backend Laravel

Siga a documentação oficial do Laravel:  
📚 [https://laravel.com/docs/11.x](https://laravel.com/docs/11.x)

#### Instalação do PHP + Composer + Laravel Installer

💻 **Linux:**

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"
```

🪿 **Windows:**

```powershell
# Executar como administrador
Set-ExecutionPolicy Bypass -Scope Process -Force; `
[System.Net.ServicePointManager]::SecurityProtocol = `
[System.Net.ServicePointManager]::SecurityProtocol -bor 3072; `
iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
```

🍎 **Mac OS:**

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"
```

---

### 5️⃣ Reinicie seu terminal

🔁 Para que todas as alterações surtam efeito, **feche e abra o terminal novamente**.

---

### 6️⃣ Configure o ambiente

📄 Copie o arquivo `.env.example` e renomeie para `.env`:

```bash
cp .env.example .env
```

✏️ Edite os dados de conexão com o banco de dados conforme necessário.

---

### 7️⃣ Gere a chave da aplicação

```bash
php artisan key:generate
```

---

### 8️⃣ Inicie o servidor Laravel

```bash
php artisan serve
```

✅ Se aparecer a mensagem de erro de conexão com banco de dados, está tudo certo — apenas instale o backup do banco ou rode as migrations:

```bash
php artisan migrate
```

---

## 🐧 Passos adicionais para LINUX

### 🐳 Instale o Docker + Docker Compose

```bash
sudo apt install docker docker-compose -y
```

---

### 🛆 Execute o Docker

Na pasta raiz do projeto, execute:

```bash
sudo docker-compose up -d
```

🔽 Esse comando baixará e executará as imagens conforme definidas no `docker-compose.yml`.

---

### ✅ Finalização

Se tudo estiver corretamente configurado:

- A aplicação estará disponível em: [http://localhost:8001](http://localhost:8001)
- O phpMyAdmin estará disponível em: [http://localhost:8080](http://localhost:8080)

---

⚠️ **Importante:** Antes de iniciar os containers, **certifique-se de que a aplicação está configurada corretamente** com:

- Pacotes instalados (`npm install`)
- PHP e Composer configurados
- `.env` preenchido
- Banco de dados configurado

---

## 🤝 Agradecimentos

Obrigado por seguir os passos!  
Esperamos que esta documentação tenha sido úil.  
Em caso de dúvidas, abra uma _issue_ ou envie uma mensagem.

---