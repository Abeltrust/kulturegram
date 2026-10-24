# Kulturegram 🌍
> **Preserve Authentic Journeys, Cultural Heritage, and Living Legacies.**

Kulturegram is a modern cultural storytelling and heritage preservation platform. It empowers communities, historians, creators, and families around the globe to document traditions, share memories, host cultural gatherings, teach masterclasses, and safeguard generational artifacts in private digital vaults.

---

## ✨ Core Pillars & Features

### 🏛️ Cultural Hub & Heritage Archive
- **Community Submissions**: Submit cultural practices, oral histories, indigenous crafts, and folklore.
- **Administrative Moderation**: Dedicated admin review center (`/admin/cultures`) to inspect, verify, feature, or manage submissions before public dissemination.
- **Discovery & Search**: Browse traditions by endangerment level, category, geographical region, or historical period.

### 📜 Chronological Timeline Feed
- **Living Timeline**: Share stories, memories, milestones, and life chapters in an authentic, distraction-free chronological feed.
- **Cultural Resonance & Taps**: Express appreciation and connection through Resonance points and Taps.
- **Lock-In Connections**: Build lasting ties with guardians and storytellers who share common roots.

### 👥 Tribes & Communities
- **Community Hubs**: Join specialized cultural circles, historical societies, and craft collectives.
- **Interactive Discussions**: Create rich posts with photos, videos, and audio; engage in nested discussions and live vibes.
- **Role-Based Management**: Community creators and admins manage membership, guidelines, and settings.

### 🎟️ Gatherings, Workshops & Kulturegram Meet
- **In-Person & Online Events**: Organize cultural festivals, cooking tutorials, language lessons, and ceremonies.
- **Ticketing & Contributions**: Built-in ticket reservations and voluntary patron contributions.
- **Virtual Classroom (Kulturegram Meet)**: Instant interactive video classrooms with screen-sharing and live discussions for registered attendees.

### 🔐 Personal Heritage Vault
- **Digital Legacy**: Safeguard heirloom photographs, family records, audio recordings, and ancestral documents.
- **Granular Access Control**: Select access permissions per item—*Private*, *Family*, *Research*, or *Public*—enforced by secure policies and protected local storage.

### 🎥 Live Cultural Broadcasts
- **Live Streaming**: Stream ceremonies, drumming workshops, and traditional dances with low-latency WebRTC camera access.
- **Interactive Chat**: Real-time reactions, message replies, and audience vibe meters.

### 👤 User Profile, Life Chapters & Digital Dashboard
- **Profile Customization**: Instant avatar and custom cover banner uploading (`/profile`).
- **Comprehensive Hub**: Centralized dashboard unifying personal stories, chronological life chapters, submitted cultural traditions with real-time review statuses, joined tribes, attended gatherings (with instant classroom access), and personal vault heirlooms.
- **Life Chapters**: Chronicle significant life milestones and cultural rites of passage with dates and descriptions.
- **Account & Security Settings**: Granular multi-tab settings hub (`/settings/{id}`) for personal details, cultural heritage markers, security/password updates, notification preferences, privacy visibility, and account deletion.

### 🛡️ Indigenous Data Sovereignty & Heritage Privacy
- **Dedicated Privacy Policy (`/privacy`)**: Rooted in indigenous data sovereignty principles (CARE and FAIR), personal vault tier security, and strict commitments against commercial data selling.
- **Terms of Service (`/terms`)**: Guidelines for cultural authenticity, elder knowledge protection, masterclass etiquette, and respectful community discourse.

---

## 🛠️ Technology Stack

- **Backend**: [Laravel 10](https://laravel.com) (PHP 8.2+)
- **Frontend**: Blade Templates, [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev)
- **Icons**: Lucide Icons
- **Database**: MySQL (Production/Local), SQLite in-memory (Test suite)
- **Virtual Meetings**: WebRTC / Jitsi Meet Integration
- **Testing**: PHPUnit / Laravel Feature Testing Suite (54 passing feature tests, 100% pass rate)

---

## 🚀 Getting Started

### 1. Prerequisites
- PHP >= 8.2 with `pdo_sqlite`, `pdo_mysql`, `mbstring`, `fileinfo` extensions enabled
- Composer >= 2.0
- Node.js >= 18.0 & NPM

### 2. Installation

```bash
# Clone or navigate into the project directory
cd c:\Timeline

# Install backend dependencies
composer install

# Install frontend dependencies and build assets
npm install
npm run build
```

### 3. Environment Configuration

Ensure your `.env` file exists and is configured:

```ini
APP_NAME=Kulturegram
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=timeline
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup & Seeding

```bash
# Run migrations
php artisan migrate

# Seed database with sample data and admin account
php artisan db:seed
```

### 5. Running the Application

```bash
# Start local development server
php artisan serve
```

Access the platform in your browser at: **`http://127.0.0.1:8000`**

---

## 🛡️ Default Administrator Account

An administrative account is automatically seeded for platform management:
- **Email:** `admin@kulturegram.com`
- **Password:** `password`
- **Admin Center URL:** `http://127.0.0.1:8000/admin/cultures`

---

## 🧪 Automated Test Suite

Run the full automated test suite containing 54 feature tests:

```bash
php artisan test
```

Test suites cover:
- `Tests\Feature\AdminModerationTest` (Permissions, approvals, featuring, rejections)
- `Tests\Feature\EventCrudTest` (Creation, editing, ticketing, registration, deletion)
- `Tests\Feature\CommunityCrudTest` (Creation, settings, posts, interactions, membership)
- `Tests\Feature\VaultCrudTest` (Uploads, privacy policy enforcement, updates, downloads)
- `Tests\Feature\Auth\*` (Login, registration, password resets, email verification)
- `Tests\Feature\ProfileTest` (Profile dashboard, avatar/cover uploads, life chapters, settings, privacy & terms pages, account deletion)

---

## 📜 License

Kulturegram is open-sourced under the [MIT license](LICENSE).
