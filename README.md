
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
* **VeeValidate** (^4.15.1) — form validation
* **Vue Toastification** — toast notifications
* **Vue Loading Overlay** — loading indicators
* **fontawesome** — icons
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

* Submit issues (description, category, location, photo)
* Upvote existing issues
* Track issue status
* Email verification
* OTP verification (login or sensitive actions)
* Captcha protection
* Optional chatbot assistance

### **Staff**

* Staff dashboard
* Update issue status (Pending → In Progress → Resolved)
* Automatic audit trail logging
* Filter issues by status or upvotes

### **Admin**

* Admin dashboard
* Manage users and roles (Citizen / Staff)
* View all audit logs
* View system statistics

---

## **Database Tables**

### **users**

`id, name, email, password_hash, role, created_at`

### **issues**

`id, title, description, category, latitude, longitude, photo_path, reported_by, timestamp, status`

### **upvotes**

`id, user_id, issue_id, timestamp`

### **audit_trail**

`id, issue_id, staff_id, old_status, new_status, timestamp`

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

### **1. Fork & Clone Repository (Team Members)**

> Team members must **fork** the repository first.

```bash
git clone https://github.com/YOUR_USERNAME/civic-connect.git
cd civic-connect
```

⚠️ Do not clone the main repository directly unless you are the project lead.

---

### **2. Add Upstream Remote (One-Time Setup)**

```bash
git remote add upstream https://github.com/EbadShelby/civic-connect.git
git remote -v
```

---

### **3.One-time global setup (EVERY TEAM MEMBER)

Add this to the workflow (VERY IMPORTANT):

```bash
git config --global pull.rebase false
```

✅ This tells Git:

“Always use merge when pulling.”

---

### **4. Sync With Main Repository (Before Coding)**

```bash
git pull upstream main
```

Always run this before starting new work.

---

### **4. Database Setup**

* Create a MySQL database named `civicconnect`
* Import `database.sql`

---

### **5. Backend Setup**

```bash
cd backend
cp .env.example .env
composer install
```

Update `.env` with:

* Database credentials
* SMTP email settings

Run XAMPP (Apache + MySQL)

Backend base URL:

```
http://localhost/civic-connect/backend/
```

---

### **6. Frontend Setup**

```bash
cd frontend
npm install
npm run dev
```

Frontend URL:

```
http://localhost:5173/
```

---

### **7. Commit, Push & Pull Request (Team Members)**

```bash
git add .
git commit -m "[frontend] Add issue reporting UI"
git push origin main
```

Then:

* Open your fork on GitHub
* Click **Compare & Pull Request**
* Describe your changes clearly

All changes must be reviewed before merging.

---

## **Repository Rules**

* Only the project lead merges to `main`
* Direct pushes to `main` are disabled
* All changes go through Pull Requests
* No branching is used in this project

---

## **Folder Structure**

```
civic-connect/
├─ frontend/                # Vue app
│  ├─ src/
│  ├─ public/
│  └─ package.json
│
├─ backend/                 # PHP API
│  ├─ api/
│  ├─ config/
│  ├─ vendor/
│  ├─ uploads/
│  └─ .env
│
├─ database.sql
├─ README.md
└─ .gitignore
```

---

## **Usage Workflow**

### **Citizen Flow**

1. Register and verify email
2. Log in (OTP if enabled)
3. Report an issue with map pin
4. Upload photo (optional)
5. Track status and receive updates

### **Staff Flow**

1. Log in to staff dashboard
2. Review reported issues
3. Update issue status
4. Actions logged in audit trail

---

## **License**

**MIT License** — free to use, modify, and distribute for academic or personal projects.

---
