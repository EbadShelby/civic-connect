# 🚀 Quick Reference: Using the Auth & Router System

## For Page Developers

### Accessing the Auth Store
```vue
<script setup>
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()

// Check authentication
if (authStore.isAuthenticated) {
  console.log('User:', authStore.user)
  console.log('Role:', authStore.userRole)
  console.log('Is Admin:', authStore.isAdmin)
}

// Perform actions
await authStore.login(email, password)
await authStore.logout()
await authStore.register(formData)
</script>

<template>
  <!-- Show content conditionally -->
  <div v-if="authStore.isAdmin">Admin only content</div>
</template>
```

### Accessing the Issues Store
```vue
<script setup>
import { useIssuesStore } from '@/stores/issuesStore'

const issuesStore = useIssuesStore()

// Fetch issues
await issuesStore.fetchIssues()

// Create issue
await issuesStore.createIssue(formData)

// Work with filters
issuesStore.setFilters({ 
  status: 'open', 
  category: 'pothole',
  sortBy: 'upvotes'
})

// Get filtered results
const filtered = issuesStore.filteredIssues

// Upvote
await issuesStore.upvoteIssue(issueId)

// Add comment
await issuesStore.addComment(issueId, commentText)
</script>
```

### Navigating Programmatically
```vue
<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

// Navigate
router.push('/dashboard')
router.push({ name: 'issueDetail', params: { id: 123 } })

// Go back
router.back()
</script>
```

### Using Route Parameters
```vue
<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

// Access route param
const issueId = route.params.id

// Access query string
const sortBy = route.query.sort
</script>
```

### Protecting Routes in Components
```vue
<script setup>
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

// Redirect if not staff
if (!authStore.isStaff) {
  router.push('/unauthorized')
}
</script>
```

## API Endpoints Reference

**Base URL**: `http://localhost/civic-connect/backend/api`

### User Endpoints
- `POST /users/register` - Register new user
- `POST /users/login` - Login user
- `POST /users/logout` - Logout user
- `POST /users/verify-email` - Verify email with OTP
- `POST /users/resend-otp` - Resend OTP
- `GET /users/{id}` - Get user profile
- `PUT /users/{id}` - Update user profile

### Issue Endpoints
- `POST /issues` - Create issue
- `GET /issues` - List all issues
- `GET /issues/{id}` - Get single issue
- `PUT /issues/{id}` - Update issue
- `DELETE /issues/{id}` - Delete issue

### Engagement Endpoints
- `POST /issues/{id}/upvotes` - Upvote issue
- `DELETE /issues/{id}/upvotes` - Remove upvote
- `GET /issues/{id}/upvotes` - Get upvotes
- `POST /issues/{id}/comments` - Add comment
- `GET /issues/{id}/comments` - Get comments
- `PUT /comments/{id}` - Update comment
- `DELETE /comments/{id}` - Delete comment

### File Endpoints
- `POST /upload/issue` - Upload issue image
- `POST /upload/profile` - Upload profile image
- `DELETE /files` - Delete file
- `GET /files/{filename}` - Get file info

## Available Stores & Methods

### Auth Store (`authStore`)
| Property | Type | Description |
|----------|------|-------------|
| `user` | ref | Current user object |
| `token` | ref | Auth token |
| `isAuthenticated` | computed | User logged in |
| `isAdmin` | computed | Is admin |
| `isStaff` | computed | Is staff |
| `isCitizen` | computed | Is citizen |
| `register(formData)` | method | Register new user |
| `login(email, password)` | method | Login user |
| `logout()` | method | Logout user |
| `verifyEmail(email, otp)` | method | Verify email |
| `resendOTP(email)` | method | Resend OTP |
| `fetchUserProfile(id)` | method | Get profile |
| `updateUserProfile(id, data)` | method | Update profile |

### Issues Store (`issuesStore`)
| Property | Type | Description |
|----------|------|-------------|
| `issues` | ref | All issues array |
| `currentIssue` | ref | Selected issue |
| `filteredIssues` | computed | Filtered issues list |
| `setFilters(filters)` | method | Apply filters |
| `resetFilters()` | method | Clear filters |
| `fetchIssues(params)` | method | Load issues |
| `fetchIssueById(id)` | method | Get issue detail |
| `createIssue(data)` | method | Create new issue |
| `updateIssue(id, data)` | method | Update issue |
| `deleteIssue(id)` | method | Delete issue |
| `upvoteIssue(id)` | method | Upvote issue |
| `removeUpvote(id)` | method | Remove upvote |
| `addComment(id, text)` | method | Add comment |
| `updateComment(id, text)` | method | Edit comment |
| `deleteComment(issueId, commentId)` | method | Remove comment |
| `fetchComments(issueId)` | method | Load comments |

## Common Patterns

### Loading State
```vue
<template>
  <div>
    <div v-if="issuesStore.isLoading" class="text-center">Loading...</div>
    <div v-else-if="issuesStore.error" class="text-red-600">{{ issuesStore.error }}</div>
    <div v-else>Issues: {{ issuesStore.issues.length }}</div>
  </div>
</template>
```

### Form Submission
```vue
<script setup>
const handleSubmit = async (formData) => {
  try {
    const result = await issuesStore.createIssue(formData)
    console.log('Success:', result.message)
    router.push({ name: 'issueDetail', params: { id: result.issue.id } })
  } catch (error) {
    console.error('Error:', issuesStore.error)
  }
}
</script>
```

### Conditional Rendering by Role
```vue
<template>
  <div>
    <div v-if="authStore.isAdmin">
      <button>Delete User</button>
    </div>
    <div v-else-if="authStore.isStaff">
      <button>Update Status</button>
    </div>
    <div v-else>
      <button>Report Issue</button>
    </div>
  </div>
</template>
```

---

**All files are ready!** Start implementing the pages with your specific UIs and forms.
