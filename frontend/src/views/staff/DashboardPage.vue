<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">
              Welcome back, {{ authStore.user?.first_name || 'Staff' }}. Here's what's happening
              today.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">Last updated: {{ lastUpdated }}</span>
            <button
              @click="refreshData"
              class="flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
              :disabled="issuesStore.isLoading"
            >
              <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': issuesStore.isLoading }" />
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Stats Grid -->
      <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Issues -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
              <ClipboardDocumentListIcon class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Total Issues</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <!-- Open Issues -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-50">
              <ExclamationCircleIcon class="h-6 w-6 text-yellow-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Pending Review</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.open }}</p>
            </div>
          </div>
        </div>

        <!-- In Progress -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50">
              <ClockIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">In Progress</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.inProgress }}</p>
            </div>
          </div>
        </div>

        <!-- Resolved -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
              <CheckCircleIcon class="h-6 w-6 text-green-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Resolved</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.resolved }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-2">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search issues..."
              class="w-full rounded-lg border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-blue-500 sm:w-64"
            />
          </div>
          <select
            v-model="selectedCategory"
            class="rounded-lg border-gray-300 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Categories</option>
            <option
              v-for="category in issuesStore.issueCategories"
              :key="category"
              :value="category"
            >
              {{ formatCategory(category) }}
            </option>
          </select>
        </div>

        <div class="flex items-center gap-2 rounded-lg bg-white p-1 shadow-sm ring-1 ring-gray-200">
          <button
            v-for="filter in statusFilters"
            :key="filter.value"
            @click="activeStatusFilter = filter.value"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
            :class="[
              activeStatusFilter === filter.value
                ? 'bg-blue-50 text-blue-700 shadow-sm'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
            ]"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>

      <!-- Issues Table -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
        <div
          v-if="issuesStore.isLoading && !issuesStore.issues.length"
          class="flex h-64 items-center justify-center"
        >
          <div
            class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"
          ></div>
        </div>

        <div
          v-else-if="filteredIssues.length === 0"
          class="flex h-64 flex-col items-center justify-center py-12 text-center"
        >
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
            <InboxIcon class="h-6 w-6 text-gray-400" />
          </div>
          <h3 class="mt-2 text-sm font-semibold text-gray-900">No issues found</h3>
          <p class="mt-1 text-sm text-gray-500">No issues match your current filters.</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Issue
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Category
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
                Date
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
              >
                Votes
              </th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr
              v-for="issue in filteredIssues"
              :key="issue.id"
              class="group transition-colors hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 shrink-0">
                    <img
                      v-if="issue.image_url"
                      :src="issue.image_url"
                      class="h-10 w-10 rounded-lg object-cover"
                      alt=""
                    />
                    <div
                      v-else
                      class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-400"
                    >
                      <PhotoIcon class="h-6 w-6" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="font-medium text-gray-900">{{ issue.title }}</div>
                    <div class="line-clamp-1 max-w-[200px] text-sm text-gray-500">
                      {{ issue.description }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800"
                >
                  {{ formatCategory(issue.category) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    getStatusColor(issue.status),
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                  ]"
                >
                  {{ formatStatus(issue.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ formatDate(issue.created_at) }}
              </td>
              <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                <div class="flex items-center gap-1">
                  <HandThumbUpIcon class="h-4 w-4 text-gray-400" />
                  {{ issue.upvote_count }}
                </div>
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                <router-link
                  :to="`/staff/issues/${issue.id}`"
                  class="text-blue-600 hover:text-blue-900"
                  >Manage</router-link
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useIssuesStore } from '../../stores/issuesStore'
import { useAuthStore } from '../../stores/authStore'
import {
  ClipboardDocumentListIcon,
  ExclamationCircleIcon,
  ClockIcon,
  CheckCircleIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  PhotoIcon,
  HandThumbUpIcon,
  InboxIcon,
} from '@heroicons/vue/24/outline'

const issuesStore = useIssuesStore()
const authStore = useAuthStore()

const searchQuery = ref('')
const selectedCategory = ref('')
const activeStatusFilter = ref('all')
const lastUpdated = ref(new Date().toLocaleTimeString())

const statusFilters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'open' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Resolved', value: 'resolved' },
]

const stats = computed(() => {
  const issues = issuesStore.issues
  return {
    total: issues.length,
    open: issues.filter((i) => i.status === 'open').length,
    inProgress: issues.filter((i) => i.status === 'in_progress').length,
    resolved: issues.filter((i) => i.status === 'resolved' || i.status === 'closed').length,
  }
})

const filteredIssues = computed(() => {
  let result = [...issuesStore.issues]

  // Status Filter
  if (activeStatusFilter.value !== 'all') {
    result = result.filter((i) => i.status === activeStatusFilter.value)
  }

  // Category Filter
  if (selectedCategory.value) {
    result = result.filter((i) => i.category === selectedCategory.value)
  }

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      (i) => i.title.toLowerCase().includes(query) || i.description.toLowerCase().includes(query),
    )
  }

  // Sort by date (newest first)
  return result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const formatCategory = (category) => {
  return category
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const formatStatus = (status) => {
  switch (status) {
    case 'open':
      return 'Pending Review'
    case 'in_progress':
      return 'In Progress'
    case 'resolved':
      return 'Resolved'
    case 'closed':
      return 'Closed'
    default:
      return status
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'open':
      return 'bg-yellow-100 text-yellow-800'
    case 'in_progress':
      return 'bg-blue-100 text-blue-800'
    case 'resolved':
      return 'bg-green-100 text-green-800'
    case 'closed':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const refreshData = async () => {
  await issuesStore.fetchIssues()
  lastUpdated.value = new Date().toLocaleTimeString()
}

onMounted(() => {
  refreshData()
})
</script>
