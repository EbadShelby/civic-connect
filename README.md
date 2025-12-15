
---

# **CivicConnect – Smart City Issue Reporter**

## **Overview**

CivicConnect is a web-based platform that allows citizens to report city issues such as potholes, trash, broken streetlights, and other public concerns. The system provides a geo-tagging map interface, upvoting features to prioritize reports, and an audit trail to ensure transparency when staff update issue statuses.

---

## **Tech Stack**

### **Frontend**

* **Vue.js** (^3.5.25) — progressive framework for building UIs
* **Vite** (^7.2.4) — fast build tool and dev server
* **Tailwind CSS** (^4.1.18) — utility-first CSS framework
* **Tailwind CSS Vite** (^4.1.18) — Vite plugin for Tailwind CSS
* **Axios** (^1.13.2) — HTTP client for API requests
* **Pinia** (^3.0.4) — state management library
* **Vue Router** (^4.6.3) — client-side routing
* **VeeValidate** (^4.15.1) — form validation library
* **Vue SweetAlert2** (^5.0.11) — beautiful confirmation dialogs
* **Vue Toastification** (^2.0.0-rc.5) — toast notifications
* **Vue Loading Overlay** (^6.0.6) — loading spinner component
* **Heroicons/Vue** (^2.2.0) — heroicons icon library for Vue
* **ESLint** (^9.39.1) — code linting
* **Prettier** (^3.6.2) — code formatting
* **Prettier Plugin Tailwind CSS** (^0.7.2) — Tailwind class sorting for Prettier
* **Tailwind CSS Motion** (^1.1.1) — animation utilities for Tailwind

### **Map**

* Leaflet.js
* OpenStreetMap

### **Backend**

* **PHP** (vanilla) — server-side logic
* **PHPMailer** — email notifications and OTP delivery
* **Dotenv** (phpdotenv) — environment variable management
* **Intervention Image** — image optimization and processing
* **MIME Type Validator** — file security and validation
* **Composer** — PHP dependency manager

### **Database**

* MySQL

### **Storage**

* Local storage (`/uploads`) for issue photos

---

## **Features**

### **Citizen**

* Submit issues (description, category, location, photo)
* Upvote existing issues
* Track status of submitted issues
* OTP verification for account or login
* Email verification upon registration
* Captcha to prevent bot signups
* Optional chatbot guidance for reporting issues

### **Staff**

* Login to staff dashboard (with OTP if desired)
* Change issue status (Pending → In Progress → Resolved)
* Audit trail auto-logged
* Filter issues by status or upvotes

### **Admin**

* Login to admin panel
* Create, update, and delete users
* Assign roles (Citizen / Staff)
* View all audit logs
* Monitor system stats (issues count, status distribution, top upvoted issues)

---

## **Database Tables**

### **1. users**

`id, name, email, password_hash, role, created_at`

### **2. issues**

`id, title, description, category, latitude, longitude, photo_path, reported_by, timestamp, status`

### **3. upvotes**

`id, user_id, issue_id, timestamp`

### **4. audit_trail**

`id, issue_id, staff_id, old_status, new_status, timestamp`

---

## **Setup**

### **1. Clone Repository**

```bash
git clone https://github.com/EbadShelby/CivicConnect.git
```

### **2. Database Setup**

* Create MySQL database `civicconnect`
* Import `database.sql`

### **3. Configure Backend**

* Inside `backend/`, copy `.env.example` → `.env`
* Update database credentials and email SMTP settings
* Install PHP dependencies:

  ```bash
  cd backend
  composer install
  ```

### **4. Start Server**

* Run XAMPP (Apache + MySQL)
* Access backend endpoints via:

  ```
  http://localhost/CivicConnect/backend/
  ```

### **5. Frontend Setup**

```bash
cd frontend
npm install
npm run dev
```

### **6. Access Frontend**

```
http://localhost:5173/
```

---

## **Node.js & Environment Requirements**

* **Node.js**: ^20.19.0 or >=22.12.0
* **npm**: ^11.7.0
* **PHP**: 7.4+ (recommended 8.0+)
* **MySQL**: 5.7+

---

## **Folder Structure**

```bash
CivicConnect/
├─ frontend/                # Vue app (UI)
│  ├─ src/
│  ├─ public/
│  └─ package.json
│
├─ backend/                 # PHP API
│  ├─ api/
│  ├─ config/
│  ├─ vendor/               # Composer packages
│  ├─ uploads/              # Local image storage
│  └─ .env
│
├─ database.sql
├─ README.md
└─ .gitignore
```

---

## **Usage Workflow**

### **Citizen Flow**

1. Register or log in
2. Submit an issue using the report form
3. Select location on the map
4. Upload a photo (optional)
5. Other users upvote the issue
6. Citizen monitors issue status changes

### **Staff Flow**

1. Log in to staff panel
2. View upvoted or recent issues
3. Change issue status (Pending → In Progress → Resolved)
4. Audit trail automatically logs action

---

## **License**

**MIT License** — free to use, modify, and distribute for academic or personal projects.

---
