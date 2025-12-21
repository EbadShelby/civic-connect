<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-4xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">
          {{ isOwnProfile ? 'My Profile' : `${profileUser?.first_name}'s Profile` }}
        </h1>
        <p class="text-[#819796]">
          {{
            isOwnProfile
              ? 'Manage your account settings and preferences'
              : 'View user profile and activity'
          }}
        </p>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoadingProfile"
        class="flex flex-col items-center rounded-xl bg-white p-12 text-center shadow-md"
      >
        <div
          class="h-8 w-8 animate-spin rounded-full border-4 border-[#25562e] border-t-transparent"
        ></div>
        <p class="mt-4 text-[#819796]">Loading profile...</p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="profileError"
        class="rounded-xl border border-red-200 bg-red-50 p-8 shadow-md"
      >
        <p class="text-red-800">{{ profileError }}</p>
        <router-link to="/" class="mt-4 inline-block font-semibold text-[#75a743] hover:underline">
          Return to home
        </router-link>
      </div>

      <!-- Profile Card -->
      <div v-else-if="profileUser" class="overflow-hidden rounded-xl bg-white shadow-md">
        <!-- Banner/Header -->
        <div class="h-32 bg-[#25562e]"></div>

        <!-- Profile Info -->
        <div class="px-8 pb-8">
          <div class="relative mb-6 flex items-end justify-between">
            <!-- Avatar -->
            <div class="-mt-12 flex items-center">
              <div
                class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-[#ebede9] text-3xl font-bold text-[#25562e]"
              >
                {{ userInitials }}
              </div>
            </div>

            <!-- Edit Button (only for own profile) -->
            <router-link
              v-if="isOwnProfile"
              to="/edit-profile"
              class="rounded-lg bg-[#25562e] px-4 py-2 font-semibold text-white transition-colors hover:bg-[#1a3d21]"
            >
              Edit Profile
            </router-link>
          </div>

          <!-- User Details -->
          <div class="mb-8">
            <div class="flex items-center gap-3">
              <h2 class="text-2xl font-bold text-[#10141f]">{{ userFullName }}</h2>
              <!-- Online Status Indicator -->
              <div class="flex items-center gap-2">
                <div
                  :class="isOwnProfile ? 'bg-green-500' : 'bg-gray-400'"
                  class="h-3 w-3 rounded-full"
                  :title="isOwnProfile ? 'Online' : 'Offline'"
                ></div>
                <span
                  :class="isOwnProfile ? 'text-green-600' : 'text-gray-500'"
                  class="text-sm font-medium"
                >
                  {{ isOwnProfile ? 'Online' : 'Offline' }}
                </span>
              </div>
            </div>
            <p class="text-[#819796]">{{ profileUser.email }}</p>
            <div class="mt-4 flex flex-wrap gap-4">
              <span
                v-if="profileUser.phone"
                class="inline-flex items-center text-sm text-[#819796]"
              >
                <PhoneIcon class="mr-1 h-4 w-4" />
                {{ profileUser.phone }}
              </span>
              <span
                v-if="profileUser.location"
                class="inline-flex items-center text-sm text-[#819796]"
              >
                <MapPinIcon class="mr-1 h-4 w-4" />
                {{ profileUser.location }}
              </span>
              <span class="inline-flex items-center text-sm text-[#819796]">
                <CalendarIcon class="mr-1 h-4 w-4" />
                Joined {{ formatDate(profileUser.created_at) }}
              </span>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-8 sm:grid-cols-2">
            <div class="rounded-lg bg-[#ebede9] p-4 text-center">
              <p class="text-2xl font-bold text-[#25562e]">
                {{ isLoadingStats ? '...' : userStats.issuesReported }}
              </p>
              <p class="text-sm font-semibold text-[#819796]">Issues Reported</p>
            </div>
            <div class="rounded-lg bg-[#ebede9] p-4 text-center">
              <p class="text-2xl font-bold text-[#25562e]">
                {{ isLoadingStats ? '...' : userStats.upvotesReceived }}
              </p>
              <p class="text-sm font-semibold text-[#819796]">Received Upvotes</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { PhoneIcon, MapPinIcon, CalendarIcon } from '@heroicons/vue/24/solid'
import axios from 'axios'

const route = useRoute()
const authStore = useAuthStore()

const profileUser = ref(null)
const isLoadingProfile = ref(true)
const isLoadingStats = ref(true)
const profileError = ref('')
const userStats = ref({
  issuesReported: 0,
  upvotesReceived: 0,
})

// Determine which user's profile to show
const profileUserId = computed(() => {
  return route.params.id ? parseInt(route.params.id) : authStore.user?.id
})

// Check if viewing own profile
const isOwnProfile = computed(() => {
  return profileUserId.value === authStore.user?.id
})

const userFullName = computed(() => {
  if (profileUser.value) {
    return `${profileUser.value.first_name} ${profileUser.value.last_name}`
  }
  return 'User'
})

const userInitials = computed(() => {
  if (profileUser.value) {
    return (
      profileUser.value.first_name.charAt(0) + profileUser.value.last_name.charAt(0)
    ).toUpperCase()
  }
  return 'U'
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
}

const fetchUserProfile = async () => {
  if (!profileUserId.value) return

  isLoadingProfile.value = true
  profileError.value = ''

  try {
    const response = await axios.get(
      `http://localhost/civic-connect/backend/api/users/${profileUserId.value}`,
    )

    if (response.data.success && response.data.user) {
      profileUser.value = response.data.user
    }
  } catch (error) {
    console.error('Failed to fetch user profile:', error)
    profileError.value =
      error.response?.data?.error || 'Failed to load profile. User may not exist.'
  } finally {
    isLoadingProfile.value = false
  }
}

const fetchUserStats = async () => {
  if (!profileUserId.value) return

  isLoadingStats.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(
      `http://localhost/civic-connect/backend/api/users/${profileUserId.value}/stats`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    )

    if (response.data.success && response.data.stats) {
      userStats.value.issuesReported = response.data.stats.issues_reported
      userStats.value.upvotesReceived = response.data.stats.upvotes_received
    }
  } catch (error) {
    console.error('Failed to fetch user stats:', error)
    // Don't show error for stats, just keep at 0
  } finally {
    isLoadingStats.value = false
  }
}

const loadProfile = async () => {
  await fetchUserProfile()
  await fetchUserStats()
}

// Watch for route parameter changes
watch(
  () => route.params.id,
  () => {
    loadProfile()
  },
)

onMounted(() => {
  loadProfile()
})
</script>
