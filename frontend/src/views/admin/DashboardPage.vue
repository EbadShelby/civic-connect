<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">Overview of system status and activity</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="border-primary h-12 w-12 animate-spin rounded-full border-b-2"></div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700"
      >
        {{ error }}
      </div>

      <div v-else class="space-y-8">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
          <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="p-5">
              <div class="flex items-center">
                <div class="shrink-0 rounded-md bg-blue-100 p-3">
                  <svg
                    class="h-6 w-6 text-blue-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="truncate text-sm font-medium text-gray-500">Total Users</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ stats.users_total }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link to="/admin/users" class="font-medium text-blue-600 hover:text-blue-500"
                  >View all users</router-link
                >
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="p-5">
              <div class="flex items-center">
                <div class="shrink-0 rounded-md bg-orange-100 p-3">
                  <svg
                    class="h-6 w-6 text-orange-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="truncate text-sm font-medium text-gray-500">Total Issues</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ stats.issues_total }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link
                  to="/admin/issues"
                  class="font-medium text-blue-600 hover:text-blue-500"
                  >View all issues</router-link
                >
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="p-5">
              <div class="flex items-center">
                <div class="shrink-0 rounded-md bg-green-100 p-3">
                  <svg
                    class="h-6 w-6 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="truncate text-sm font-medium text-gray-500">Closed Issues</dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.issues_by_status?.closed || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <span class="text-gray-500">Resolved & Closed</span>
              </div>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="p-5">
              <div class="flex items-center">
                <div class="shrink-0 rounded-md bg-purple-100 p-3">
                  <svg
                    class="h-6 w-6 text-purple-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="truncate text-sm font-medium text-gray-500">Recent Activity</dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.recent_activity_count }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link
                  to="/admin/audit-logs"
                  class="font-medium text-blue-600 hover:text-blue-500"
                  >View audit logs</router-link
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Charts / Detailed Breakdown -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <!-- Issues by Status -->
          <div class="rounded-xl bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Issues by Status</h3>
            <div class="space-y-4">
              <div
                v-for="(count, status) in stats.issues_by_status"
                :key="status"
                class="flex items-center"
              >
                <span class="w-24 text-sm font-medium text-gray-600 capitalize">{{
                  status.replace('_', ' ')
                }}</span>
                <div class="mr-4 ml-4 flex-1">
                  <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full"
                      :class="{
                        'bg-red-500': status === 'open',
                        'bg-yellow-500': status === 'in_progress',
                        'bg-green-500': status === 'resolved',
                        'bg-gray-500': status === 'closed',
                      }"
                      :style="{ width: `${(count / stats.issues_total) * 100}%` }"
                    ></div>
                  </div>
                </div>
                <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
              </div>
              <div v-if="!stats.issues_total" class="text-center text-gray-500">
                No issues found
              </div>
            </div>
          </div>

          <!-- Users by Role -->
          <div class="rounded-xl bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Users by Role</h3>
            <div class="space-y-4">
              <div
                v-for="(count, role) in stats.users_by_role"
                :key="role"
                class="flex items-center"
              >
                <span class="w-24 text-sm font-medium text-gray-600 capitalize">{{ role }}</span>
                <div class="mr-4 ml-4 flex-1">
                  <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full bg-blue-500"
                      :style="{ width: `${(count / stats.users_total) * 100}%` }"
                    ></div>
                  </div>
                </div>
                <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const stats = ref({
  users_total: 0,
  issues_total: 0,
  users_by_role: {},
  issues_by_status: {},
  recent_activity_count: 0,
})

const loading = ref(true)
const error = ref(null)

const fetchStats = async () => {
  loading.value = true
  try {
    const response = await axios.get('http://localhost/civic-connect/backend/api/admin/stats')
    if (response.data.success) {
      stats.value = response.data.stats
    }
  } catch (err) {
    error.value = 'Failed to load dashboard statistics'
    console.error('Error fetching stats:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>
