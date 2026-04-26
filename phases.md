WordPress Broadcast Chat Plugin - Project Phases

Phase 1 – Project Initialization
- Create base plugin folder structure
- Create main plugin bootstrap file
- Define constants (plugin path, URL, version)
- Register activation and deactivation hooks

Phase 2 – Database Layer
- Create custom database tables on plugin activation
- Tables:
  - wp_livechat_users
  - wp_livechat_messages
- Add indexes for performance
- Build database helper functions

Phase 3 – Core Plugin Architecture
- Implement modular file loading system
- Separate folders for:
  - admin
  - public
  - ajax
  - database
- Create loader class for hooks

Phase 4 – Frontend Asset System
- Register and enqueue CSS/JS only on pages where shortcode exists
- Localize script variables (ajax_url, nonce, polling interval)

Phase 5 – Chat UI Component
- Build chat container UI
- Build read-only input field
- Build message list UI
- Implement modal for collecting user name

Phase 6 – User Session System
- Generate unique session ID
- Save username in localStorage
- Register user in database via AJAX
- Implement heartbeat (last_seen updates)

Phase 7 – Message Retrieval (Polling)
- Implement AJAX endpoint for fetching messages
- Poll server every few seconds
- Load full history for new visitors
- Load only new messages using last_id

Phase 8 – Admin Broadcast Panel
- Add admin menu page
- Build broadcast message form
- Insert admin messages into database
- Show number of currently online users

Phase 9 – Shortcode & Page Integration
- Create shortcode for embedding chat module
- Render chat UI via shortcode
- Allow placement on any page

Phase 10 – Optimization & Security
- Sanitize all inputs
- Add nonce verification
- Optimize polling queries
- Limit message history
- Cleanup inactive users
- Final UI polish and testing
