<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <router-link
              to="/staff/dashboard"
              class="flex items-center text-gray-500 hover:text-gray-700"
            >
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900">Manage Issue #{{ issue?.id }}</h1>
          </div>
          <div class="flex items-center gap-3">
            <span
              v-if="issue"
              :class="[
                getStatusColor(issue.status),
                'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium',
              ]"
            >
              {{ formatStatus(issue.status) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex h-64 items-center justify-center">
      <div
        class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"
      ></div>
    </div>

    <div v-else-if="error" class="mx-auto mt-8 max-w-3xl px-4">
      <div class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="shrink-0">
            <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error loading issue</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error }}</p>
            </div>
            <div class="mt-4">
              <router-link
                to="/staff/dashboard"
                class="text-sm font-medium text-red-800 hover:text-red-900"
                >Back to Dashboard</router-link
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-else-if="issue"
      class="mx-auto mt-8 grid max-w-7xl grid-cols-1 gap-6 px-4 lg:grid-cols-3"
    >
      <!-- Main Column -->
      <div class="space-y-6 lg:col-span-2">
        <!-- Issue Info -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <span
                class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800"
              >
                {{ formatCategory(issue.category) }}
              </span>
              <span class="text-sm text-gray-500">{{ formatDate(issue.created_at) }}</span>
            </div>
            <h2 class="mt-4 text-xl font-bold text-gray-900">{{ issue.title }}</h2>
            <p class="mt-4 whitespace-pre-wrap text-gray-700">{{ issue.description }}</p>

            <div v-if="issue.image_url" class="mt-6">
              <img
                :src="issue.image_url"
                alt="Issue Image"
                class="h-96 w-full rounded-lg object-cover"
              />
            </div>
          </div>
        </div>

        <!-- Location (Leaflet Map) -->
        <div
          v-if="issue.latitude && issue.longitude"
          class="overflow-hidden rounded-lg bg-white shadow"
        >
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Location</h3>
            <div v-if="issue.location" class="mt-4 mb-4">
              <p class="flex items-start text-gray-700">
                <MapPinIcon class="mt-0.5 mr-2 h-5 w-5 shrink-0 text-blue-600" />
                <span class="font-semibold">{{ issue.location }}</span>
              </p>
              <p class="mt-1 ml-7 text-sm text-gray-500">
                Coordinates: {{ issue.latitude.toFixed(6) }}, {{ issue.longitude.toFixed(6) }}
              </p>
            </div>
            <div id="staff-issue-map" class="mt-4 h-64 rounded-lg bg-gray-100"></div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status Management -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Update Status</h3>
            <div class="mt-4 space-y-3">
              <p class="mb-2 text-sm text-gray-500">
                Current status: <span class="font-medium">{{ formatStatus(issue.status) }}</span>
              </p>

              <div class="grid grid-cols-1 gap-2">
                <button
                  v-for="status in availableStatuses"
                  :key="status.value"
                  @click="updateStatus(status.value)"
                  :disabled="updatingStatus !== null || issue.status === status.value"
                  class="flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium transition-colors"
                  :class="[
                    issue.status === status.value
                      ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
                      : updatingStatus !== null
                        ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                  ]"
                >
                  <div
                    v-if="updatingStatus === status.value"
                    class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"
                  ></div>
                  <component
                    v-else
                    :is="status.icon"
                    class="mr-2 h-4 w-4"
                    :class="status.colorClass"
                  />
                  Mark as {{ status.label }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Issue Details Sidebar -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">Details</h3>
            <dl class="mt-4 space-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                <dd class="mt-1 flex items-center text-sm text-gray-900 capitalize">
                  <ExclamationCircleIcon class="mr-1.5 h-4 w-4 text-gray-400" />
                  {{ issue.priority }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Upvotes</dt>
                <dd class="mt-1 flex items-center text-sm text-gray-900">
                  <HandThumbUpIcon class="mr-1.5 h-4 w-4 text-gray-400" />
                  {{ issue.upvote_count }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Reporter</dt>
                <dd class="mt-1 text-sm">
                  <router-link
                    :to="`/profile/${issue.user_id}`"
                    class="font-medium text-blue-600 transition-colors hover:text-blue-800 hover:underline"
                  >
                    {{ issue.user_name }}
                  </router-link>
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIssuesStore } from '../../stores/issuesStore'
import { useToast } from 'vue-toastification'
import L from 'leaflet'
import {
  ArrowLeftIcon,
  ExclamationCircleIcon,
  HandThumbUpIcon,
  CheckCircleIcon,
  ClockIcon,
  InboxIcon,
  MapPinIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const issuesStore = useIssuesStore()
const toast = useToast()

const issue = ref(null)
const isLoading = ref(false)
const updatingStatus = ref(null)
const error = ref('')

const availableStatuses = [
  {
    value: 'pending_review',
    label: 'Pending Review',
    icon: InboxIcon,
    colorClass: 'text-yellow-500',
  },
  { value: 'in_progress', label: 'In Progress', icon: ClockIcon, colorClass: 'text-blue-500' },
  { value: 'resolved', label: 'Resolved', icon: CheckCircleIcon, colorClass: 'text-green-500' },
]

const formatCategory = (category) => {
  return category
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const formatStatus = (status) => {
  switch (status) {
    case 'pending_review':
      return 'Pending Review'
    case 'in_progress':
      return 'In Progress'
    case 'resolved':
      return 'Resolved'
    default:
      return status
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'pending_review':
      return 'bg-yellow-100 text-yellow-800'
    case 'in_progress':
      return 'bg-blue-100 text-blue-800'
    case 'resolved':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const updateStatus = async (newStatus) => {
  if (!issue.value) return

  updatingStatus.value = newStatus
  try {
    await issuesStore.updateIssue(issue.value.id, { status: newStatus })
    issue.value.status = newStatus
    toast.success(`Issue marked as ${formatStatus(newStatus)}`)
  } catch (err) {
    toast.error('Failed to update status')
    console.error(err)
  } finally {
    updatingStatus.value = null
  }
}

const initMap = () => {
  if (issue.value?.latitude && issue.value?.longitude) {
    const mapContainer = document.getElementById('staff-issue-map')
    if (mapContainer && !mapContainer.hasChildNodes()) {
      const map = L.map('staff-issue-map').setView(
        [issue.value.latitude, issue.value.longitude],
        15,
      )

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
      }).addTo(map)

      L.marker([issue.value.latitude, issue.value.longitude])
        .addTo(map)
        .bindPopup(issue.value.title)
        .openPopup()
    }
  }
}

const fetchIssue = async () => {
  isLoading.value = true
  error.value = ''
  try {
    issue.value = await issuesStore.fetchIssueById(route.params.id)
    setTimeout(initMap, 100)
  } catch (err) {
    error.value = 'Failed to load issue details.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchIssue()
})
</script>
