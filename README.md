# NIA Chatbot Gemini

Plugin WordPress que exibe um chatbot no site da FATEB, com respostas geradas pela API Google Gemini. A chave da API fica no servidor; o navegador conversa apenas com o WordPress.

Projeto NIA-FATEB — TAP `NIA-TAP-2026.09.001-1` (v1.0, 24/09/2026), MVP em 90 dias.

## Arquitetura

```
Navegador (widget JS) → REST API do WordPress → API Gemini
                      ←                       ←
```

O frontend nunca acessa o Gemini diretamente.

## Ambiente de referência

| Item | Versão |
|---|---|
| WordPress | 7.1.3 |
| PHP | 7.4.33 (não pode ser atualizado) |
| Sistema operacional | Rocky Linux 8 |

**Regra de código:** compatível com PHP 7.4 e 8.x. Sem recursos de PHP 8+ e sem dependências do Composer.

## Estrutura planejada

```
nia-chatbot-gemini/
├── nia-chatbot-gemini.php      # cabeçalho e bootstrap
├── includes/
│   ├── class-plugin.php
│   ├── class-rest-controller.php
│   └── class-gemini-client.php
├── assets/
│   ├── js/chatbot.js
│   └── css/chatbot.css
├── languages/
├── docs/                       # requisitos, riscos, decisões, testes
└── README.md
```

## Instalação (homologação)

1. Copie a pasta `nia-chatbot-gemini` para `wp-content/plugins/`.
2. Ative em **Plugins** no painel do WordPress.
3. Se o PHP ou o WordPress não atenderem ao mínimo, o plugin não ativa e informa o motivo.

## Etapas do MVP

| Etapa | Conteúdo | Estado |
|---|---|---|
| 1 | Requisitos e preparação do ambiente | Em desenvolvimento |
| 2 | Plugin e interface | A fazer |
| 3 | API REST | A fazer |
| 4 | Integração com Gemini | A fazer |
| 5 | Contexto, segurança e erros | A fazer |
| 6 | Testes, correções e documentação | A fazer |

## Segurança

- A chave da API não é versionada nem exposta no navegador (será definida por constante no `wp-config.php`).
- Arquivos `.env`, `*.key` e `wp-config.php` estão no `.gitignore`.

## Fora do MVP

Histórico persistente, base de conhecimento (RAG), WhatsApp/CRM, múltiplos chatbots, analytics avançado e fine-tuning.

## Licença

GPL-2.0-or-later.
