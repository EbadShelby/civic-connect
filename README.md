
---

# **CivicConnect – Smart City Issue Reporter**

## **Overview**

CivicConnect is a web-based platform that allows citizens to report city issues such as potholes, trash, broken streetlights, and other public concerns. The system provides a geo-tagging map interface, upvoting features to prioritize reports, and an audit trail to ensure transparency when staff update issue statuses.

---

## **Tech Stack**

### **Frontend**

* **Vue.js** (^3.5.25) — progressive UI framework
* **Vite** (^7.2.4) — fast build tool and dev server
* **Tailwind CSS** (^4.1.18) — utility-first CSS framework
* **Axios** (^1.13.2) — HTTP client
* **Pinia** (^3.0.4) — state management
* **Vue Router** (^4.6.3) — client-side routing
* **Vue Toastification** — toast notifications
* **Vue Loading Overlay** — loading indicators
* **Heroicons/vue** — icons
* **ESLint + Prettier** — linting and formatting

### **Map**

* Leaflet.js
* OpenStreetMap

### **Backend**

* **PHP** (vanilla)
* **PHPMailer** — email, OTP, verification
* **Dotenv (phpdotenv)** — environment variables
* **Composer** — PHP dependency manager

### **Database**

* MySQL

### **Storage**

* Local storage (`/backend/uploads`) for issue images

---

## **Features**

### **Citizen**

* **Submit Issues**: Report problems with description, category, location (map pin), and photo.
* **My Issues**: View and track status of issues you have reported.
* **Upvote**: Upvote existing issues to increase priority.
* **Track Status**: Receive updates when issue status changes.
* **Security**:
    * Email verification on signup.

    * OTP verification for login (optional/conditional).
    * Captcha protection.
* **Notifications**: 
    * In-app notification center for status updates.
    * Email alerts for issue status changes.
* **Chatbot Assistance**: (Coming Soon)

### **Staff**

* **Dashboard**: View assigned or relevant issues.
* **Issue Management**: Update status (Pending → In Progress → Resolved).
* **Audit Trail**: all actions are automatically logged.
* **Filtering**: Sort/filter issues by status or upvotes.

### **Admin**

* **Admin Dashboard**: Overview of system activity.
* **User Management**: Manage users, assign roles (Citizen / Staff).
* **Staff Management**: Create and manage staff accounts.
* **Audit Logs**: Full transparency with efficient audit log viewing.
* **System Stats**: View key metrics.

---

## **Database Tables**

### **users**
`id, email, password_hash, first_name, last_name, phone, profile_image, bio, location, email_verified, otp_code, role, last_login, created_at`

### **issues**
`id, user_id, title, description, category, location, latitude, longitude, status, priority, image_path, upvote_count, is_anonymous, created_at, resolved_at`

### **upvotes**
`id, issue_id, user_id, created_at`

### **notification**
`id, user_id, issue_id, type, title, message, old_status, new_status, is_read, created_at`

### **issue_updates**
`id, issue_id, user_id, update_type, content, old_status, new_status, created_at`

### **audit_trail**
`id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at`

### **sessions**
`id, user_id, token_hash, ip_address, user_agent, expires_at, created_at`

### **password_resets**
`id, user_id, token_hash, expires_at, created_at`

---

## **Environment Requirements**

* **Node.js**: ^20.19.0 or >=22.12.0
* **npm**: ^11.7.0
* **PHP**: 7.4+ (recommended 8.0+)
* **MySQL**: 5.7+

---

## **VScode Extension Requirements**
* **Vue (official)**
* **Todo+**
* **Eslint**
* **Prettier**
* **Tailwind CSS Intellisense**

---

## **Setup & Development Workflow**

### **1. Fork & Clone Repository**

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/civic-connect.git
cd civic-connect

# Add upstream (if working in a team)
git remote add upstream https://github.com/EbadShelby/civic-connect.git
```

### **2. Database Setup**

1.  Create a MySQL database named `civicconnect`.
2.  Import the schema:
    ```sql
    source database.sql;
    ```
3.  **Create Admin Account**:
    Register a new user via the frontend (register page), then run the migration script to promote them to admin:
    ```bash
    php migrate_admin.php
    ```

### **3. Backend Setup**

```bash
cd backend
cp .env.example .env
composer install
```

Update `.env` with your credentials:
*   Database host, user, password, name
*   SMTP settings for email (Gmail/Mailtrap)

**Run Backend**:
Ensure your web server (Apache/Nginx via XAMPP/MAMP) points to the project root or `backend` folder.
Base URL in frontend config is set to: `http://localhost/civic-connect/backend/api` (Adjust in `frontend/src/stores/` if different).

### **4. Frontend Setup**

```bash
cd frontend
npm install
npm run dev
```

---

## **Folder Structure**

```
civic-connect/
├─ frontend/                # Vue 3 + Vite App
│  ├─ src/
│  │  ├─ views/             # Page components (Citizen, Staff, Admin)
│  │  ├─ stores/            # Pinia state stores
│  │  └─ router/            # Vue router config
│  └─ package.json
│
├─ backend/                 # PHP API
│  ├─ api/
│  │  └─ controllers/       # Logic for Issues, Users, Staff
│  ├─ config/               # DB & Mailer config
│  ├─ uploads/              # Stored images
│  └─ composer.json
│
├─ database.sql             # SQL Schema
├─ migrate_admin.php        # Utility to create admin
└─ README.md
```

---

## **License**

**MIT License** — free to use, modify, and distribute for academic or personal projects.

---

---

## **API Reference**

### **Notifications**

*   `GET /api/notifications` - Fetch list of notifications (Supports pagination).
*   `GET /api/notifications/unread-count` - Get count of unread notifications.
*   `PUT /api/notifications/{id}/read` - Mark a specific notification as read.
*   `PUT /api/notifications/mark-all-read` - Mark all notifications as read.
