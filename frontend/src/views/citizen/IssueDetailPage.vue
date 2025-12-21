<template>
  <div class="min-h-screen bg-linear-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-4xl px-4 py-8">
      <!-- Back Button -->
      <router-link
        to="/issues"
        class="mb-6 inline-flex items-center gap-2 font-semibold text-[#75a743] hover:underline"
      >
        <ArrowLeftIcon class="h-5 w-5" />
        Back to Issues
      </router-link>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="flex flex-col items-center rounded-xl bg-white p-12 text-center shadow-md"
      >
        <ArrowPathIcon class="h-8 w-8 animate-spin text-[#75a743]" />
        <p class="mt-4 text-[#819796]">Loading issue details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-8 shadow-md">
        <p class="flex items-center text-red-800">
          <ExclamationCircleIcon class="mr-2 inline h-5 w-5" />
          {{ error }}
        </p>
        <router-link
          to="/issues"
          class="mt-4 inline-block font-semibold text-[#75a743] hover:underline"
        >
          Return to issues list
        </router-link>
      </div>

      <!-- Issue Details -->
      <div v-else-if="issue">
        <!-- Header Card -->
        <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-md">
          <!-- Issue Image -->
          <div
            v-if="issue.image_url"
            class="h-96 w-full cursor-pointer overflow-hidden"
            @click="selectedImage = issue.image_url"
          >
            <img
              :src="issue.image_url"
              :alt="issue.title"
              class="h-full w-full object-cover transition-opacity hover:opacity-95"
            />
          </div>

          <!-- Issue Info -->
          <div class="p-8">
            <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
              <div class="grow">
                <h1 class="mb-2 text-3xl font-bold text-[#10141f]">{{ issue.title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                  <!-- Status Badge -->
                  <div
                    :class="{
                      'bg-blue-100 text-blue-800': issue.status === 'pending_review',
                      'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                      'bg-green-100 text-green-800': issue.status === 'resolved',
                    }"
                    class="rounded-full px-4 py-1 text-sm font-semibold uppercase"
                  >
                    {{ issue.status }}
                  </div>

                  <!-- Category -->
                  <span class="flex items-center gap-2 text-[#819796]">
                    <TagIcon class="h-4 w-4 text-[#75a743]" />
                    {{ formatCategory(issue.category) }}
                  </span>

                  <!-- Priority -->
                  <span
                    :class="{
                      'text-orange-600': issue.priority === 'high',
                      'text-yellow-600': issue.priority === 'medium',
                      'text-blue-600': issue.priority === 'low',
                    }"
                    class="flex items-center font-semibold"
                  >
                    <ExclamationCircleIcon class="mr-1 h-4 w-4" />
                    {{ issue.priority?.charAt(0).toUpperCase() + issue.priority?.slice(1) }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex min-w-fit flex-col gap-2">
                <button
                  @click="toggleUpvote"
                  :class="{
                    'bg-[#cf573c] text-white': issue.user_has_upvoted,
                    'bg-gray-200 text-[#10141f]': !issue.user_has_upvoted,
                  }"
                  class="flex items-center justify-center gap-2 rounded-lg px-6 py-2 font-semibold transition-colors"
                >
                  <HandThumbUpIcon class="h-5 w-5" />
                  Vote ({{ issue.upvote_count || 0 }})
                </button>
                <button
                  v-if="canDeleteIssue"
                  @click="deleteIssue"
                  :disabled="isDeleting"
                  class="flex items-center justify-center rounded-lg bg-red-100 px-6 py-2 font-semibold text-red-800 transition-colors hover:bg-red-200 disabled:opacity-50"
                >
                  <ArrowPathIcon v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                  {{ isDeleting ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </div>

            <!-- Meta Information -->
            <div class="mt-6 grid grid-cols-1 gap-4 border-t border-gray-200 pt-6 md:grid-cols-3">
              <div>
                <p class="text-sm text-[#819796]">Reported by</p>
                <router-link
                  :to="`/profile/${issue.user_id}`"
                  class="font-semibold text-[#25562e] transition-colors hover:text-[#75a743] hover:underline"
                >
                  {{ issue.user_name }}
                </router-link>
              </div>
              <div>
                <p class="text-sm text-[#819796]">Date</p>
                <p class="font-semibold text-[#10141f]">{{ formatDate(issue.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-[#819796]">Upvotes</p>
                <p class="font-semibold text-[#10141f]">{{ issue.upvote_count || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="mb-6 rounded-xl bg-white p-8 shadow-md">
          <h2 class="mb-4 text-2xl font-bold text-[#10141f]">Description</h2>
          <p class="leading-relaxed whitespace-pre-wrap text-[#10141f]">{{ issue.description }}</p>
        </div>

        <!-- Location -->
        <div
          v-if="issue.latitude && issue.longitude"
          class="mb-6 rounded-xl bg-white p-8 shadow-md"
        >
          <h2 class="mb-4 text-2xl font-bold text-[#10141f]">Location</h2>
          <p v-if="issue.location" class="mb-2 flex items-start text-[#10141f]">
            <MapPinIcon class="mt-0.5 mr-2 inline h-5 w-5 flex-shrink-0 text-[#75a743]" />
            <span class="font-semibold">{{ issue.location }}</span>
          </p>
          <p class="mb-4 flex items-center text-sm text-[#819796]">
            <span class="ml-7"
              >Coordinates: {{ issue.latitude.toFixed(6) }}, {{ issue.longitude.toFixed(6) }}</span
            >
          </p>
          <div id="issue-map" class="h-64 rounded-lg border border-gray-300"></div>
        </div>
      </div>
    </div>
  </div>
  <ImageModal :image-url="selectedImage" :alt="issue?.title" @close="selectedImage = null" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useIssuesStore } from '../../stores/issuesStore'
import ImageModal from '../../components/common/ImageModal.vue'
import L from 'leaflet'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  ExclamationCircleIcon,
  TagIcon,
  HandThumbUpIcon,
  MapPinIcon,
} from '@heroicons/vue/24/solid'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const issuesStore = useIssuesStore()

const issue = ref(null)
const isLoading = ref(false)
const error = ref('')
const isDeleting = ref(false)
const selectedImage = ref(null)

const canDeleteIssue = computed(() => {
  return authStore.user?.id === issue.value?.user_id
})

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
    return 'Today at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } else if (date.toDateString() === yesterday.toDateString()) {
    return (
      'Yesterday at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
    )
  } else {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined,
    })
  }
}

const toggleUpvote = async () => {
  try {
    let result
    if (issue.value?.user_has_upvoted) {
      result = await issuesStore.removeUpvote(route.params.id)
    } else {
      result = await issuesStore.upvoteIssue(route.params.id)
    }

    // Update local issue state reactively with the returned data
    if (result && issue.value) {
      issue.value.upvote_count = result.upvoteCount
      issue.value.user_has_upvoted = !issue.value.user_has_upvoted
    }
  } catch {
    error.value = 'Failed to upvote issue'
  }
}

const deleteIssue = async () => {
  if (!confirm('Are you sure you want to delete this issue? This action cannot be undone.')) return

  isDeleting.value = true
  try {
    await issuesStore.deleteIssue(route.params.id)
    router.push('/issues')
  } catch {
    error.value = 'Failed to delete issue'
  } finally {
    isDeleting.value = false
  }
}

const initMap = () => {
  if (issue.value?.latitude && issue.value?.longitude) {
    const mapContainer = document.getElementById('issue-map')
    if (mapContainer && !mapContainer.hasChildNodes()) {
      const map = L.map('issue-map').setView([issue.value.latitude, issue.value.longitude], 15)

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

const fetchIssueDetails = async () => {
  isLoading.value = true
  error.value = ''
  try {
    issue.value = await issuesStore.fetchIssueById(route.params.id)

    // Initialize map after issue is loaded
    setTimeout(initMap, 100)
  } catch {
    error.value = 'Failed to load issue details'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchIssueDetails()
})
</script>
