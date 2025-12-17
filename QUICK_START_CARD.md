# 🚀 QUICK START CARD - Routing & Authentication

## One-Page Reference

### Start Development
```bash
cd frontend
npm run dev
# Opens http://localhost:5173
```

### File Locations

| What | Where |
|------|-------|
| Auth Logic | `src/stores/authStore.js` |
| Issues Logic | `src/stores/issuesStore.js` |
| Routes Config | `src/router/index.js` |
| Access Guards | `src/router/guards.js` |
| All Pages | `src/views/` |
| App Shell | `src/App.vue` |

### Use Auth Store

```javascript
// In any component
import { useAuthStore } from '@/stores/authStore'
const auth = useAuthStore()

// Check status
if (auth.isAuthenticated) console.log('Logged in')
if (auth.isAdmin) console.log('Admin user')

// Take action
await auth.login('user@example.com', 'password')
await auth.logout()
```

### Use Issues Store

```javascript
import { useIssuesStore } from '@/stores/issuesStore'
const issues = useIssuesStore()

// Get data
await issues.fetchIssues()
console.log(issues.filteredIssues) // All issues

// Update data
await issues.createIssue(formData)
await issues.upvoteIssue(issueId)
await issues.addComment(issueId, text)
```

### Navigate Programmatically

```javascript
import { useRouter } from 'vue-router'
const router = useRouter()

router.push('/dashboard')
router.push({ name: 'issueDetail', params: { id: 123 } })
```

### Access Route Params

```javascript
import { useRoute } from 'vue-router'
const route = useRoute()

const id = route.params.id        // From URL path
const sort = route.query.sort     // From query string
```

### Guard Routes

```javascript
// Automatic - configured in routes
// OR manually in component:
if (!authStore.isStaff) {
  router.push('/unauthorized')
}
```

## Routes Reference

### 🌐 Public
- `/` Home
- `/register` Register
- `/login` Login
- `/verify-email` Verify
- `/forgot-password` Recovery

### 👤 Citizen
- `/dashboard` Dashboard
- `/issues` All issues
- `/issues/:id` Issue detail
- `/report-issue` Create
- `/my-issues` My reports
- `/profile` Profile
- `/edit-profile` Edit

### 👷 Staff
- `/staff/dashboard` Dashboard
- `/staff/issues` Manage
- `/staff/issues/:id` Detail
- `/staff/reports` Reports

### 🛡️ Admin
- `/admin/dashboard` Dashboard
- `/admin/users` Users
- `/admin/issues` Issues
- `/admin/audit-logs` Logs
- `/admin/analytics` Analytics

## API Base

All requests go to:
```
http://localhost/civic-connect/backend/api
```

With Bearer token automatically added:
```
Authorization: Bearer <token>
```

## Common Code Patterns

### Login Form
```vue
<script setup>
const email = ref('')
const password = ref('')
const authStore = useAuthStore()
const router = useRouter()

const login = async () => {
  try {
    await authStore.login(email.value, password.value)
    router.push('/dashboard')
  } catch (e) {
    console.error(authStore.error)
  }
}
</script>

<template>
  <form @submit.prevent="login">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button type="submit" :disabled="authStore.isLoading">Sign In</button>
    <div v-if="authStore.error" class="error">{{ authStore.error }}</div>
  </form>
</template>
```

### Issue List
```vue
<script setup>
import { onMounted } from 'vue'
import { useIssuesStore } from '@/stores/issuesStore'

const issues = useIssuesStore()

onMounted(async () => {
  await issues.fetchIssues()
})
</script>

<template>
  <div v-if="issues.isLoading">Loading...</div>
  <div v-else-if="issues.error" class="error">{{ issues.error }}</div>
  <div v-else class="grid gap-4">
    <div v-for="issue in issues.filteredIssues" :key="issue.id">
      <h3>{{ issue.title }}</h3>
      <p>{{ issue.description }}</p>
      <span>{{ issue.status }}</span>
    </div>
  </div>
</template>
```

### Protect Component
```vue
<script setup>
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

if (!auth.isAdmin) router.push('/unauthorized')
</script>

<template>
  <!-- Admin-only content -->
</template>
```

## Key Concepts

| Concept | Meaning |
|---------|---------|
| Guard | Route access level (PUBLIC/CITIZEN/STAFF/ADMIN) |
| Role | User permission level (citizen/staff/admin) |
| Store | State management (auth/issues) |
| Token | JWT authentication (in localStorage) |
| Redirect | Automatic navigation on unauthorized access |

## Troubleshooting

**Routes not loading?**
→ Check router is in main.js

**Can't login?**
→ Check backend is running on http://localhost/civic-connect/backend/api

**State lost on refresh?**
→ Check localStorage has "token" and "user" keys

**Can't access admin routes?**
→ Check user.role is "admin" in auth store

**Form not working?**
→ Check vee-validate is installed (npm install vee-validate)

## Next Steps

1. **Implement Auth Pages**
   - Add form inputs to login/register
   - Connect to authStore.login/register
   - Add validation

2. **Build Issue Pages**
   - Fetch issues with issuesStore.fetchIssues
   - Display in UI
   - Add filters

3. **Add Features**
   - Image uploads
   - Map picker
   - Comments
   - Upvotes

## Resources

- Vue Docs: https://vuejs.org
- Router Docs: https://router.vuejs.org
- Pinia Docs: https://pinia.vuejs.org
- Tailwind Docs: https://tailwindcss.com

---

**Status**: ✅ Ready to implement  
**Next**: Add forms to auth pages  
**Time Est**: 2-3 hours for auth pages

**Happy Coding! 🎉**
