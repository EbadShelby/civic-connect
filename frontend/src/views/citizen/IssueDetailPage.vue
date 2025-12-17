<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="max-w-4xl mx-auto py-8 px-4">
      <!-- Back Button -->
      <router-link to="/issues" class="inline-flex items-center gap-2 text-[#75a743] font-semibold mb-6 hover:underline">
        <i class="fas fa-arrow-left"></i>
        Back to Issues
      </router-link>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="fas fa-spinner fa-spin text-3xl text-[#75a743]"></i>
        <p class="text-[#819796] mt-4">Loading issue details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl shadow-md p-8">
        <p class="text-red-800">
          <i class="fas fa-exclamation-circle mr-2"></i>
          {{ error }}
        </p>
        <router-link to="/issues" class="text-[#75a743] font-semibold hover:underline mt-4 inline-block">
          Return to issues list
        </router-link>
      </div>

      <!-- Issue Details -->
      <div v-else-if="issue">
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
          <!-- Issue Image -->
          <div v-if="issue.image_url" class="w-full h-96 overflow-hidden">
            <img :src="issue.image_url" :alt="issue.title" class="w-full h-full object-cover" />
          </div>

          <!-- Issue Info -->
          <div class="p-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
              <div class="flex-grow">
                <h1 class="text-3xl font-bold text-[#10141f] mb-2">{{ issue.title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                  <!-- Status Badge -->
                  <div
                    :class="{
                      'bg-blue-100 text-blue-800': issue.status === 'open',
                      'bg-yellow-100 text-yellow-800': issue.status === 'in_progress',
                      'bg-green-100 text-green-800': issue.status === 'resolved',
                      'bg-gray-100 text-gray-800': issue.status === 'closed'
                    }"
                    class="px-4 py-1 rounded-full text-sm font-semibold uppercase"
                  >
                    {{ issue.status }}
                  </div>

                  <!-- Category -->
                  <span class="text-[#819796] flex items-center gap-2">
                    <i class="fas fa-tag text-[#75a743]"></i>
                    {{ formatCategory(issue.category) }}
                  </span>

                  <!-- Priority -->
                  <span
                    :class="{
                      'text-red-600': issue.priority === 'critical',
                      'text-orange-600': issue.priority === 'high',
                      'text-yellow-600': issue.priority === 'medium',
                      'text-blue-600': issue.priority === 'low'
                    }"
                    class="font-semibold"
                  >
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ issue.priority?.charAt(0).toUpperCase() + issue.priority?.slice(1) }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col gap-2 min-w-fit">
                <button
                  @click="toggleUpvote"
                  :class="{
                    'bg-[#cf573c] text-white': issue.user_has_upvoted,
                    'bg-gray-200 text-[#10141f]': !issue.user_has_upvoted
                  }"
                  class="px-6 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2"
                >
                  <i class="fas fa-arrow-up"></i>
                  Upvote ({{ issue.upvote_count || 0 }})
                </button>
                <button
                  v-if="canDeleteIssue"
                  @click="deleteIssue"
                  :disabled="isDeleting"
                  class="px-6 py-2 rounded-lg font-semibold bg-red-100 text-red-800 hover:bg-red-200 transition-colors disabled:opacity-50"
                >
                  <i v-if="isDeleting" class="fas fa-spinner fa-spin mr-2"></i>
                  {{ isDeleting ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </div>

            <!-- Meta Information -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
              <div>
                <p class="text-[#819796] text-sm">Reported by</p>
                <p class="text-[#10141f] font-semibold">{{ issue.user_name || 'Anonymous' }}</p>
              </div>
              <div>
                <p class="text-[#819796] text-sm">Date</p>
                <p class="text-[#10141f] font-semibold">{{ formatDate(issue.created_at) }}</p>
              </div>
              <div>
                <p class="text-[#819796] text-sm">Upvotes</p>
                <p class="text-[#10141f] font-semibold">{{ issue.upvote_count || 0 }}</p>
              </div>
              <div>
                <p class="text-[#819796] text-sm">Comments</p>
                <p class="text-[#10141f] font-semibold">{{ issue.comment_count || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-6">
          <h2 class="text-2xl font-bold text-[#10141f] mb-4">Description</h2>
          <p class="text-[#10141f] leading-relaxed whitespace-pre-wrap">{{ issue.description }}</p>
        </div>

        <!-- Location -->
        <div v-if="issue.latitude && issue.longitude" class="bg-white rounded-xl shadow-md p-8 mb-6">
          <h2 class="text-2xl font-bold text-[#10141f] mb-4">Location</h2>
          <p class="text-[#819796] text-sm mb-4">
            <i class="fas fa-map-marker-alt mr-2"></i>
            {{ issue.latitude.toFixed(6) }}, {{ issue.longitude.toFixed(6) }}
          </p>
          <div id="issue-map" class="h-64 rounded-lg border border-gray-300"></div>
        </div>

        <!-- Comments Section -->
        <div class="bg-white rounded-xl shadow-md p-8">
          <h2 class="text-2xl font-bold text-[#10141f] mb-6">Comments ({{ comments.length }})</h2>

          <!-- Add Comment Form -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <textarea
              v-model="newComment"
              placeholder="Add a comment..."
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#25562e]"
            ></textarea>
            <button
              @click="addComment"
              :disabled="!newComment.trim() || isSubmittingComment"
              class="mt-3 px-6 py-2 bg-[#25562e] text-white rounded-lg font-semibold hover:bg-[#1a3d21] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="isSubmittingComment" class="fas fa-spinner fa-spin mr-2"></i>
              {{ isSubmittingComment ? 'Posting...' : 'Post Comment' }}
            </button>
          </div>

          <!-- Comments List -->
          <div v-if="comments.length === 0" class="text-center py-8">
            <p class="text-[#819796]">No comments yet. Be the first to comment!</p>
          </div>

          <div v-else class="space-y-6">
            <div v-for="comment in comments" :key="comment.id" class="border-l-4 border-[#75a743] pl-4">
              <div class="flex items-start justify-between mb-2">
                <div>
                  <p class="font-semibold text-[#10141f]">{{ comment.user_name || 'Anonymous' }}</p>
                  <p class="text-xs text-[#819796]">{{ formatDate(comment.created_at) }}</p>
                </div>
                <div v-if="canEditComment(comment)" class="flex gap-2">
                  <button
                    @click="startEditingComment(comment.id)"
                    class="text-[#75a743] text-sm hover:underline"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    @click="deleteComment(comment.id)"
                    class="text-[#cf573c] text-sm hover:underline"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <!-- Editing Mode -->
              <div v-if="editingCommentId === comment.id" class="mt-2">
                <textarea
                  v-model="editingCommentText"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                ></textarea>
                <div class="flex gap-2 mt-2">
                  <button
                    @click="saveComment(comment.id)"
                    class="px-3 py-1 bg-[#25562e] text-white rounded text-sm"
                  >
                    Save
                  </button>
                  <button @click="editingCommentId = null" class="px-3 py-1 bg-gray-200 text-[#10141f] rounded text-sm">
                    Cancel
                  </button>
                </div>
              </div>

              <!-- View Mode -->
              <p v-else class="text-[#10141f]">{{ comment.content }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useIssuesStore } from '../../stores/issuesStore'
import L from 'leaflet'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const issuesStore = useIssuesStore()

const issue = ref(null)
const comments = ref([])
const newComment = ref('')
const isLoading = ref(false)
const error = ref('')
const isSubmittingComment = ref(false)
const isDeleting = ref(false)
const editingCommentId = ref(null)
const editingCommentText = ref('')

const canDeleteIssue = computed(() => {
  return authStore.user?.id === issue.value?.user_id
})

const canEditComment = (comment) => {
  return authStore.user?.id === comment.user_id
}

const formatCategory = (category) => {
  const categoryMap = {
    pothole: 'Pothole',
    trash: 'Trash/Littering',
    streetlight: 'Street Light',
    graffiti: 'Graffiti',
    water_leak: 'Water Leak',
    tree_damage: 'Tree Damage',
    sidewalk: 'Sidewalk Damage',
    other: 'Other'
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
    return 'Yesterday at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined })
  }
}

const toggleUpvote = async () => {
  try {
    if (issue.value?.user_has_upvoted) {
      await issuesStore.removeUpvote(route.params.id)
    } else {
      await issuesStore.upvoteIssue(route.params.id)
    }

    // Update local issue state
    const updated = issuesStore.currentIssue
    if (updated) {
      issue.value = { ...issue.value, ...updated }
    }
  } catch (err) {
    error.value = 'Failed to upvote issue'
  }
}

const addComment = async () => {
  if (!newComment.value.trim()) return

  isSubmittingComment.value = true
  try {
    const result = await issuesStore.addComment(route.params.id, newComment.value)
    comments.value.push(result.comment)
    newComment.value = ''

    // Update issue comment count
    issue.value.comment_count = result.commentCount
  } catch (err) {
    error.value = 'Failed to add comment'
  } finally {
    isSubmittingComment.value = false
  }
}

const startEditingComment = (commentId) => {
  const comment = comments.value.find((c) => c.id === commentId)
  if (comment) {
    editingCommentId.value = commentId
    editingCommentText.value = comment.content
  }
}

const saveComment = async (commentId) => {
  try {
    const result = await issuesStore.updateComment(commentId, editingCommentText.value)
    const index = comments.value.findIndex((c) => c.id === commentId)
    if (index !== -1) {
      comments.value[index] = result.comment
    }
    editingCommentId.value = null
  } catch (err) {
    error.value = 'Failed to update comment'
  }
}

const deleteComment = async (commentId) => {
  if (!confirm('Are you sure you want to delete this comment?')) return

  try {
    const result = await issuesStore.deleteComment(route.params.id, commentId)
    comments.value = comments.value.filter((c) => c.id !== commentId)
    issue.value.comment_count = result.commentCount
  } catch (err) {
    error.value = 'Failed to delete comment'
  }
}

const deleteIssue = async () => {
  if (!confirm('Are you sure you want to delete this issue? This action cannot be undone.')) return

  isDeleting.value = true
  try {
    await issuesStore.deleteIssue(route.params.id)
    router.push('/issues')
  } catch (err) {
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
        maxZoom: 19
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
    comments.value = await issuesStore.fetchComments(route.params.id)

    // Initialize map after issue is loaded
    setTimeout(initMap, 100)
  } catch (err) {
    error.value = 'Failed to load issue details'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchIssueDetails()
})
</script>
