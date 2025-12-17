<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-7xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">
          Welcome, {{ authStore.user?.first_name || 'Citizen' }}!
        </h1>
        <p class="text-lg text-[#819796]">Here's what's happening in your community</p>
      </div>

      <!-- Stats Grid -->
      <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
        <!-- My Issues -->
        <div class="rounded-xl border-l-4 border-[#25562e] bg-white p-6 shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold tracking-wide text-[#819796] uppercase">My Issues</p>
              <p class="mt-2 text-3xl font-bold text-[#25562e]">{{ stats.myIssuesCount }}</p>
            </div>
            <FlagIcon class="h-8 w-8 text-[#25562e] opacity-20" />
          </div>
          <router-link
            to="/my-issues"
            class="mt-4 inline-block text-sm text-[#75a743] hover:underline"
          >
            View all →
          </router-link>
        </div>

        <!-- Issues Resolved -->
        <div class="rounded-xl border-l-4 border-[#75a743] bg-white p-6 shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold tracking-wide text-[#819796] uppercase">Resolved</p>
              <p class="mt-2 text-3xl font-bold text-[#75a743]">{{ stats.resolvedCount }}</p>
            </div>
            <CheckCircleIcon class="h-8 w-8 text-[#75a743] opacity-20" />
          </div>
        </div>

        <!-- Total Upvotes -->
        <div class="rounded-xl border-l-4 border-[#cf573c] bg-white p-6 shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold tracking-wide text-[#819796] uppercase">
                Upvotes Received
              </p>
              <p class="mt-2 text-3xl font-bold text-[#cf573c]">{{ stats.totalUpvotes }}</p>
            </div>
            <HandThumbUpIcon class="h-8 w-8 text-[#cf573c] opacity-20" />
          </div>
        </div>

        <!-- Community Issues -->
        <div class="rounded-xl border-l-4 border-indigo-600 bg-white p-6 shadow-md">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold tracking-wide text-[#819796] uppercase">
                Community Issues
              </p>
              <p class="mt-2 text-3xl font-bold text-indigo-600">{{ stats.totalIssues }}</p>
            </div>
            <MapPinIcon class="h-8 w-8 text-indigo-600 opacity-20" />
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Report New Issue -->
        <router-link
          to="/report-issue"
          class="rounded-xl bg-gradient-to-br from-[#25562e] to-[#1a3d21] p-8 text-white shadow-md transition-shadow hover:shadow-lg"
        >
          <div class="flex items-center gap-4">
            <PlusCircleIcon class="h-10 w-10 opacity-80" />
            <div>
              <h3 class="text-xl font-bold">Report Issue</h3>
              <p class="mt-1 text-[#a8d5a8]">Help your community by reporting an issue</p>
            </div>
          </div>
        </router-link>

        <!-- Browse Issues -->
        <router-link
          to="/issues"
          class="rounded-xl bg-gradient-to-br from-[#75a743] to-[#5a8530] p-8 text-white shadow-md transition-shadow hover:shadow-lg"
        >
          <div class="flex items-center gap-4">
            <ListBulletIcon class="h-10 w-10 opacity-80" />
            <div>
              <h3 class="text-xl font-bold">Browse Issues</h3>
              <p class="mt-1 text-[#c8e6c8]">View and upvote community issues</p>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Recent Issues Section -->
      <div class="mb-8 rounded-xl bg-white p-6 shadow-md">
        <div class="mb-6 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-[#10141f]">Recent Community Issues</h2>
          <router-link to="/issues" class="font-semibold text-[#75a743] hover:underline">
            See all →
          </router-link>
        </div>

        <div v-if="isLoading" class="py-12 text-center">
          <ArrowPathIcon class="mx-auto h-8 w-8 animate-spin text-[#75a743]" />
          <p class="mt-4 text-[#819796]">Loading issues...</p>
        </div>

        <div v-else-if="recentIssues.length === 0" class="py-12 text-center">
          <InboxIcon class="mx-auto mb-4 h-10 w-10 text-[#819796] opacity-50" />
          <p class="text-[#819796]">No issues reported yet. Be the first to report one!</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="issue in recentIssues"
            :key="issue.id"
            class="flex items-center gap-4 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50"
          >
            <!-- Status Badge -->
            <div class="shrink-0">
              <div
                :class="{
                  'bg-blue-100 text-blue-800': issue.status === 'open',
                  'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                  'bg-green-100 text-green-800': issue.status === 'resolved',
                  'bg-gray-100 text-gray-800': issue.status === 'closed',
                }"
                class="rounded-full px-3 py-1 text-xs font-semibold uppercase"
              >
                {{ issue.status }}
              </div>
            </div>

            <!-- Issue Info -->
            <div class="grow">
              <router-link
                :to="`/issues/${issue.id}`"
                class="text-lg font-semibold text-[#25562e] transition-colors hover:text-[#75a743]"
              >
                {{ issue.title }}
              </router-link>
              <p class="mt-1 text-sm text-[#819796]">
                {{ issue.category }} • {{ formatDate(issue.created_at) }}
              </p>
            </div>

            <!-- Stats -->
            <div class="flex items-center gap-4 text-sm">
              <span class="flex items-center gap-1 text-[#819796]">
                <HandThumbUpIcon class="h-4 w-4 text-[#cf573c]" />
                {{ issue.upvote_count || 0 }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- My Issues List -->
      <div class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="border-b border-gray-200 p-6">
          <h2 class="text-xl font-bold text-[#10141f]">My Reports</h2>
        </div>

        <div v-if="userIssuesLoading" class="p-12 text-center">
          <ArrowPathIcon class="mx-auto h-8 w-8 animate-spin text-[#75a743]" />
          <p class="mt-4 text-[#819796]">Loading your issues...</p>
        </div>

        <div
          v-else-if="userIssues.length === 0"
          class="flex flex-col items-center p-12 text-center"
        >
          <InboxIcon class="mb-4 h-12 w-12 text-[#819796] opacity-50" />
          <p class="mb-4 text-lg text-[#819796]">You haven't reported any issues yet.</p>
          <router-link
            to="/report-issue"
            class="inline-block font-bold text-[#75a743] hover:underline"
          >
            Report your first issue
          </router-link>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="issue in userIssues.slice(0, 3)"
            :key="issue.id"
            class="flex flex-col gap-4 p-6 transition-colors hover:bg-gray-50 md:flex-row md:items-center md:justify-between"
          >
            <div class="grow">
              <div class="mb-1 flex items-center gap-3">
                <h3 class="text-lg font-bold text-[#10141f]">{{ issue.title }}</h3>
                <span
                  :class="{
                    'bg-blue-100 text-blue-800': issue.status === 'open',
                    'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                    'bg-green-100 text-green-800': issue.status === 'resolved',
                    'bg-gray-100 text-gray-800': issue.status === 'closed',
                  }"
                  class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase"
                >
                  {{ issue.status }}
                </span>
              </div>
              <p class="mb-2 line-clamp-1 text-sm text-[#819796]">{{ issue.description }}</p>
              <p class="text-xs text-[#819796]">
                Reported on {{ new Date(issue.created_at).toLocaleDateString() }}
              </p>
            </div>

            <div class="flex items-center gap-4">
              <div class="min-w-[60px] text-center md:text-right">
                <p class="text-xs text-[#819796]">Upvotes</p>
                <p class="font-bold text-[#10141f]">{{ issue.upvote_count || 0 }}</p>
              </div>
              <router-link
                :to="`/issues/${issue.id}`"
                class="flex items-center font-semibold text-[#25562e] hover:text-[#1a3d21]"
              >
                View <ArrowRightIcon class="ml-1 h-4 w-4" />
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <router-link
      to="/report-issue"
      class="fixed right-6 bottom-6 z-50 flex items-center justify-center rounded-full bg-[#25562e] p-4 text-white shadow-lg transition-all hover:bg-[#1a3d21] md:hidden"
    >
      <PlusIcon class="h-6 w-6" />
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useIssuesStore } from '../../stores/issuesStore'
import {
  ListBulletIcon,
  CheckCircleIcon,
  InboxIcon,
  ArrowRightIcon,
  PlusIcon,
  FlagIcon,
  HandThumbUpIcon,
  MapPinIcon,
  PlusCircleIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/solid'

const authStore = useAuthStore()
const issuesStore = useIssuesStore()

const stats = ref({
  myIssuesCount: 0,
  resolvedCount: 0,
  totalUpvotes: 0,
  totalIssues: 0,
})

const recentIssues = ref([])
const userIssues = ref([])
const isLoading = ref(false)
const userIssuesLoading = ref(false)

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

const fetchRecentIssues = async () => {
  isLoading.value = true
  try {
    const allIssues = await issuesStore.fetchIssues({ limit: 5 })
    recentIssues.value = allIssues.slice(0, 5)

    // Calculate stats
    stats.value.totalIssues = allIssues.length
    stats.value.totalUpvotes = allIssues.reduce((sum, issue) => sum + (issue.upvote_count || 0), 0)
  } catch (error) {
    console.error('Error fetching recent issues:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchUserIssues = async () => {
  if (!authStore.user?.id) {
    console.warn('User ID not available, skipping user issues fetch')
    return
  }

  userIssuesLoading.value = true
  try {
    const issues = await issuesStore.fetchUserIssues(authStore.user.id)
    userIssues.value = issues

    // Update stats
    stats.value.myIssuesCount = issues.length
    stats.value.resolvedCount = issues.filter((i) => i.status === 'resolved').length
    stats.value.totalUpvotes = issues.reduce((sum, issue) => sum + (issue.upvote_count || 0), 0)
  } catch (error) {
    console.error('Error fetching user issues:', error)
  } finally {
    userIssuesLoading.value = false
  }
}

onMounted(() => {
  fetchRecentIssues()
  fetchUserIssues()
})
</script>
