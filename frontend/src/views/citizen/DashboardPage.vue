<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-[#10141f] mb-2">
          Welcome, {{ authStore.user?.first_name || 'Citizen' }}!
        </h1>
        <p class="text-[#819796] text-lg">Here's what's happening in your community</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- My Issues -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#25562e]">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[#819796] text-sm font-semibold uppercase tracking-wide">My Issues</p>
              <p class="text-3xl font-bold text-[#25562e] mt-2">{{ stats.myIssuesCount }}</p>
            </div>
            <i class="fas fa-flag text-3xl text-[#25562e] opacity-20"></i>
          </div>
          <router-link to="/my-issues" class="text-[#75a743] text-sm mt-4 inline-block hover:underline">
            View all →
          </router-link>
        </div>

        <!-- Issues Resolved -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#75a743]">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[#819796] text-sm font-semibold uppercase tracking-wide">Resolved</p>
              <p class="text-3xl font-bold text-[#75a743] mt-2">{{ stats.resolvedCount }}</p>
            </div>
            <i class="fas fa-check-circle text-3xl text-[#75a743] opacity-20"></i>
          </div>
        </div>

        <!-- Total Upvotes -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#cf573c]">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[#819796] text-sm font-semibold uppercase tracking-wide">Upvotes Received</p>
              <p class="text-3xl font-bold text-[#cf573c] mt-2">{{ stats.totalUpvotes }}</p>
            </div>
            <i class="fas fa-arrow-up text-3xl text-[#cf573c] opacity-20"></i>
          </div>
        </div>

        <!-- Community Issues -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-600">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[#819796] text-sm font-semibold uppercase tracking-wide">Community Issues</p>
              <p class="text-3xl font-bold text-indigo-600 mt-2">{{ stats.totalIssues }}</p>
            </div>
            <i class="fas fa-map-marker-alt text-3xl text-indigo-600 opacity-20"></i>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Report New Issue -->
        <router-link
          to="/report-issue"
          class="bg-gradient-to-br from-[#25562e] to-[#1a3d21] rounded-xl shadow-md p-8 text-white hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center gap-4">
            <i class="fas fa-plus-circle text-4xl opacity-80"></i>
            <div>
              <h3 class="text-xl font-bold">Report Issue</h3>
              <p class="text-[#a8d5a8] mt-1">Help your community by reporting an issue</p>
            </div>
          </div>
        </router-link>

        <!-- Browse Issues -->
        <router-link
          to="/issues"
          class="bg-gradient-to-br from-[#75a743] to-[#5a8530] rounded-xl shadow-md p-8 text-white hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center gap-4">
            <i class="fas fa-list text-4xl opacity-80"></i>
            <div>
              <h3 class="text-xl font-bold">Browse Issues</h3>
              <p class="text-[#c8e6c8] mt-1">View and upvote community issues</p>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Recent Issues Section -->
      <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-[#10141f]">Recent Community Issues</h2>
          <router-link to="/issues" class="text-[#75a743] font-semibold hover:underline">
            See all →
          </router-link>
        </div>

        <div v-if="isLoading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-3xl text-[#75a743]"></i>
          <p class="text-[#819796] mt-4">Loading issues...</p>
        </div>

        <div v-else-if="recentIssues.length === 0" class="text-center py-12">
          <i class="fas fa-inbox text-4xl text-[#819796] opacity-50 mb-4"></i>
          <p class="text-[#819796]">No issues reported yet. Be the first to report one!</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="issue in recentIssues"
            :key="issue.id"
            class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <!-- Status Badge -->
            <div class="flex-shrink-0">
              <div
                :class="{
                  'bg-blue-100 text-blue-800': issue.status === 'open',
                  'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                  'bg-green-100 text-green-800': issue.status === 'resolved',
                  'bg-gray-100 text-gray-800': issue.status === 'closed'
                }"
                class="px-3 py-1 rounded-full text-xs font-semibold uppercase"
              >
                {{ issue.status }}
              </div>
            </div>

            <!-- Issue Info -->
            <div class="flex-grow">
              <router-link
                :to="`/issues/${issue.id}`"
                class="text-lg font-semibold text-[#25562e] hover:text-[#75a743] transition-colors"
              >
                {{ issue.title }}
              </router-link>
              <p class="text-[#819796] text-sm mt-1">{{ issue.category }} • {{ formatDate(issue.created_at) }}</p>
            </div>

            <!-- Stats -->
            <div class="flex items-center gap-4 text-sm">
              <span class="flex items-center gap-1 text-[#819796]">
                <i class="fas fa-arrow-up text-[#cf573c]"></i>
                {{ issue.upvote_count || 0 }}
              </span>
              <span class="flex items-center gap-1 text-[#819796]">
                <i class="fas fa-comment text-indigo-600"></i>
                {{ issue.comment_count || 0 }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- My Recent Issues Section -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-[#10141f]">Your Recent Issues</h2>
          <router-link to="/my-issues" class="text-[#75a743] font-semibold hover:underline">
            See all →
          </router-link>
        </div>

        <div v-if="userIssuesLoading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-3xl text-[#75a743]"></i>
          <p class="text-[#819796] mt-4">Loading your issues...</p>
        </div>

        <div v-else-if="userIssues.length === 0" class="text-center py-12">
          <i class="fas fa-folder-open text-4xl text-[#819796] opacity-50 mb-4"></i>
          <p class="text-[#819796]">You haven't reported any issues yet.</p>
          <router-link
            to="/report-issue"
            class="text-[#75a743] font-semibold hover:underline inline-block mt-2"
          >
            Report your first issue
          </router-link>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="issue in userIssues.slice(0, 3)"
            :key="issue.id"
            class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <!-- Status Badge -->
            <div class="flex-shrink-0">
              <div
                :class="{
                  'bg-blue-100 text-blue-800': issue.status === 'open',
                  'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                  'bg-green-100 text-green-800': issue.status === 'resolved',
                  'bg-gray-100 text-gray-800': issue.status === 'closed'
                }"
                class="px-3 py-1 rounded-full text-xs font-semibold uppercase"
              >
                {{ issue.status }}
              </div>
            </div>

            <!-- Issue Info -->
            <div class="flex-grow">
              <router-link
                :to="`/issues/${issue.id}`"
                class="text-lg font-semibold text-[#25562e] hover:text-[#75a743] transition-colors"
              >
                {{ issue.title }}
              </router-link>
              <p class="text-[#819796] text-sm mt-1">{{ issue.category }} • {{ formatDate(issue.created_at) }}</p>
            </div>

            <!-- Stats -->
            <div class="flex items-center gap-4 text-sm">
              <span class="flex items-center gap-1 text-[#819796]">
                <i class="fas fa-arrow-up text-[#cf573c]"></i>
                {{ issue.upvote_count || 0 }}
              </span>
              <span class="flex items-center gap-1 text-[#819796]">
                <i class="fas fa-comment text-indigo-600"></i>
                {{ issue.comment_count || 0 }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/authStore'
import { useIssuesStore } from '../../stores/issuesStore'

const authStore = useAuthStore()
const issuesStore = useIssuesStore()

const stats = ref({
  myIssuesCount: 0,
  resolvedCount: 0,
  totalUpvotes: 0,
  totalIssues: 0
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
    return 'Yesterday at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined })
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
