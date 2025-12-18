<template>
  <div class="motion-preset-fade mx-auto max-w-7xl px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-text mb-2 text-3xl font-bold md:text-4xl">
        Welcome, {{ authStore.user?.first_name || 'Citizen' }}!
      </h1>
      <p class="text-text-light text-lg">Here's what's happening in your community</p>
    </div>

    <!-- Stats Grid -->
    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-4">
      <!-- My Issues -->
      <div
        class="shadow-soft hover-lift motion-preset-slide-up-sm motion-delay-75 rounded-2xl border border-gray-100 bg-white p-6 transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-text-light text-sm font-semibold tracking-wide uppercase">My Issues</p>
            <p class="text-primary mt-2 text-3xl font-bold">{{ stats.myIssuesCount }}</p>
          </div>
          <div class="bg-primary-light/20 rounded-xl p-3">
            <FlagIcon class="text-primary h-6 w-6" />
          </div>
        </div>
        <router-link
          to="/my-issues"
          class="text-accent hover:text-accent-hover group mt-4 inline-flex items-center text-sm font-medium transition-colors"
        >
          View all
          <ArrowRightIcon class="ml-1 h-3 w-3 transition-transform group-hover:translate-x-1" />
        </router-link>
      </div>

      <!-- Issues Resolved -->
      <div
        class="shadow-soft hover-lift motion-preset-slide-up-sm motion-delay-100 rounded-2xl border border-gray-100 bg-white p-6 transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-text-light text-sm font-semibold tracking-wide uppercase">Resolved</p>
            <p class="text-success mt-2 text-3xl font-bold">{{ stats.resolvedCount }}</p>
          </div>
          <div class="bg-success-light rounded-xl p-3">
            <CheckCircleIcon class="text-success h-6 w-6" />
          </div>
        </div>
      </div>

      <!-- Total Upvotes -->
      <div
        class="shadow-soft hover-lift motion-preset-slide-up-sm motion-delay-150 rounded-2xl border border-gray-100 bg-white p-6 transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-text-light text-sm font-semibold tracking-wide uppercase">
              Upvotes Received
            </p>
            <p class="text-warning mt-2 text-3xl font-bold">{{ stats.totalUpvotes }}</p>
          </div>
          <div class="bg-warning-light rounded-xl p-3">
            <HandThumbUpIcon class="text-warning h-6 w-6" />
          </div>
        </div>
      </div>

      <!-- Community Issues -->
      <div
        class="shadow-soft hover-lift motion-preset-slide-up-sm motion-delay-200 rounded-2xl border border-gray-100 bg-white p-6 transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-text-light text-sm font-semibold tracking-wide uppercase">
              Community Issues
            </p>
            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ stats.totalIssues }}</p>
          </div>
          <div class="rounded-xl bg-indigo-50 p-3">
            <MapPinIcon class="h-6 w-6 text-indigo-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
      <!-- Report New Issue -->
      <router-link
        to="/report-issue"
        class="group from-primary to-primary-hover shadow-primary/20 motion-preset-slide-up-sm motion-delay-300 relative overflow-hidden rounded-2xl bg-linear-to-br p-8 text-white shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl"
      >
        <div
          class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-white/10 blur-2xl transition-colors group-hover:bg-white/20"
        ></div>
        <div class="relative flex items-center gap-4">
          <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
            <PlusCircleIcon class="h-8 w-8 text-white" />
          </div>
          <div>
            <h3 class="text-xl font-bold">Report Issue</h3>
            <p class="text-primary-light mt-1">Help your community by reporting an issue</p>
          </div>
        </div>
      </router-link>

      <!-- Browse Issues -->
      <router-link
        to="/issues"
        class="group text-text shadow-soft hover:border-primary/30 motion-preset-slide-up-sm motion-delay-300 relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-lg"
      >
        <div
          class="bg-accent/5 group-hover:bg-accent/10 absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full blur-2xl transition-colors"
        ></div>
        <div class="relative flex items-center gap-4">
          <div class="bg-accent/10 text-accent rounded-xl p-3">
            <ListBulletIcon class="h-8 w-8" />
          </div>
          <div>
            <h3 class="group-hover:text-primary text-xl font-bold transition-colors">
              Browse Issues
            </h3>
            <p class="text-text-light mt-1">View and upvote community issues</p>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Recent Issues Section -->
    <div
      class="shadow-soft motion-preset-slide-up-sm motion-delay-500 mb-8 rounded-2xl border border-gray-100 bg-white p-6"
    >
      <div class="mb-6 flex items-center justify-between">
        <h2 class="text-text text-2xl font-bold">Recent Community Issues</h2>
        <router-link
          to="/issues"
          class="text-accent hover:text-accent-hover text-sm font-semibold hover:underline"
        >
          See all →
        </router-link>
      </div>

      <div v-if="isLoading" class="py-12 text-center">
        <ArrowPathIcon class="text-accent mx-auto h-8 w-8 animate-spin" />
        <p class="text-text-light mt-4">Loading issues...</p>
      </div>

      <div v-else-if="recentIssues.length === 0" class="py-12 text-center">
        <InboxIcon class="text-text-lighter mx-auto mb-4 h-10 w-10" />
        <p class="text-text-light">No issues reported yet. Be the first to report one!</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="issue in recentIssues"
          :key="issue.id"
          @click="router.push(`/issues/${issue.id}`)"
          class="flex cursor-pointer items-center gap-4 rounded-xl border border-gray-100 p-4 transition-all hover:border-gray-200 hover:bg-gray-50"
        >
          <!-- Status Badge -->
          <div class="shrink-0">
            <div
              :class="{
                'bg-blue-50 text-blue-700 ring-1 ring-blue-700/10': issue.status === 'open',
                'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20':
                  issue.status === 'in_progress',
                'bg-green-50 text-green-700 ring-1 ring-green-600/20': issue.status === 'resolved',
                'bg-gray-50 text-gray-600 ring-1 ring-gray-500/10': issue.status === 'closed',
              }"
              class="rounded-full px-3 py-1 text-xs font-semibold tracking-wider uppercase"
            >
              {{ issue.status }}
            </div>
          </div>

          <!-- Issue Info -->
          <div class="grow">
            <div class="text-text hover:text-primary text-lg font-semibold transition-colors">
              {{ issue.title }}
            </div>
            <p class="text-text-light mt-1 text-sm">
              {{ issue.category }} • {{ formatDate(issue.created_at) }}
            </p>
          </div>

          <!-- Stats -->
          <div class="hidden items-center gap-4 text-sm sm:flex">
            <span class="text-text-light flex items-center gap-1 rounded-lg bg-gray-50 px-2 py-1">
              <HandThumbUpIcon
                :class="issue.user_has_upvoted ? 'text-warning' : 'text-gray-400'"
                class="h-4 w-4"
              />
              <span class="text-text font-medium">{{ issue.upvote_count || 0 }}</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- My Issues List -->
    <div
      class="shadow-soft motion-preset-slide-up-sm motion-delay-500 overflow-hidden rounded-2xl border border-gray-100 bg-white"
    >
      <div class="border-b border-gray-100 bg-gray-50/50 p-6">
        <h2 class="text-text text-xl font-bold">My Reports</h2>
      </div>

      <div v-if="userIssuesLoading" class="p-12 text-center">
        <ArrowPathIcon class="text-accent mx-auto h-8 w-8 animate-spin" />
        <p class="text-text-light mt-4">Loading your issues...</p>
      </div>

      <div v-else-if="userIssues.length === 0" class="flex flex-col items-center p-12 text-center">
        <InboxIcon class="text-text-lighter mb-4 h-12 w-12" />
        <p class="text-text-light mb-4 text-lg">You haven't reported any issues yet.</p>
        <router-link
          to="/report-issue"
          class="btn-primary shadow-primary/30 rounded-lg px-6 py-2 font-semibold shadow-sm"
        >
          Report your first issue
        </router-link>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div
          v-for="issue in userIssues.slice(0, 3)"
          :key="issue.id"
          class="group flex flex-col gap-4 p-6 transition-colors hover:bg-gray-50 md:flex-row md:items-center md:justify-between"
        >
          <div class="grow">
            <div class="mb-2 flex items-center gap-3">
              <h3 class="text-text group-hover:text-primary text-lg font-bold transition-colors">
                {{ issue.title }}
              </h3>
              <span
                :class="{
                  'bg-blue-50 text-blue-700': issue.status === 'open',
                  'bg-yellow-50 text-yellow-700': issue.status === 'in_progress',
                  'bg-green-50 text-green-700': issue.status === 'resolved',
                  'bg-gray-50 text-gray-600': issue.status === 'closed',
                }"
                class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase"
              >
                {{ issue.status }}
              </span>
            </div>
            <p class="text-text-light mb-2 line-clamp-1 text-sm">{{ issue.description }}</p>
            <p class="text-text-lighter text-xs">
              Reported on {{ new Date(issue.created_at).toLocaleDateString() }}
            </p>
          </div>

          <div class="flex items-center gap-4">
            <div class="min-w-[60px] text-center md:text-right">
              <p class="text-text-light text-xs">Upvotes</p>
              <p class="text-text font-bold">{{ issue.upvote_count || 0 }}</p>
            </div>
            <router-link
              :to="`/issues/${issue.id}`"
              class="text-primary hover:text-primary-hover bg-primary-light/10 flex items-center rounded-lg px-3 py-1.5 font-semibold transition-colors"
            >
              View <ArrowRightIcon class="ml-1 h-4 w-4" />
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <router-link
      to="/report-issue"
      class="bg-primary shadow-primary/40 hover:bg-primary-hover fixed right-6 bottom-6 z-50 flex items-center justify-center rounded-full p-4 text-white shadow-lg transition-all hover:scale-110 active:scale-95 md:hidden"
    >
      <PlusIcon class="h-6 w-6" />
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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

const router = useRouter()
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
