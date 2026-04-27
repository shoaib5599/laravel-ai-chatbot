<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Assistant</title>
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #111827;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border: #e5e7eb;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --danger: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 40%, #020617 100%);
            color: var(--text-primary);
            padding: 28px 16px;
        }

        .layout {
            max-width: 1060px;
            margin: 0 auto;
        }

        .chat-shell {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.35);
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 82vh;
        }

        .chat-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .header-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0b1220;
        }

        .header-subtitle {
            margin: 4px 0 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .status-badge {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .messages {
            padding: 24px;
            overflow-y: auto;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .message-row {
            display: flex;
            margin-bottom: 14px;
            align-items: flex-end;
            gap: 10px;
        }

        .message-row.user {
            justify-content: flex-end;
        }

        .message-row.ai {
            justify-content: flex-start;
        }

        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
        }

        .avatar.user {
            background: #334155;
        }

        .avatar.ai {
            background: var(--brand);
        }

        .bubble {
            max-width: min(76%, 760px);
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
            border: 1px solid transparent;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        }

        .bubble.user {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: #ffffff;
            border-bottom-right-radius: 6px;
        }

        .bubble.ai {
            background: #ffffff;
            color: #111827;
            border-color: #dbe2ea;
            border-bottom-left-radius: 6px;
        }

        .composer-wrap {
            border-top: 1px solid var(--border);
            background: var(--surface);
            padding: 16px 20px 18px;
        }

        .composer {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .composer textarea {
            width: 100%;
            resize: none;
            min-height: 52px;
            max-height: 140px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 13px 14px;
            font-size: 14px;
            line-height: 1.4;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: var(--surface-soft);
        }

        .composer textarea:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            background: #ffffff;
        }

        .composer button {
            border: none;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 14px 18px;
            min-width: 96px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.2s ease;
        }

        .composer button:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(29, 78, 216, 0.35);
        }

        .composer button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .composer-help {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }

        .error-text {
            color: var(--danger);
            font-size: 13px;
            margin-top: 8px;
            display: none;
        }

        @media (max-width: 720px) {
            body {
                padding: 0;
                background: #0f172a;
            }

            .chat-shell {
                min-height: 100vh;
                border-radius: 0;
                border: none;
            }

            .messages {
                padding: 16px;
            }

            .bubble {
                max-width: 86%;
            }
        }
    </style>
</head>
<body>
    <main class="layout">
        <section class="chat-shell">
            <header class="chat-header">
                <div>
                    <h1 class="header-title">AI Assistant</h1>
                    <p class="header-subtitle">Powered by local Ollama model</p>
                </div>
                <span class="status-badge">Online</span>
            </header>

            <div id="messages" class="messages">
                @forelse ($chats as $chat)
                    <div class="message-row user">
                        <div class="bubble user">{{ $chat->message }}</div>
                        <span class="avatar user">You</span>
                    </div>
                    @if (!empty($chat->response))
                        <div class="message-row ai">
                            <span class="avatar ai">AI</span>
                            <div class="bubble ai">{{ $chat->response }}</div>
                        </div>
                    @endif
                @empty
                    <div class="message-row ai">
                        <span class="avatar ai">AI</span>
                        <div class="bubble ai">Welcome! Ask anything to start chatting.</div>
                    </div>
                @endforelse
            </div>

            <div class="composer-wrap">
                <form id="chat-form" class="composer">
                    @csrf
                    <textarea id="message-input" name="message" placeholder="Send a message..." maxlength="2000" required></textarea>
                    <button type="submit" id="send-button">Send</button>
                </form>
                <div class="composer-help">
                    <span>Enter to send, Shift + Enter for a new line</span>
                    <span id="char-count">0 / 2000</span>
                </div>
                <div id="error-text" class="error-text"></div>
            </div>
        </section>
    </main>

    <template id="typing-template">
        <div class="message-row ai" id="typing-row">
            <span class="avatar ai">AI</span>
            <div class="bubble ai">Thinking...</div>
        </div>
    </template>

    <script>
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const messagesContainer = document.getElementById('messages');
        const errorText = document.getElementById('error-text');
        const charCount = document.getElementById('char-count');
        const typingTemplate = document.getElementById('typing-template');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let typingRow = null;

        const scrollToBottom = () => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        const setComposerState = (isBusy) => {
            sendButton.disabled = isBusy;
            messageInput.disabled = isBusy;
        };

        const updateCharCount = () => {
            charCount.textContent = `${messageInput.value.length} / 2000`;
        };

        const ensureTyping = () => {
            if (typingRow) {
                return;
            }

            const fragment = typingTemplate.content.cloneNode(true);
            typingRow = fragment.querySelector('#typing-row');
            messagesContainer.appendChild(fragment);
            scrollToBottom();
        };

        const removeTyping = () => {
            if (typingRow && typingRow.parentNode) {
                typingRow.parentNode.removeChild(typingRow);
            }

            typingRow = null;
        };

        const appendMessage = (text, type) => {
            const row = document.createElement('div');
            row.className = `message-row ${type}`;

            const avatar = document.createElement('span');
            avatar.className = `avatar ${type}`;
            avatar.textContent = type === 'user' ? 'You' : 'AI';

            const bubble = document.createElement('div');
            bubble.className = `bubble ${type}`;
            bubble.textContent = text;

            if (type === 'user') {
                row.appendChild(bubble);
                row.appendChild(avatar);
            } else {
                row.appendChild(avatar);
                row.appendChild(bubble);
            }

            messagesContainer.appendChild(row);
            scrollToBottom();
        };

        messageInput.addEventListener('input', updateCharCount);

        messageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                chatForm.requestSubmit();
            }
        });

        scrollToBottom();
        updateCharCount();

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = messageInput.value.trim();
            if (!message) {
                return;
            }

            errorText.style.display = 'none';
            setComposerState(true);
            appendMessage(message, 'user');
            messageInput.value = '';
            updateCharCount();
            ensureTyping();

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();

                if (!response.ok) {
                    const messageText = data.message ?? 'Failed to send message.';
                    throw new Error(messageText);
                }

                removeTyping();
                appendMessage(data.response ?? 'No response available.', 'ai');
            } catch (error) {
                removeTyping();
                errorText.textContent = error.message;
                errorText.style.display = 'block';
            } finally {
                setComposerState(false);
                messageInput.focus();
            }
        });
    </script>
</body>
</html>
