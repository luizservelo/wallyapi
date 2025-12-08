# 📖 Índice da Documentação do WallyAPI

> Guia de navegação por toda a documentação do projeto

---

## 🎯 Para Qual Público?

### 👨‍💻 Desenvolvedores Humanos
Se você é um desenvolvedor trabalhando com WallyAPI, comece aqui:

1. **README.md** ⭐ COMECE AQUI
   - Visão geral do projeto
   - Guia de instalação e configuração
   - Exemplos básicos de uso
   - Referência rápida de funcionalidades

---

### 🤖 Assistentes de IA (Cursor, GitHub Copilot, etc.)

Se você é uma IA ajudando no desenvolvimento do WallyAPI, consulte estes arquivos na ordem:

1. **AI_PROJECT_GUIDE.md** ⭐ DOCUMENTAÇÃO COMPLETA
   - Visão geral detalhada da arquitetura
   - Estrutura completa de diretórios
   - Todos os componentes Core explicados
   - Fluxo completo de requisição
   - Convenções e padrões de código
   - Exemplos práticos de implementação
   - Checklist de validação
   
   **Quando usar:** Para entender profundamente o projeto ou implementar funcionalidades complexas.

2. **AI_QUICK_REFERENCE.md** ⚡ REFERÊNCIA RÁPIDA
   - Templates prontos (Controller, Model, Middleware)
   - Queries comuns de Model
   - Padrões de rota
   - Exemplos de autenticação
   - Comandos Docker
   - Checklist rápido
   
   **Quando usar:** Para implementar CRUD, criar recursos, ou resolver problemas comuns rapidamente.

3. **AI_FLOW_EXAMPLES.md** 🔄 EXEMPLOS DE FLUXO
   - Fluxos completos de requisição
   - Exemplos visuais com diagramas
   - Como $data, $middlewareData e $injectedData funcionam
   - Exemplos de autenticação passo a passo
   - Upload de arquivos
   - Relacionamentos entre Models
   
   **Quando usar:** Para entender como os dados fluem através do sistema ou debugar problemas.

4. **.cursorules** ⚙️ REGRAS DO CURSOR AI
   - Convenções de código
   - Padrões de nomenclatura
   - Regras de segurança
   - Templates de implementação
   - Avisos sobre arquivos Core
   
   **Quando usar:** Automaticamente carregado pelo Cursor AI.

---

## 📂 Arquivos por Categoria

### 📘 Documentação Geral
- `README.md` - Documentação principal para usuários
- `DOCUMENTATION_INDEX.md` - Este arquivo (índice)

### 🤖 Documentação para IAs
- `AI_PROJECT_GUIDE.md` - Guia completo e detalhado
- `AI_QUICK_REFERENCE.md` - Referência rápida
- `AI_FLOW_EXAMPLES.md` - Exemplos de fluxo de dados
- `.cursorules` - Regras para Cursor AI

### 🔧 Código do Projeto
- `www/` - Todo o código da aplicação
- `docker-compose.yml` - Configuração Docker
- `Dockerfile` - Build da imagem

---

## 🎓 Guia de Estudo (Para IAs)

### Nível 1: Conhecimento Básico (15 min)
1. Ler `AI_QUICK_REFERENCE.md` completo
2. Entender estrutura de Controller, Model, Middleware
3. Memorizar assinaturas de métodos

### Nível 2: Conhecimento Intermediário (30 min)
1. Ler seções de "Componentes Core" no `AI_PROJECT_GUIDE.md`
2. Estudar exemplos em `AI_FLOW_EXAMPLES.md`
3. Entender fluxo completo de requisição

### Nível 3: Conhecimento Avançado (1 hora)
1. Ler `AI_PROJECT_GUIDE.md` completo
2. Estudar todos os fluxos em `AI_FLOW_EXAMPLES.md`
3. Revisar código em `www/app/Core/`

### Nível 4: Domínio Total (2 horas)
1. Toda documentação AI
2. Código-fonte completo
3. Exemplos práticos de implementação

---

## 🔍 Busca Rápida

### Como fazer X?

#### Criar novo recurso (CRUD completo)
→ `AI_QUICK_REFERENCE.md` - Seção "Criar Novo Recurso"

#### Implementar autenticação
→ `AI_QUICK_REFERENCE.md` - Seção "Auth - Login e Register"  
→ `AI_FLOW_EXAMPLES.md` - Fluxo 2

#### Fazer upload de arquivo
→ `AI_FLOW_EXAMPLES.md` - Fluxo 5

#### Criar relacionamentos entre tabelas
→ `AI_FLOW_EXAMPLES.md` - Fluxo 6

#### Entender fluxo de dados
→ `AI_FLOW_EXAMPLES.md` - Diagrama: Composição de $data

#### Criar middleware customizado
→ `AI_QUICK_REFERENCE.md` - Seção "Middleware - Template AuthMiddleware"  
→ `AI_PROJECT_GUIDE.md` - Seção "Middlewares"

