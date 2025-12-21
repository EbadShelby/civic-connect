<template>
  <div class="min-h-screen bg-linear-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-7xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="mb-2 text-4xl font-bold text-[#10141f]">Community Issues</h1>
          <p class="text-[#819796]">Browse and upvote issues in your community</p>
        </div>
        <router-link
          to="/report-issue"
          class="flex items-center gap-2 rounded-lg bg-[#cf573c] px-6 py-3 font-semibold text-white shadow-md transition-all hover:bg-[#b84a31] hover:shadow-lg"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="h-5 w-5"
          >
            <path
              fill-rule="evenodd"
              d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z"
              clip-rule="evenodd"
            />
          </svg>
          Report Issue
        </router-link>
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
              <ListBulletIcon class="h-5 w-5" />
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
              <MapIcon class="h-5 w-5" />
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
              <option value="pending_review">Pending Review</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
            </select>

            <!-- Category Filter -->
            <select
              v-model="filters.category"
              @change="applyFilters"
              class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
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

            <!-- Sort Filter -->
            <select
              v-model="filters.sortBy"
              @change="applyFilters"
              class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            >
              <option value="recent">Most Recent</option>
              <option value="upvotes">Most Upvoted</option>
            </select>

            <!-- Reset Filters -->
            <button
              @click="resetAllFilters"
              class="flex items-center rounded-lg bg-gray-200 px-4 py-2 font-semibold text-[#10141f] transition-colors hover:bg-gray-300"
            >
              <ArrowPathIcon class="mr-2 h-5 w-5" />
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="space-y-4">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center py-12 text-center">
          <ArrowPathIcon class="h-8 w-8 animate-spin text-[#75a743]" />
          <p class="mt-4 text-[#819796]">Loading issues...</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredIssuesStore.length === 0"
          class="flex flex-col items-center rounded-xl bg-white p-12 text-center shadow-md"
        >
          <InboxIcon class="mb-4 block h-12 w-12 text-[#819796] opacity-50" />
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
              'border-blue-500': issue.status === 'pending_review',
              'border-yellow-500': issue.status === 'in_progress',
              'border-green-500': issue.status === 'resolved',
            }"
          >
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
              <!-- Issue Image -->
              <div
                v-if="issue.image_url"
                class="h-32 w-full shrink-0 cursor-pointer overflow-hidden rounded-lg transition-opacity hover:opacity-90 lg:w-32"
                @click="selectedImage = issue.image_url"
              >
                <img :src="issue.image_url" :alt="issue.title" class="h-full w-full object-cover" />
              </div>
              <div
                v-else
                class="flex h-32 w-full shrink-0 items-center justify-center rounded-lg bg-gray-200 lg:w-32"
              >
                <PhotoIcon class="h-8 w-8 text-gray-400" />
              </div>

              <!-- Issue Details -->
              <div class="grow">
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
                      'bg-blue-100 text-blue-800': issue.status === 'pending_review',
                      'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                      'bg-green-100 text-green-800': issue.status === 'resolved',
                    }"
                    class="rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap uppercase"
                  >
                    {{ issue.status }}
                  </div>
                </div>

                <!-- Category and Meta -->
                <div class="mb-3 flex flex-wrap items-center gap-3 text-sm text-[#819796]">
                  <span class="flex items-center">
                    <TagIcon class="mr-1 h-4 w-4 text-[#75a743]" />
                    {{ formatCategory(issue.category) }}
                  </span>
                  <span class="flex items-center">
                    <CalendarIcon class="mr-1 h-4 w-4 text-[#75a743]" />
                    {{ formatDate(issue.created_at) }}
                  </span>
                  <span v-if="issue.location" class="flex max-w-xs items-center truncate">
                    <MapPinIcon class="mr-1 h-4 w-4 shrink-0 text-[#75a743]" />
                    <span class="truncate">{{ issue.location }}</span>
                  </span>
                  <span
                    class="flex items-center"
                    :class="{
                      'text-red-600': issue.priority === 'critical',
                      'text-orange-600': issue.priority === 'high',
                      'text-yellow-600': issue.priority === 'medium',
                      'text-blue-600': issue.priority === 'low',
                    }"
                  >
                    <ExclamationCircleIcon class="mr-1 h-4 w-4" />
                    {{ issue.priority?.charAt(0).toUpperCase() + issue.priority?.slice(1) }}
                  </span>
                </div>

                <!-- Description -->
                <p class="mb-3 line-clamp-2 text-[#10141f]">{{ issue.description }}</p>

                <!-- Reporter -->
                <p class="mb-3 flex items-center text-xs text-[#819796]">
                  <UserCircleIcon class="mr-1 h-4 w-4" />
                  Reported by
                  <router-link
                    :to="`/profile/${issue.user_id}`"
                    class="ml-1 font-semibold text-[#25562e] transition-colors hover:text-[#75a743] hover:underline"
                  >
                    {{ issue.user_name }}
                  </router-link>
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
                      <HandThumbUpIcon class="h-5 w-5" />
                      Vote ({{ issue.upvote_count || 0 }})
                    </button>
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
          <ArrowPathIcon class="h-8 w-8 animate-spin text-[#75a743]" />
        </div>
        <div id="map-container" v-else class="h-96 md:h-[600px]"></div>
      </div>
    </div>

    <!-- Image Modal -->
    <ImageModal :image-url="selectedImage" :alt="'Issue Image'" @close="selectedImage = null" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import L from 'leaflet'
