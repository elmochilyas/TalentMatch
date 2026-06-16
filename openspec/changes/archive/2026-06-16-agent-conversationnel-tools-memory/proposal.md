## Why

RH agents currently receive structured AI analysis for each candidate, but cannot interact with the data conversationally. They must manually scan analysis pages to compare candidates, identify gaps, or prepare interview questions. A conversational assistant backed by real Laravel tools solves this: agents can ask natural questions about individual candidates, compare saved analyses, and get instant answers — all within the existing auth and ownership model.

This builds directly on the structured analysis foundation now complete. The laravel/ai SDK, conversation memory tables, and Blade UI are ready — only the assistant orchestration, tool definitions, and chat interface remain.

## What Changes

- Register three real Laravel AI SDK tools: `getCandidateAnalysis`, `getJobRequirements`, `compareCandidates`
- Create a conversational chat endpoint inside the analysis detail page (scoped to authenticated user ownership)
- Add an assistant orchestration service that manages tool calls and conversation history via laravel/ai SDK
- Add a new Blade view for the chat UI (messages list + input form)
- Add Pest tests covering auth, ownership, tool responses, missing data handling, and follow-up context preservation
- No changes to the existing MCD/MLD — the `agent_conversations` and `agent_conversation_messages` tables already exist from the laravel/ai SDK migration
- No changes to the AnalyseCandidat model, job, or AI analysis pipeline

## Capabilities

### New Capabilities
- `assistant-conversationnel`: AI-powered assistant with real Laravel tool calling and conversation memory, scoped to authenticated RH agent's own candidate analyses

### Modified Capabilities
*(None — no existing spec requirements change)*

## Impact

- **Routes**: one new GET/POST route pair for assistant messages under a candidate analysis
- **Controllers**: one new controller (thin, delegates to service)
- **Services**: `AssistantService` (orchestrates tools + conversation memory)
- **Tools**: three callable Laravel AI SDK tools in `app/Ai/Tools/`
- **Views**: one new Blade partial (`offres/assistant-chat.blade.php`) injected into analysis detail view
- **Tests**: new `AssistantConversationTest.php` in `tests/Feature/`
- **No new migrations**, **no new models**, **no new enums**, **no new jobs**
