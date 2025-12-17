<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">Manage Users</h1>
        <p class="mt-1 text-sm text-gray-500">View users and manage their roles and permissions.</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Search -->
      <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="relative w-full max-w-md">
          <svg
            class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            v-model="searchQuery"
            @input="handleSearch"
            type="text"
            placeholder="Search by name or email..."
            class="w-full rounded-lg border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>

      <!-- Users Table -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div v-if="loading" class="flex h-64 items-center justify-center">
          <div
            class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"
          ></div>
        </div>

        <div
          v-else-if="users.length === 0"
          class="flex h-64 flex-col items-center justify-center py-12 text-center"
        >
          <h3 class="mt-2 text-sm font-semibold text-gray-900">No users found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your search terms.</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                User
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Role
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Status
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Joined
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Last Login
              </th>
              <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr
              v-for="user in users"
              :key="user.id"
              class="group transition-colors hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 font-semibold text-gray-500"
                  >
                    {{ user.first_name[0] }}{{ user.last_name[0] }}
                  </div>
                  <div class="ml-4">
                    <div class="font-medium text-gray-900">
                      {{ user.first_name }} {{ user.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                  :class="{
                    'bg-gray-100 text-gray-800': user.role === 'citizen',
                    'bg-blue-100 text-blue-800': user.role === 'staff',
                    'bg-purple-100 text-purple-800': user.role === 'admin',
                  }"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="
                    user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  "
                >
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ user.last_login ? formatDate(user.last_login) : 'Never' }}
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                <button @click="openRoleModal(user)" class="text-blue-600 hover:text-blue-900">
                  Edit Role
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div
          v-if="pagination.total_pages > 1"
          class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
        >
          <div class="flex flex-1 justify-between sm:hidden">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.total_pages"
              class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{
                  (pagination.current_page - 1) * pagination.limit + 1
                }}</span>
                to
                <span class="font-medium">{{
                  Math.min(pagination.current_page * pagination.limit, pagination.total_items)
                }}</span>
                of
                <span class="font-medium">{{ pagination.total_items }}</span>
                results
              </p>
            </div>
            <div>
              <nav
                class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                aria-label="Pagination"
              >
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                >
                  <span class="sr-only">Previous</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                      fill-rule="evenodd"
                      d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
                <button
                  v-for="page in pagination.total_pages"
                  :key="page"
                  @click="changePage(page)"
                  :class="[
                    page === pagination.current_page
                      ? 'relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                      : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.total_pages"
                  class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                >
                  <span class="sr-only">Next</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                      fill-rule="evenodd"
                      d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Role Edit Modal -->
    <div
      v-if="editingUser"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
      <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900">Edit User Role</h3>
        <p class="mt-2 text-sm text-gray-500">
          Change role for
          <span class="font-medium text-gray-900"
            >{{ editingUser.first_name }} {{ editingUser.last_name }}</span
          >.
        </p>

        <div class="mt-6 space-y-4">
          <div v-for="role in ['citizen', 'staff', 'admin']" :key="role" class="flex items-center">
            <input
              v-model="selectedRole"
              :id="role"
              name="role"
              type="radio"
              :value="role"
              class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-600"
            />
            <label :for="role" class="ml-3 block text-sm font-medium text-gray-700 capitalize">
              {{ role }}
            </label>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="closeRoleModal"
            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
          >
            Cancel
          </button>
          <button
            @click="saveRole"
            :disabled="saving"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : 'Save Role' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Simple debounce implementation
const debounce = (func, delay) => {
  let timeoutId
  return (...args) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func(...args), delay)
  }
}

const users = ref([])
const loading = ref(true)
const searchQuery = ref('')
const pagination = ref({
  current_page: 1,
  total_pages: 1,
  total_items: 0,
  limit: 20,
})

const editingUser = ref(null)
const selectedRole = ref('')
const saving = ref(false)

const fetchUsers = async (page = 1) => {
  loading.value = true
  try {
    const response = await axios.get('http://localhost/civic-connect/backend/api/admin/users', {
      params: {
        page,
        search: searchQuery.value,
      },
    })

    if (response.data.success) {
      users.value = response.data.users
      pagination.value = response.data.pagination
    }
  } catch (error) {
    console.error('Failed to fetch users:', error)
  } finally {
    loading.value = false
  }
}

const handleSearch = debounce(() => {
  fetchUsers(1)
}, 300)

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.total_pages) {
    fetchUsers(page)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const openRoleModal = (user) => {
  editingUser.value = user
  selectedRole.value = user.role
}

const closeRoleModal = () => {
  editingUser.value = null
  selectedRole.value = ''
}

const saveRole = async () => {
  if (!editingUser.value) return

  saving.value = true
  try {
    await axios.put(
      `http://localhost/civic-connect/backend/api/admin/users/${editingUser.value.id}/role`,
      {
        role: selectedRole.value,
      },
    )

    // Update local state
    const userIndex = users.value.findIndex((u) => u.id === editingUser.value.id)
    if (userIndex !== -1) {
      users.value[userIndex].role = selectedRole.value
    }

    closeRoleModal()
  } catch (error) {
    console.error('Failed to update role:', error)
    alert('Failed to update role. Please try again.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