#### Queries avançadas no Model
→ `AI_PROJECT_GUIDE.md` - Seção "Models"  
→ `AI_QUICK_REFERENCE.md` - Seção "Model - Queries Comuns"

#### Configurar CORS
→ `AI_PROJECT_GUIDE.md` - Buscar "CORS"

#### Trabalhar com JWT
→ `AI_PROJECT_GUIDE.md` - Buscar "JWT"  
→ `AI_QUICK_REFERENCE.md` - Seção "Utilidades Core"

---

## 📋 Checklist para IAs

Antes de gerar código, verifique:

### ✅ Informações Básicas
- [ ] Conheço a arquitetura geral (MVC)?
- [ ] Entendo os 3 parâmetros do controller?
- [ ] Sei como rotas funcionam?

### ✅ Convenções
- [ ] Namespaces corretos?
- [ ] Nomenclatura seguindo padrões?
- [ ] Extends da classe correta?

### ✅ Segurança
- [ ] Validações implementadas?
- [ ] Senhas hasheadas?
- [ ] Tokens verificados?
- [ ] Dados sensíveis não expostos?

### ✅ Qualidade
- [ ] Status codes HTTP corretos?
- [ ] Tratamento de erros implementado?
- [ ] Response no formato padrão?
- [ ] Código documentado?

---

## 🚀 Fluxo de Trabalho Recomendado para IAs

### Ao receber uma solicitação:

```
1. ANALISAR
   ├─ Ler a solicitação
   ├─ Identificar tipo de tarefa
   └─ Determinar qual documentação consultar

2. CONSULTAR DOCUMENTAÇÃO
   ├─ Tarefa simples → AI_QUICK_REFERENCE.md
   ├─ Tarefa complexa → AI_PROJECT_GUIDE.md
   └─ Dúvida de fluxo → AI_FLOW_EXAMPLES.md

3. PLANEJAR
   ├─ Listar arquivos a criar/modificar
   ├─ Verificar convenções
   └─ Validar segurança

4. IMPLEMENTAR
   ├─ Gerar código seguindo padrões
   ├─ Adicionar validações
   ├─ Implementar tratamento de erros
   └─ Documentar código

5. VALIDAR
   ├─ Verificar checklist
   ├─ Confirmar padrões
   └─ Revisar segurança
```

---

## 💡 Dicas para IAs

### ✨ Boas Práticas
1. **SEMPRE** consulte a documentação antes de gerar código
2. **SEMPRE** siga as convenções de nomenclatura
3. **SEMPRE** valide inputs no controller
4. **SEMPRE** use os métodos utilitários ($this->response(), $this->error())
5. **SEMPRE** implemente tratamento de erros

### ⚠️ Armadilhas Comuns
1. ❌ Esquecer de adicionar rotas em `routes/api.php`
2. ❌ Usar namespace errado
3. ❌ Esquecer validações
4. ❌ Retornar senhas em responses
5. ❌ Status codes HTTP incorretos
6. ❌ Não tratar erros de Model (user->fail())

### 🔧 Quando Modificar Arquivos Core
Os arquivos em `app/Core/` são o núcleo do framework.
**APENAS modifique se:**
- Houver um bug crítico
- Solicitação explícita do usuário
- Melhoria de segurança necessária

**SEMPRE avise o usuário antes de modificar Core!**

---

## 📞 Suporte

### Para Desenvolvedores
- GitHub: https://github.com/luizservelo/wallyapi
- Issues: Para reportar bugs ou sugerir features

### Para IAs
- Consulte sempre os arquivos AI_*.md primeiro
- Use .cursorules como referência de convenções
- Em caso de dúvida, peça esclarecimento ao usuário

---

## 🔄 Atualizações

Esta documentação foi criada em **Dezembro de 2025**.

**Para IAs:** Se encontrar inconsistências entre a documentação e o código:
1. Priorize o código-fonte atual
2. Informe o usuário sobre a discrepância
3. Sugira atualização da documentação

---

## 📊 Estatísticas da Documentação

| Arquivo | Tamanho | Público | Prioridade |
|---------|---------|---------|------------|
| README.md | Médio | Desenvolvedores | ⭐⭐⭐⭐⭐ |
| AI_PROJECT_GUIDE.md | Grande | IAs | ⭐⭐⭐⭐⭐ |
| AI_QUICK_REFERENCE.md | Médio | IAs | ⭐⭐⭐⭐ |
| AI_FLOW_EXAMPLES.md | Grande | IAs | ⭐⭐⭐ |
| .cursorules | Pequeno | Cursor AI | ⭐⭐⭐⭐ |

---

**Documentação Completa e Estruturada ✅**

*Este índice ajuda a navegar por toda a documentação do WallyAPI, seja você um desenvolvedor humano ou uma IA assistente.*

