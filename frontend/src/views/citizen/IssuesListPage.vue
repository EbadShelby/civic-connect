<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-7xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">Community Issues</h1>
        <p class="text-[#819796]">Browse, upvote, and comment on issues in your community</p>
      </div>

      <!-- View Toggle and Filters -->
      <div class="mb-8 rounded-xl bg-white p-6 shadow-md">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <!-- View Toggle -->
          <div class="flex gap-2">
            <button
              @click="viewMode = 'list'"
              :class="{
                'bg-[#25562e] text-white': viewMode === 'list',
                'bg-gray-200 text-[#10141f]': viewMode !== 'list',
              }"
              class="flex items-center gap-2 rounded-lg px-4 py-2 font-semibold transition-colors"
            >
              <i class="fas fa-list"></i>
              List View
            </button>
            <button
              @click="viewMode = 'map'"
              :class="{
                'bg-[#25562e] text-white': viewMode === 'map',
                'bg-gray-200 text-[#10141f]': viewMode !== 'map',
              }"
              class="flex items-center gap-2 rounded-lg px-4 py-2 font-semibold transition-colors"
            >
              <i class="fas fa-map"></i>
              Map View
            </button>
          </div>

          <!-- Filters -->
          <div class="flex flex-col gap-4 sm:flex-row">
            <!-- Status Filter -->
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            >
              <option value="">All Status</option>
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>

            <!-- Category Filter -->
            <select
              v-model="filters.category"
              @change="applyFilters"
              class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            >
              <option value="">All Categories</option>
              <option value="pothole">Pothole</option>
              <option value="trash">Trash/Littering</option>
              <option value="streetlight">Street Light</option>
              <option value="graffiti">Graffiti</option>
              <option value="water_leak">Water Leak</option>
              <option value="tree_damage">Tree Damage</option>
              <option value="sidewalk">Sidewalk Damage</option>
              <option value="other">Other</option>
            </select>

            <!-- Sort Filter -->
            <select
              v-model="filters.sortBy"
              @change="applyFilters"
              class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            >
              <option value="recent">Most Recent</option>
              <option value="upvotes">Most Upvoted</option>
              <option value="comments">Most Discussed</option>
            </select>

            <!-- Reset Filters -->
            <button
              @click="resetAllFilters"
              class="rounded-lg bg-gray-200 px-4 py-2 font-semibold text-[#10141f] transition-colors hover:bg-gray-300"
            >
              <i class="fas fa-redo mr-2"></i>Reset
            </button>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="space-y-4">
        <!-- Loading State -->
        <div v-if="isLoading" class="py-12 text-center">
          <i class="fas fa-spinner fa-spin text-3xl text-[#75a743]"></i>
          <p class="mt-4 text-[#819796]">Loading issues...</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredIssuesStore.length === 0"
          class="rounded-xl bg-white p-12 text-center shadow-md"
        >
          <i class="fas fa-inbox mb-4 block text-4xl text-[#819796] opacity-50"></i>
          <p class="text-lg text-[#819796]">No issues found matching your filters.</p>
          <button
            @click="resetAllFilters"
            class="mt-4 font-semibold text-[#75a743] hover:underline"
          >
            Clear filters
          </button>
        </div>

        <!-- Issues List -->
        <div v-else class="space-y-4">
          <div
            v-for="issue in filteredIssuesStore"
            :key="issue.id"
            class="rounded-xl border-l-4 bg-white p-6 shadow-md transition-shadow hover:shadow-lg"
            :class="{
              'border-blue-500': issue.status === 'open',
              'border-yellow-500': issue.status === 'in_progress',
              'border-green-500': issue.status === 'resolved',
              'border-gray-500': issue.status === 'closed',
            }"
          >
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
              <!-- Issue Image -->
              <div
                v-if="issue.image_url"
                class="h-32 w-full flex-shrink-0 overflow-hidden rounded-lg lg:w-32"
              >
                <img :src="issue.image_url" :alt="issue.title" class="h-full w-full object-cover" />
              </div>
              <div
                v-else
                class="flex h-32 w-full flex-shrink-0 items-center justify-center rounded-lg bg-gray-200 lg:w-32"
              >
                <i class="fas fa-image text-3xl text-gray-400"></i>
              </div>

              <!-- Issue Details -->
              <div class="flex-grow">
                <div class="mb-2 flex items-start gap-3">
                  <div>
                    <router-link
                      :to="`/issues/${issue.id}`"
                      class="text-xl font-bold text-[#25562e] transition-colors hover:text-[#75a743]"
                    >
                      {{ issue.title }}
                    </router-link>
                  </div>
                  <!-- Status Badge -->
                  <div
                    :class="{
                      'bg-blue-100 text-blue-800': issue.status === 'open',
                      'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                      'bg-green-100 text-green-800': issue.status === 'resolved',
                      'bg-gray-100 text-gray-800': issue.status === 'closed',
                    }"
                    class="rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap uppercase"
                  >
                    {{ issue.status }}
                  </div>
                </div>

                <!-- Category and Meta -->
                <div class="mb-3 flex flex-wrap items-center gap-3 text-sm text-[#819796]">
                  <span>
                    <i class="fas fa-tag mr-1 text-[#75a743]"></i>
                    {{ formatCategory(issue.category) }}
                  </span>
                  <span>
                    <i class="fas fa-calendar mr-1 text-[#75a743]"></i>
                    {{ formatDate(issue.created_at) }}
                  </span>
                  <span
                    :class="{
                      'text-red-600': issue.priority === 'critical',
                      'text-orange-600': issue.priority === 'high',
                      'text-yellow-600': issue.priority === 'medium',
                      'text-blue-600': issue.priority === 'low',
                    }"
                  >
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ issue.priority?.charAt(0).toUpperCase() + issue.priority?.slice(1) }}
                  </span>
                </div>

                <!-- Description -->
                <p class="mb-3 line-clamp-2 text-[#10141f]">{{ issue.description }}</p>

                <!-- Reporter -->
                <p class="mb-3 text-xs text-[#819796]">
                  <i class="fas fa-user-circle mr-1"></i>
                  Reported by {{ issue.user_name || 'Anonymous' }}
                </p>
              </div>

              <!-- Stats and Actions -->
              <div class="flex min-w-fit flex-col gap-4">
                <!-- Stats -->
                <div class="flex flex-col gap-2">
                  <div class="text-center">
                    <button
                      @click="toggleUpvote(issue.id)"
                      :class="{
                        'bg-[#cf573c] text-white': issue.user_has_upvoted,
                        'bg-gray-200 text-[#10141f] hover:bg-[#cf573c] hover:text-white':
                          !issue.user_has_upvoted,
                      }"
                      class="flex w-full items-center justify-center gap-2 rounded-lg px-3 py-2 font-semibold transition-colors"
                    >
                      <i class="fas fa-arrow-up"></i>
                      {{ issue.upvote_count || 0 }}
                    </button>
                  </div>
                  <div class="text-center">
                    <router-link
                      :to="`/issues/${issue.id}`"
                      class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-200 px-3 py-2 font-semibold text-[#10141f] transition-colors hover:bg-[#75a743] hover:text-white"
                    >
                      <i class="fas fa-comment"></i>
                      {{ issue.comment_count || 0 }}
                    </router-link>
                  </div>
                </div>

                <!-- View Details Link -->
                <router-link
                  :to="`/issues/${issue.id}`"
                  class="w-full rounded-lg bg-[#25562e] px-4 py-2 text-center font-semibold text-white transition-colors hover:bg-[#1a3d21]"
                >
                  View Details
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredIssuesStore.length > 0" class="mt-8 flex items-center justify-between">
          <p class="text-[#819796]">
            Showing <strong>{{ filteredIssuesStore.length }}</strong> of
            <strong>{{ totalIssuesCount }}</strong> issues
          </p>
        </div>
      </div>

      <!-- Map View -->
      <div v-if="viewMode === 'map'" class="overflow-hidden rounded-xl bg-white shadow-md">
        <div v-if="isLoading" class="flex h-96 items-center justify-center">
          <i class="fas fa-spinner fa-spin text-3xl text-[#75a743]"></i>
        </div>
        <div id="map-container" v-else class="h-96 md:h-[600px]"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import L from 'leaflet'
import { useIssuesStore } from '../../stores/issuesStore'

const issuesStore = useIssuesStore()

const viewMode = ref('list')
const isLoading = ref(false)
const totalIssuesCount = ref(0)
let mapInstance = null
let markers = {}

const filters = ref({
  status: '',
  category: '',
  sortBy: 'recent',
})

const filteredIssuesStore = computed(() => issuesStore.filteredIssues)

const formatCategory = (category) => {
  const categoryMap = {
    pothole: 'Pothole',
    trash: 'Trash/Littering',
    streetlight: 'Street Light',
    graffiti: 'Graffiti',
    water_leak: 'Water Leak',
    tree_damage: 'Tree Damage',
    sidewalk: 'Sidewalk Damage',
    other: 'Other',
  }
  return categoryMap[category] || category
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  if (date.toDateString() === today.toDateString()) {
    return 'Today'
  } else if (date.toDateString() === yesterday.toDateString()) {
    return 'Yesterday'
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  }
}

const applyFilters = () => {
  issuesStore.setFilters({
    status: filters.value.status || null,
    category: filters.value.category || null,
    sortBy: filters.value.sortBy,
  })
}

const resetAllFilters = () => {
  filters.value = {
    status: '',
    category: '',
    sortBy: 'recent',
  }
  issuesStore.resetFilters()
}

const toggleUpvote = async (issueId) => {
  try {
    const issue = issuesStore.issues.find((i) => i.id === issueId)
    if (issue?.user_has_upvoted) {
      await issuesStore.removeUpvote(issueId)
    } else {
      await issuesStore.upvoteIssue(issueId)
    }
  } catch (error) {
    console.error('Error toggling upvote:', error)
  }
}

const initMap = () => {
  if (mapInstance) {
    mapInstance.remove()
  }

  // Cotabato City coordinates and bounds
  const cotabatoCenterLat = 7.2167
  const cotabatoCenterLng = 124.25
  const cotabatoBounds = [
    [7.0967, 124.12], // Southwest
    [7.3367, 124.38], // Northeast
  ]

  // Initialize map centered on Cotabato City
  mapInstance = L.map('map-container').setView([cotabatoCenterLat, cotabatoCenterLng], 13)

  // Set max bounds to Cotabato City with some buffer
  mapInstance.setMaxBounds([
    [7.0767, 124.1],
    [7.3567, 124.4],
  ])

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    minZoom: 11,
  }).addTo(mapInstance)

  // Add a visual bounds rectangle for Cotabato City
  L.rectangle(cotabatoBounds, {
    color: '#25562e',
    weight: 2,
    opacity: 0.3,
    fillColor: '#75a743',
    fillOpacity: 0.05,
  }).addTo(mapInstance)

  // Add markers for all issues
  issuesStore.filteredIssues.forEach((issue) => {
    if (issue.latitude && issue.longitude) {
      const marker = L.marker([issue.latitude, issue.longitude]).addTo(mapInstance).bindPopup(`
          <div class="max-w-xs">
            <h3 class="font-bold">${issue.title}</h3>
            <p class="text-sm text-gray-600">${issue.category}</p>
            <p class="text-sm mt-2">${issue.description.substring(0, 100)}...</p>
            <a href="#/issues/${issue.id}" class="text-blue-600 text-sm font-semibold mt-2 inline-block">View Details →</a>
          </div>
        `)

      // Add CSS class based on status for styling
      const statusClass = `status-${issue.status}`
      marker.getElement()?.classList.add(statusClass)

      markers[issue.id] = marker
    }
  })

  // Fit bounds to show all markers if there are any
  if (Object.keys(markers).length > 0) {
    const group = L.featureGroup(Object.values(markers))
    mapInstance.fitBounds(group.getBounds().pad(0.1))
  }
}

const fetchIssues = async () => {
  isLoading.value = true
  try {
    const result = await issuesStore.fetchIssues()
    totalIssuesCount.value = result.length
    applyFilters()
  } catch (error) {
    console.error('Error fetching issues:', error)
  } finally {
    isLoading.value = false
  }
}

watch(
  () => viewMode.value,
  (newMode) => {
    if (newMode === 'map') {
      setTimeout(() => {
        initMap()
      }, 100)
    }
  },
)

onMounted(() => {
  fetchIssues()
})
</script>
