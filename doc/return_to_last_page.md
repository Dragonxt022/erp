# Funcionalidade - Continuar de Onde Parou (Persistent Navigation)

## 📅 Data: 31/12/2025

## 📋 Resumo

Implementação de um sistema de persistência de navegação que armazena a última página visitada pelo usuário no banco de dados. Isso permite que, ao reautenticar ou retornar ao sistema, o usuário seja redirecionado automaticamente para o ponto exato onde parou, melhorando a experiência de uso.

## 🛠️ Componentes Técnicos

### 1. Banco de Dados
Foi adicionada a coluna `last_visited_url` na tabela `users`:
- **Tipo**: `VARCHAR` (String)
- **Propriedade**: `NULLABLE`
- **Migration**: `2025_12_31_110524_add_last_visited_url_to_users_table.php`

### 2. Middleware de Rastreamento
Arquivo: `app/Http/Middleware/TrackLastVisitedUrl.php`
- **Função**: Captura a URL completa de requisições `GET` bem-sucedidas.
- **Exclusões**: Não rastreia rotas de autenticação (`/login`, `/logout`, `/callback`), rotas de reset de senha ou chamadas puras de API (non-Inertia).
- **Registro**: Registrado no grupo `web` em `bootstrap/app.php`.

### 3. Lógica de Redirecionamento
Arquivo: `app/Http/Controllers/Auth/AuthController.php`
- **Método Auxiliar**: `redirectUser($user)`
- **Comportamento**: 
    - Se `last_visited_url` estiver preenchido, redireciona para esse endereço.
    - Caso contrário, segue o fluxo padrão baseado no grupo do usuário (Franqueadora ou Franqueado).
- **Integração**: Utilizado nos métodos de login por PIN, callback de IDP e login tradicional por CPF/Senha.

### 4. Modelo de Usuário
Arquivo: `app/Models/User.php`
- Adicionado `last_visited_url` ao array `$fillable` para permitir a gravação automática via middleware.

## ✅ Benefícios

1.  **Produtividade**: O usuário não precisa navegar manualmente até a tela de trabalho anterior após um timeout de sessão ou re-login.
2.  **Persistência Cross-Device**: Por estar salvo no banco de dados (e não apenas no LocalStorage), a última página é mantida mesmo se o usuário trocar de navegador ou dispositivo.

## 📝 Observações
As rotas de API que não retornam interface (non-Inertia) não afetam a URL salva, garantindo que o redirecionamento sempre leve a uma página visual válida.
