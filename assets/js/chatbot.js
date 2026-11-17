(function () {
  'use strict';

  const config = window.niaChatbotConfig || null;
  const MAX_LENGTH = config && config.maxLength ? Number(config.maxLength) : 500;

  function initChatbot() {
    let root = document.getElementById('nia-chatbot-root');
    if (!root) {
      root = document.createElement('div');
      root.id = 'nia-chatbot-root';
      root.setAttribute('aria-live', 'polite');
      document.body.appendChild(root);
    }

    if (root.dataset.initialized === 'true') {
      return;
    }
    root.dataset.initialized = 'true';

    root.innerHTML = `
      <button
        type="button"
        class="nia-chatbot-toggle"
        aria-label="Abrir assistente virtual da FATEB"
        aria-expanded="false"
        aria-controls="nia-chatbot-window"
      >
        <span class="nia-chatbot-toggle-icon" aria-hidden="true">💬</span>
      </button>

      <section
        id="nia-chatbot-window"
        class="nia-chatbot-window"
        role="dialog"
        aria-label="Assistente Virtual FATEB"
        hidden
      >
        <header class="nia-chatbot-header">
          <div class="nia-chatbot-title-group">
            <h2 class="nia-chatbot-title">Assistente FATEB</h2>
            <span class="nia-chatbot-subtitle">NIA • Atendimento Virtual</span>
          </div>
          <button
            type="button"
            class="nia-chatbot-close"
            aria-label="Fechar janela do chat"
          >
            &times;
          </button>
        </header>

        <div class="nia-chatbot-messages" role="log" aria-live="polite"></div>

        <form class="nia-chatbot-form" novalidate>
          <label for="nia-chatbot-input" class="nia-sr-only">
            Digite sua dúvida sobre a FATEB
          </label>
          <textarea
            id="nia-chatbot-input"
            class="nia-chatbot-input"
            rows="1"
            maxlength="${MAX_LENGTH}"
            placeholder="Digite sua dúvida sobre a FATEB..."
            required
          ></textarea>
          <button type="submit" class="nia-chatbot-send" aria-label="Enviar mensagem">
            Enviar
          </button>
        </form>
      </section>
    `;

    const toggleBtn = root.querySelector('.nia-chatbot-toggle');
    const closeBtn = root.querySelector('.nia-chatbot-close');
    const chatWindow = root.querySelector('.nia-chatbot-window');
    const messagesBox = root.querySelector('.nia-chatbot-messages');
    const form = root.querySelector('.nia-chatbot-form');
    const input = root.querySelector('.nia-chatbot-input');
    const sendBtn = root.querySelector('.nia-chatbot-send');

    let isOpen = false;
    let isSending = false;

    appendMessage(
      messagesBox,
      'bot',
      'Olá! Sou o assistente virtual da FATEB. Como posso ajudar você hoje?'
    );

    function setChatOpen(open) {
      isOpen = open;
      chatWindow.hidden = !open;
      toggleBtn.setAttribute('aria-expanded', String(open));
      if (open) {
        input.focus();
      } else {
        toggleBtn.focus();
      }
    }

    toggleBtn.addEventListener('click', function () {
      setChatOpen(!isOpen);
    });

    closeBtn.addEventListener('click', function () {
      setChatOpen(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen) {
        setChatOpen(false);
      }
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });

    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (isSending) {
        return;
      }

      const rawText = input.value.trim();
      if (!rawText) {
        return;
      }

      if (rawText.length > MAX_LENGTH) {
        appendMessage(
          messagesBox,
          'error',
          `A mensagem ultrapassa o limite de ${MAX_LENGTH} caracteres.`
        );
        return;
      }

      appendMessage(messagesBox, 'user', rawText);
      input.value = '';
      setFormDisabled(true);

      const typingEl = appendTypingIndicator(messagesBox);

      try {
        const reply = await requestReply(rawText);
        typingEl.remove();
        appendMessage(messagesBox, 'bot', reply);
      } catch (err) {
        typingEl.remove();
        appendMessage(
          messagesBox,
          'error',
          err && err.message
            ? err.message
            : 'Não foi possível obter resposta no momento. Tente novamente.'
        );
      } finally {
        setFormDisabled(false);
        input.focus();
      }
    });

    function setFormDisabled(disabled) {
      isSending = disabled;
      input.disabled = disabled;
      sendBtn.disabled = disabled;
    }
  }

  /**
   * Envia a mensagem para o backend WordPress ou usa fallback no modo demo local.
   *
   * @param {string} message
   * @returns {Promise<string>}
   */
  async function requestReply(message) {
    if (!config || !config.endpoint) {
      await new Promise(function (resolve) {
        setTimeout(resolve, 600);
      });
      return (
        'Modo de demonstração local (fora do WordPress): recebi "' +
        message +
        '". Ative o plugin no WordPress para testar a REST API.'
      );
    }

    const response = await fetch(config.endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce || '',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ message: message }),
    });

    let data = null;
    try {
      data = await response.json();
    } catch (e) {
      throw new Error('Resposta inválida do servidor.');
    }

    if (!response.ok) {
      const serverMsg =
        data && data.message
          ? data.message
          : 'Erro ao processar sua solicitação (' + response.status + ').';
      throw new Error(serverMsg);
    }

    if (!data || typeof data.reply !== 'string') {
      throw new Error('O servidor não retornou uma resposta válida.');
    }

    return data.reply;
  }

  /**
   * Adiciona uma bolha de mensagem ao histórico usando textContent (previne XSS).
   */
  function appendMessage(container, sender, text) {
    const item = document.createElement('div');
    item.className = 'nia-chatbot-message nia-chatbot-message--' + sender;
    item.textContent = text;
    container.appendChild(item);
    container.scrollTop = container.scrollHeight;
    return item;
  }

  /**
   * Exibe indicador visual de digitação enquanto aguarda o servidor.
   */
  function appendTypingIndicator(container) {
    const item = document.createElement('div');
    item.className = 'nia-chatbot-message nia-chatbot-message--bot nia-chatbot-typing';
    item.textContent = 'Digitando...';
    container.appendChild(item);
    container.scrollTop = container.scrollHeight;
    return item;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatbot);
  } else {
    initChatbot();
  }
})();