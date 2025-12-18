import { defineStore } from 'pinia'
import axios from 'axios'

const API_BASE_URL = 'http://localhost/civic-connect/backend/api'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    isLoading: false,
    error: null,
  }),

  actions: {
    async fetchNotifications(unreadOnly = false) {
      this.isLoading = true
      this.error = null
      try {
        const response = await axios.get(`${API_BASE_URL}/notifications`, {
          params: { unread_only: unreadOnly },
        })
        this.notifications = response.data.notifications || []
        return response.data
      } catch (error) {
        this.error = error.response?.data?.error || 'Failed to fetch notifications'
        this.notifications = [] // Ensure array on error
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchUnreadCount() {
      try {
        const response = await axios.get(`${API_BASE_URL}/notifications/unread-count`)
        this.unreadCount = response.data.count
        return response.data.count
      } catch (error) {
        console.error('Failed to fetch unread count:', error)
        return 0
      }
    },

    async markAsRead(notificationId) {
      try {
        await axios.put(`${API_BASE_URL}/notifications/${notificationId}/read`)

        // Update local state
        const notification = this.notifications.find((n) => n.id === notificationId)
        if (notification && !notification.is_read) {
          notification.is_read = true
          this.unreadCount = Math.max(0, this.unreadCount - 1)
        }
      } catch (error) {
        console.error('Failed to mark notification as read:', error)
        throw error
      }
    },

    async markAllAsRead() {
      try {
        await axios.put(`${API_BASE_URL}/notifications/mark-all-read`)

        // Update local state
        this.notifications.forEach((n) => (n.is_read = true))
        this.unreadCount = 0
      } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
        throw error
      }
    },
  },
})
