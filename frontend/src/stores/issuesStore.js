import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_BASE_URL = 'http://localhost/civic-connect/backend/api'

export const useIssuesStore = defineStore('issues', () => {
  const issues = ref([])
  const currentIssue = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  const totalCount = ref(0)
  const filters = ref({
    status: null,
    category: null,
    sortBy: 'recent',
  })

  // Computed properties
  const filteredIssues = computed(() => {
    let result = [...issues.value]

    if (filters.value.status) {
      result = result.filter((issue) => issue.status === filters.value.status)
    }

    if (filters.value.category) {
      result = result.filter((issue) => issue.category === filters.value.category)
    }

    // Sort
    if (filters.value.sortBy === 'recent') {
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    } else if (filters.value.sortBy === 'upvotes') {
      result.sort((a, b) => b.upvote_count - a.upvote_count)
    }

    return result
  })

  const issueCategories = computed(() => [
    'roads',
    'street_lights',
    'trash',
    'water_drainage',
    'parks_recreation',
    'public_safety',
    'graffiti_vandalism',
    'noise',
    'other',
  ])

  const issueStatuses = computed(() => ['pending_review', 'in_progress', 'resolved'])

  const issuePriorities = computed(() => ['low', 'medium', 'high', 'critical'])

  // Fetch all issues
  const fetchIssues = async (params = {}) => {
    isLoading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const headers = {}
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }
      const response = await axios.get(`${API_BASE_URL}/issues`, { params, headers })
      issues.value = response.data.issues || []

      // Store total count from pagination metadata
      if (response.data.pagination) {
        totalCount.value = response.data.pagination.total || 0
      } else {
        // If no pagination, use array length (for backward compatibility)
        totalCount.value = issues.value.length
      }

      return issues.value
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to fetch issues'
      issues.value = [] // Ensure array on error
      totalCount.value = 0
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Fetch single issue
  const fetchIssueById = async (issueId) => {
    isLoading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const headers = {}
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }

      const response = await axios.get(`${API_BASE_URL}/issues/${issueId}`, { headers })
      currentIssue.value = response.data.issue

      return response.data.issue
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to fetch issue'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Create issue
  const createIssue = async (formData) => {
    isLoading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const response = await axios.post(`${API_BASE_URL}/issues`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      const newIssue = response.data.issue
      issues.value.unshift(newIssue)

      return {
        success: true,
        message: response.data.message,
        issue: newIssue,
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to create issue'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Update issue
  const updateIssue = async (issueId, formData) => {
    isLoading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const headers = {}
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }

      const response = await axios.put(`${API_BASE_URL}/issues/${issueId}`, formData, { headers })

      const updatedIssue = response.data.issue
      const index = issues.value.findIndex((issue) => issue.id === issueId)
      if (index !== -1) {
        // Update the issue in place to maintain reactivity
        Object.assign(issues.value[index], updatedIssue)
      } else {
        // If not found in array, try to add it
        issues.value.push(updatedIssue)
      }

      if (currentIssue.value?.id === issueId) {
        currentIssue.value = updatedIssue
      }

      return {
        success: true,
        message: response.data.message,
        issue: updatedIssue,
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to update issue'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Delete issue
  const deleteIssue = async (issueId) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await axios.delete(`${API_BASE_URL}/issues/${issueId}`)
      issues.value = issues.value.filter((issue) => issue.id !== issueId)

      if (currentIssue.value?.id === issueId) {
        currentIssue.value = null
      }

      return {
        success: true,
        message: response.data.message,
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to delete issue'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Fetch user issues
  const fetchUserIssues = async (userId) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await axios.get(`${API_BASE_URL}/users/${userId}/issues`)
      return response.data.issues || []
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to fetch user issues'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }

  // Upvote issue
  const upvoteIssue = async (issueId) => {
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const response = await axios.post(
        `${API_BASE_URL}/issues/${issueId}/upvotes`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        },
      )

      // Update issue in list
      const issue = issues.value.find((i) => i.id === issueId)
      if (issue) {
        issue.upvote_count = response.data.upvote_count
        issue.user_has_upvoted = true
      }

      // Update current issue
      if (currentIssue.value?.id === issueId) {
        currentIssue.value.upvote_count = response.data.upvote_count
        currentIssue.value.user_has_upvoted = true
      }

      return {
        success: true,
        upvoteCount: response.data.upvote_count,
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to upvote issue'
      throw error.value
    }
  }

  // Remove upvote
  const removeUpvote = async (issueId) => {
    error.value = null
    try {
      const token = localStorage.getItem('token')
      const response = await axios.delete(`${API_BASE_URL}/issues/${issueId}/upvotes`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      // Update issue in list
      const issue = issues.value.find((i) => i.id === issueId)
      if (issue) {
        issue.upvote_count = response.data.upvote_count
        issue.user_has_upvoted = false
      }

      // Update current issue
      if (currentIssue.value?.id === issueId) {
        currentIssue.value.upvote_count = response.data.upvote_count
        currentIssue.value.user_has_upvoted = false
      }

      return {
        success: true,
        upvoteCount: response.data.upvote_count,
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to remove upvote'
      throw error.value
    }
  }

  // Set filters
  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  // Reset filters
  const resetFilters = () => {
    filters.value = {
      status: null,
      category: null,
      sortBy: 'recent',
    }
  }

  return {
    issues,
    currentIssue,
    isLoading,
    error,
    totalCount,
    filters,
    filteredIssues,
    issueCategories,
    issueStatuses,
    issuePriorities,
    fetchIssues,
    fetchIssueById,
    createIssue,
    updateIssue,
    deleteIssue,
    fetchUserIssues,
    upvoteIssue,
    removeUpvote,
    setFilters,
    resetFilters,
  }
})
