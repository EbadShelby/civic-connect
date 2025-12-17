<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-4xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">My Profile</h1>
        <p class="text-[#819796]">Manage your account settings and preferences</p>
      </div>

      <!-- Profile Card -->
      <div class="overflow-hidden rounded-xl bg-white shadow-md">
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

            <!-- Edit Button -->
            <router-link
              to="/edit-profile"
              class="rounded-lg bg-[#25562e] px-4 py-2 font-semibold text-white transition-colors hover:bg-[#1a3d21]"
            >
              Edit Profile
            </router-link>
          </div>

          <!-- User Details -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-[#10141f]">{{ userFullName }}</h2>
            <p class="text-[#819796]">{{ authStore.user?.email }}</p>
            <div class="mt-4 flex flex-wrap gap-4">
              <span
                class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800 uppercase"
              >
                {{ authStore.user?.role }}
              </span>
              <span
                v-if="authStore.user?.phone"
                class="inline-flex items-center text-sm text-[#819796]"
              >
                <PhoneIcon class="mr-1 h-4 w-4" />
                {{ authStore.user.phone }}
              </span>
            </div>
          </div>

          <!-- Stats (Placeholder for now) -->
          <div class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-8 sm:grid-cols-3">
            <div class="rounded-lg bg-[#ebede9] p-4 text-center">
              <p class="text-2xl font-bold text-[#25562e]">0</p>
              <p class="text-sm font-semibold text-[#819796]">Issues Reported</p>
            </div>
            <div class="rounded-lg bg-[#ebede9] p-4 text-center">
              <p class="text-2xl font-bold text-[#25562e]">0</p>
              <p class="text-sm font-semibold text-[#819796]">Votes Given</p>
            </div>
            <div class="rounded-lg bg-[#ebede9] p-4 text-center">
              <p class="text-2xl font-bold text-[#25562e]">Active</p>
              <p class="text-sm font-semibold text-[#819796]">Account Status</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../stores/authStore'
import { PhoneIcon } from '@heroicons/vue/24/solid'

const authStore = useAuthStore()

const userFullName = computed(() => {
  if (authStore.user) {
    return `${authStore.user.first_name} ${authStore.user.last_name}`
  }
  return 'User'
})

const userInitials = computed(() => {
  if (authStore.user) {
    return (authStore.user.first_name.charAt(0) + authStore.user.last_name.charAt(0)).toUpperCase()
  }
  return 'U'
})
</script>
