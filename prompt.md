You are acting as a Senior WordPress Plugin Architect and Software Engineer.

Your role is NOT to immediately write code.
Your first responsibility is to design a clean, scalable, modular architecture for a custom WordPress plugin based on the requirements below.

We are building a production‑ready WordPress plugin.

--------------------------------------------------
PROJECT NAME
--------------------------------------------------

Broadcast Chat Plugin (One-Way Admin Broadcast Chat)

--------------------------------------------------
PROJECT PURPOSE
--------------------------------------------------

We need a WordPress plugin that visually looks like a chat system, but is actually a one‑way broadcast communication tool.

Visitors cannot send messages.
Only the admin can broadcast messages.
Messages are shown to all visitors who open a specific page.

This is NOT a real chat.
This is a broadcast announcement system presented as a chat UI.

--------------------------------------------------
CORE FUNCTIONAL REQUIREMENTS
--------------------------------------------------

1) User Flow

- Visitor opens a page where the chat widget is embedded.
- Immediately a modal asks ONLY for the user's name.
- No email, no phone, no extra data.
- After submitting:
  - User is registered in a custom database table.
  - A session ID is generated.
  - Session stored in localStorage (or similar lightweight persistence).
  - Chat UI becomes visible.

2) Chat Behavior

- Input field must be disabled.
- Placeholder text example: "گفتگو غیرفعال شده است"
- Users cannot send messages.
- Only admin messages exist.

3) Message Delivery

- Admin sends a broadcast message from WordPress admin panel.
- Message is stored in custom DB table.
- Frontend retrieves new messages using AJAX polling (every ~5 seconds).
- Strict realtime (websockets) is NOT required.

4) Message History

- When a new user opens the page,
  they must see previously broadcast messages.
- On initial load, frontend should fetch full message history (or recent limited history).

5) User Presence

We must track active users.

Each user record should include:

- id
- name
- session_id
- page_url
- created_at
- last_seen
- ip_address

Frontend must send a heartbeat request (every ~20 seconds)
to update last_seen.

Online users = last_seen within last 60 seconds.

6) Admin Panel

Inside WordPress admin:

- Broadcast message input
- Send button
- Online users counter

--------------------------------------------------
TECHNICAL REQUIREMENTS
--------------------------------------------------

This MUST be a clean, modular, maintainable WordPress plugin.

DO NOT build a single-file procedural plugin.

Follow professional WordPress architecture standards:

- OOP structure
- Namespaced classes (if appropriate)
- Separation of concerns
- Dedicated classes/modules for:
  - Plugin bootstrap
  - Database layer
  - Admin functionality
  - Frontend rendering
  - AJAX handlers
  - Assets management
- Proper use of hooks
- Proper script enqueueing
- Nonce validation
- Data sanitization and escaping
- Minimal performance overhead

--------------------------------------------------
DATABASE REQUIREMENTS
--------------------------------------------------

Use custom tables (created on plugin activation).

Example:

- wp_broadcastchat_users
- wp_broadcastchat_messages

DO NOT use WordPress posts table.
DO NOT use usermeta for this.

Include proper indexing (session_id, last_seen, message id).

--------------------------------------------------
FRONTEND REQUIREMENTS
--------------------------------------------------

Frontend should include:

- Name modal component
- Chat container component
- Disabled input
- Message list rendering
- Polling module
- Heartbeat module
- Auto scroll
- Clean UI separation from logic

JS must be modular and not messy.

--------------------------------------------------
ARCHITECTURE EXPECTATIONS
--------------------------------------------------

Before writing any code:

1) Analyze requirements.
2) Propose a clean plugin folder structure.
3) Propose class architecture.
4) Define responsibilities per class.
5) Define data flow (Frontend ↔ AJAX ↔ DB).
6) Identify security considerations.
7) Identify performance considerations.
8) Suggest improvements if needed.
9) Keep the system extensible.

--------------------------------------------------
PHASED DEVELOPMENT
--------------------------------------------------

In the root of the project there is a file:

phases.md

You MUST read that file and align your architecture with its phases.

We will implement this plugin phase-by-phase.
You are NOT allowed to generate the full plugin immediately.

First Step Now:
- Read phases.md
- Analyze requirements
- Propose architecture
- Confirm understanding
- Wait for me to tell you which phase to start implementing.

Do NOT write full implementation code yet.

Act as a senior architect.
Think long-term maintainability.
Design for production quality.