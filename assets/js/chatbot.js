/**
 * NIA Chatbot Gemini - widget (Etapa 2).
 * Nesta etapa as respostas sao SIMULADAS. A comunicacao com o endpoint REST entra na Etapa 3.
 * Seguranca: todo texto entra na pagina via textContent (nunca innerHTML), evitando XSS.
 */
(function () {
	'use strict';

	var cfg = window.NiaChatbotConfig || {};
	var MAX_LEN = parseInt(cfg.maxLength, 10) || 500;
	var TEXT = {
		title: cfg.title || 'Assistente FATEB',
		welcome: cfg.welcome || 'Olá! Sou o assistente virtual da FATEB. Como posso ajudar?',
		placeholder: cfg.placeholder || 'Digite sua pergunta…',
		send: cfg.send || 'Enviar',
		open: cfg.openLabel || 'Abrir chat',
		close: cfg.closeLabel || 'Fechar chat',
		typing: cfg.typing || 'Digitando…',
		mock: cfg.mockReply || 'Esta é uma resposta simulada (versão beta). Em breve responderei com a ajuda da inteligência artificial.'
	};

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text) { node.textContent = text; }
		return node;
	}

	function init() {
		if (document.getElementById('nia-cb-root')) { return; }

		var root = el('div', 'nia-cb-root');
		root.id = 'nia-cb-root';

		var toggle = el('button', 'nia-cb-toggle');
		toggle.type = 'button';
		toggle.setAttribute('aria-label', TEXT.open);
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-controls', 'nia-cb-panel');
		toggle.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>'; // SVG fixo, sem dados do usuario

		var panel = el('div', 'nia-cb-panel');
		panel.id = 'nia-cb-panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-label', TEXT.title);

		var header = el('div', 'nia-cb-header');
		header.appendChild(el('h2', 'nia-cb-title', TEXT.title));
		var closeBtn = el('button', 'nia-cb-close', '×');
		closeBtn.type = 'button';
		closeBtn.setAttribute('aria-label', TEXT.close);
		header.appendChild(closeBtn);

		var messages = el('div', 'nia-cb-messages');
		messages.setAttribute('role', 'log');
		messages.setAttribute('aria-live', 'polite');

		var form = el('form', 'nia-cb-form');
		var input = el('input', 'nia-cb-input');
		input.type = 'text';
		input.maxLength = MAX_LEN;
		input.placeholder = TEXT.placeholder;
		input.setAttribute('aria-label', TEXT.placeholder);
		input.autocomplete = 'off';
		var send = el('button', 'nia-cb-send', TEXT.send);
		send.type = 'submit';
		send.disabled = true;
		form.appendChild(input);
		form.appendChild(send);

		panel.appendChild(header);
		panel.appendChild(messages);
		panel.appendChild(form);
		root.appendChild(toggle);
		root.appendChild(panel);
		document.body.appendChild(root);

		var waiting = false;

		function addMessage(who, text, extra) {
			var m = el('div', 'nia-cb-msg nia-cb-msg-' + who + (extra ? ' ' + extra : ''), text);
			messages.appendChild(m);
			messages.scrollTop = messages.scrollHeight;
			return m;
		}

		function updateSend() {
			send.disabled = waiting || input.value.trim() === '';
		}

		function setOpen(open) {
			panel.classList.toggle('nia-cb-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			toggle.setAttribute('aria-label', open ? TEXT.close : TEXT.open);
			if (open) {
				if (!messages.children.length) { addMessage('bot', TEXT.welcome); }
				input.focus();
			} else {
				toggle.focus();
			}
		}

		toggle.addEventListener('click', function () { setOpen(!panel.classList.contains('nia-cb-open')); });
		closeBtn.addEventListener('click', function () { setOpen(false); });
		panel.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setOpen(false); } });
		input.addEventListener('input', updateSend);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var text = input.value.trim().slice(0, MAX_LEN);
			if (!text || waiting) { return; }
			addMessage('user', text);
			input.value = '';
			waiting = true;
			updateSend();
			var typing = addMessage('bot', TEXT.typing, 'nia-cb-msg-typing');
			// Etapa 2: resposta simulada. Etapa 3 troca este bloco por fetch() ao endpoint REST.
			window.setTimeout(function () {
				messages.removeChild(typing);
				addMessage('bot', TEXT.mock);
				waiting = false;
				updateSend();
				input.focus();
			}, 700);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
