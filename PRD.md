# PRD.md — AniRank REST API (`/api/v1`)

## Overview
AniRank is a backend-only REST API for anime discovery, trivia games, and
community tier-list rankings, backed by data synced from Jikan v4.

## In Scope (MVP)

### Auth
- Sanctum token auth: register, login, logout.

### Data Sync
- Artisan command to sync Jikan v4 data: Fall 2026 season + Top 200 anime
  and their characters. No live API calls during requests.

### Arcade (public, `X-Guest-UUID` identified)
- **Daily Drop** — guess the anime from audio clip / cropped frame / silhouette.
- **MAL Clash** — Higher/Lower batches comparing anime stats (score, popularity, etc).
- **1v1 Arena** — bracket-style anime-vs-anime matchups.
- **Red Flag Scanner** — 5-character tag-scoring engine (rule-based scoring
  against character tags, zero AI API calls).

### Community (auth:sanctum for writes)
- Reddit-style **Tier List** community:
  - `tier_lists` resource, user-created.
  - Sort by Hot / Top / New.
  - Polymorphic votes (upvote/downvote on tier lists and comments).
  - 2-level nested comments (top-level + one reply level).

## Out of Scope
- Live WebSockets / real-time features.
- Direct messages between users.
- Image uploads for avatars.
- Admin web panel.

## Phases

### Phase 1 — Foundation & Core Data
- Sanctum auth (register/login/logout).
- Jikan v4 sync Artisan command (Fall 2026 season + Top 200 anime/characters).
- Guest identification middleware (`X-Guest-UUID`).
- Daily Drop + MAL Clash endpoints (read-heavy, guest-accessible).
- Pest test coverage for auth and sync command.

### Phase 2 — Games & Community
- 1v1 Arena bracket logic.
- Red Flag Scanner tag-scoring engine.
- Tier List community: CRUD, polymorphic votes, Hot/Top/New sorting,
  2-level nested comments.
- Pest test coverage for arcade scoring logic and community write routes.
