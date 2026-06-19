# Agent Configurations and Instructions (AGENTS.md)

This file defines the behavior, tools, and skills configuration for AI agents working in this repository.

## 1. Active Agents

- **Antigravity Coding Assistant (Default)**: Developer agent for PHP backend refactoring, security audits, and frontend CSS adjustments.

## 2. Configured System Skills

The agent is trained to discover and use the following skills installed in the system:

1. **reduce-token**: Minimizes token consumption, optimizes context, and ensures short responses.
2. **efficient-coding**: Ensures rapid syntax checking (e.g., `php -l`), testing via CLI scratch scripts, and fast debugging.

## 3. Developer Guidelines & Constraints

- **Language Style**: Responses must be concise, structured, and in GitHub-flavored Markdown. Avoid verbose introductory comments.
- **Verification Priority**: Run linting on all modified PHP files. Create one-off scripts in `scratch/` for testing instead of launching full browser instances unless explicitly requested.
- **Safety First**: Do not modify database schemas or encryption methods without explicit user request. Keep credentials in `.env` and load them dynamically.
