# Laravel AI Chatbot

A professional, production-ready Laravel chatbot application powered by a local Ollama model and enhanced with Retrieval-Augmented Generation (RAG).

---

## Project Name

**Laravel AI Chatbot**

---

## Description

Laravel AI Chatbot is a secure, authenticated web application where users can chat with a local AI model (TinyLlama via Ollama).  
It includes a lightweight RAG pipeline so the chatbot can answer based on your own knowledge files (`.md` / `.txt`) instead of only generic model responses.

This project is ideal for:

- Local/offline-friendly AI assistants
- Internal company FAQ bots
- Learning Laravel + AI integration patterns

---

## Tech Stack

### Backend
- **Laravel 12**
- **PHP 8.2+**
- **MySQL**
- **Eloquent ORM**

### Frontend
- **Blade**
- **Tailwind CSS v3**
- **Vite**
- **Alpine.js**

### AI / RAG
- **Ollama** (local inference server)
- **tinyllama** (default model)
- Custom **RAG ingestion + retrieval** service in Laravel

---

## Installation Steps

### 1) Clone the repository

```bash
git clone <your-repo-url>
cd laravel-ai-chatbot
```

### 2) Install dependencies

```bash
composer install
npm install
```

### 3) Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

> On Windows PowerShell:
```powershell
copy .env.example .env
php artisan key:generate
```

### 4) Configure `.env`

Set database values:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ai_chatbot
DB_USERNAME=root
DB_PASSWORD=
```

Set Ollama + RAG values:

```env
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=tinyllama
OLLAMA_TIMEOUT=120

RAG_TOP_K=3
RAG_MAX_CONTEXT_CHARS=5000
```

### 5) Start Ollama

```bash
ollama pull tinyllama
ollama run tinyllama
```

Keep this terminal running.

### 6) Database + RAG setup

```bash
php artisan migrate
php artisan rag:ingest
```

### 7) Run the app

Terminal A:

```bash
php artisan serve
```

Terminal B:

```bash
npm run dev
```

Open:

`http://127.0.0.1:8000`

---

## Features

- User authentication (register/login/logout)
- Chat UI with modern professional layout
- AI responses from local Ollama model
- RAG support using local knowledge files
- Command-based ingestion (`php artisan rag:ingest`)
- Chat history persistence per user
- Dashboard navigation with **AI Chatbot** entry
- Root route redirect to login for secure default entry

---

## Tech Stack
- Backend: Laravel
- Frontend: Blade (Laravel)
- AI Integration: OpenAI API / Local LLM (Ollama)
- Database: MySQL
- Version Control: Git & GitHub

---

## Screenshots

> Add your screenshots in a folder like `docs/screenshots/` and update paths below.

### Login Screen

![Login Screen](https://github.com/shoaib5599/laravel-ai-chatbot/blob/a11d4c31cf0e32161ea07783b5884a7966aee7d2/login.png)

### Dashboard

![Dashboard](https://github.com/shoaib5599/laravel-ai-chatbot/blob/765d47aaf121cb37feac023a17d5e8dd24e3068a/dashboard.png)

### AI Chatbot

![AI Chatbot](https://github.com/shoaib5599/laravel-ai-chatbot/blob/765d47aaf121cb37feac023a17d5e8dd24e3068a/chatbot.png)

### RAG Knowledge Example

![RAG Knowledge](https://github.com/shoaib5599/laravel-ai-chatbot/blob/b916210e7221930a590dcaa740664088fa76798c/Rag.png)

---

## API Info

### Base URL

`http://127.0.0.1:8000`

### Authentication

Web session authentication (Laravel auth middleware).

### Main Chat Endpoint

`POST /chat/send`

#### Request Body (JSON)

```json
{
  "message": "What is our refund policy?"
}
```

#### Success Response (JSON)

```json
{
  "id": 1,
  "message": "What is our refund policy?",
  "response": "Users can request a refund within 7 days of purchase."
}
```

#### Error Response (JSON)

```json
{
  "message": "Local AI service is unavailable. Start Ollama and run: ollama run tinyllama"
}
```

---

## RAG Workflow

1. Add `.md` or `.txt` files to:
   - `storage/app/knowledge`
2. Run:
   - `php artisan rag:ingest`
3. Ask questions in chatbot
4. App retrieves relevant chunks and appends context to prompt

---

## Testing

```bash
php artisan test
```
## How It Works

- User sends message via UI
- Laravel sends request to AI API
- AI processes and returns response
- Laravel displays response in chat

---

## Future Improvements

- PDF upload + AI Q&A (RAG)
- Image understanding
- Multi-language support
- Role-based chatbot access

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first.

---

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

---

## Author

Shoaib Ali
Laravel Backend Developer

---

## Support

If you like this project, give it a ⭐ on GitHub!
