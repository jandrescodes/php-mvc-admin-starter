# 🤖 Configuración de IA y Agentes para el Proyecto

Este proyecto cuenta con configuraciones estándar para herramientas de Inteligencia Artificial (especialmente Claude Code) que asisten en el desarrollo.

Aquí se explica la arquitectura de nuestra configuración de IA y cómo puedes aprovecharla.

## 🗂 Estructura de Archivos IA

### 1. `.claude/skills/`

- **¿Qué es?** Habilidades e instrucciones específicas hechas a medida para interactuar con la consola interactiva de [Claude Code](https://docs.anthropic.com/en/docs/agents-and-tools/claude-code/overview). Por ejemplo, flujos definidos de code-review o git-commit.
- **¿Se sube a Git?** **SÍ**. Está configurado en el `.gitignore` para compartir _sólo_ la subcarpeta de `/skills`, omitiendo tu historial o configuraciones personales de esa herramienta.

### 2. Servidores MCP (`.mcp.json` y `.mcp.example.json`)

- **¿Qué son?** El Model Context Protocol (MCP) conecta nuestros agentes de IA con el ecosistema externo (GitHub, Bases de Datos, Clickup).
- **¿Se sube a Git?** **NO se sube `.mcp.json`**, porque contiene tus credenciales de base de datos locales. **SÍ se sube `.mcp.example.json`**, que es una plantilla para que los nuevos desarrolladores sepan qué contenedores instanciar.
- **¿Cómo usarlo?** Renombra o copia `.mcp.example.json` como `.mcp.json` y rellena `your_db_password_here`, etc., de la base de datos de tu local.

### 3. Permisos de Bash (`.claude/settings.example.json`)

- **¿Qué es?** Un ejemplo de permisos para auto-aprobar comandos repetitivos en Claude (ej: `git`, `php`, `ls`).
- **¿Cómo usarlo?** Puedes copiar su contenido en tu archivo privado `.claude/settings.json` o aceptar cada comando de consola de manera manual según prefieras.

---

> Procura siempre mantener tus credenciales fuera de `.claude/skills/`, usando variables de entorno o el archivo privado `.mcp.json`.