import { useIssuesStore } from '../../stores/issuesStore'
import ImageModal from '../../components/common/ImageModal.vue'
import {
  ListBulletIcon,
  MapIcon,
  ArrowPathIcon,
  InboxIcon,
  PhotoIcon,
  TagIcon,
  CalendarIcon,
  ExclamationCircleIcon,
  UserCircleIcon,
  HandThumbUpIcon,
  MapPinIcon,
} from '@heroicons/vue/24/solid'

const issuesStore = useIssuesStore()

const viewMode = ref('list')
const isLoading = ref(false)
const selectedImage = ref(null)
let mapInstance = null
let markers = {}

const filters = ref({
  status: '',
  category: '',
  sortBy: 'recent',
})

const filteredIssuesStore = computed(() => issuesStore.filteredIssues)
const totalIssuesCount = computed(() => issuesStore.totalCount)

const formatCategory = (category) => {
  const categoryMap = {
    roads: 'Roads',
    street_lights: 'Street Lights',
    trash: 'Trash',
    water_drainage: 'Water & Drainage',
    parks_recreation: 'Parks & Recreation',
    public_safety: 'Public Safety',
    graffiti_vandalism: 'Graffiti & Vandalism',
    noise: 'Noise',
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

  // Handle popup clicks via delegation
  mapInstance.getContainer().addEventListener('click', (e) => {
    if (e.target.classList.contains('view-details-link')) {
      e.preventDefault()
      const issueId = e.target.getAttribute('data-issue-id')
      if (issueId) {
        import('../../router').then(({ default: router }) => {
          router.push(`/issues/${issueId}`)
        })
      }
    }
  })

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
      // SVG for map popup thumbs up
      const thumbsUpSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-1 inline-block">
        <path d="M7.493 18.75c-.425 0-.82-.236-.975-.632A7.48 7.48 0 016 15.375c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.233 18.748c.036.003.072.003.108.003h3.126c.909 0 1.764-.325 2.43-.873a10.45 10.45 0 002.606-6.666c0-.525-.03-1.044-.09-1.554a3.75 3.75 0 00-3.665-3.348h-3.126a2.25 2.25 0 00-1.418.508c-.27.233-.673.34-1.077.34-.378 0-.743-.092-1.077-.282a3.75 3.75 0 00-1.748-.43h-2.25C6.983 7.545 6 9.006 6 10.875v5.85c0 1.056.405 2.06 1.127 2.766.721.705 1.707 1.059 2.723 1.059h5.383z" />
      </svg>`

      const marker = L.marker([issue.latitude, issue.longitude]).addTo(mapInstance).bindPopup(`
          <div class="max-w-xs">
            <h3 class="font-bold">${issue.title}</h3>
            <p class="text-sm text-gray-600">${issue.category}</p>
            <p class="text-sm font-semibold text-[#cf573c] mt-1 flex items-center">
              ${thumbsUpSvg}
              ${issue.upvote_count || 0} Votes
            </p>
            <p class="text-sm mt-2">${issue.description.substring(0, 100)}...</p>
            <a href="javascript:void(0)" class="text-blue-600 text-sm font-semibold mt-2 inline-block view-details-link" data-issue-id="${issue.id}">View Details →</a>
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
    // Fetch all issues by requesting a large limit
    await issuesStore.fetchIssues({ limit: 1000 })
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
