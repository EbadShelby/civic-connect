<template>
  <div class="bg-bg flex min-h-screen flex-col font-sans">
    <!-- Navigation -->
    <nav
      class="bg-surface/80 sticky top-0 z-50 border-b border-gray-100 shadow-sm backdrop-blur-md transition-all duration-300 dark:border-gray-800"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center gap-3">
            <router-link to="/" class="group flex items-center gap-2">
              <div
                class="bg-primary group-hover:bg-primary-hover rounded-lg p-1.5 text-white transition-colors"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-6 w-6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                  />
                </svg>
              </div>
              <span class="text-primary font-display text-xl font-bold tracking-tight"
                >CivicConnect</span
              >
            </router-link>
          </div>

          <div class="hidden items-center gap-6 md:flex">
            <!-- Public Links -->
            <template v-if="!authStore.isAuthenticated">
              <router-link
                to="/"
                class="text-text-light hover:text-primary font-medium transition-colors"
                >Home</router-link
              >
            </template>

            <!-- Authenticated Links -->
            <template v-if="authStore.isAuthenticated">
              <router-link
                to="/dashboard"
                active-class="text-primary font-semibold"
                class="text-text-light hover:text-primary font-medium transition-colors"
                >Dashboard</router-link
              >
              <router-link
                to="/issues"
                active-class="text-primary font-semibold"
                class="text-text-light hover:text-primary font-medium transition-colors"
                >Issues</router-link
              >

              <!-- Admin Links -->
              <template v-if="authStore.isAdmin">
                <router-link
                  to="/admin/dashboard"
                  active-class="text-primary font-semibold"
                  class="text-text-light hover:text-primary font-medium transition-colors"
                  >Admin</router-link
                >
              </template>

              <!-- Profile Dropdown (Simplified for now) -->
              <div class="group relative">
                <button
                  class="text-text hover:text-primary flex items-center gap-2 font-medium transition-colors"
                >
                  <span
                    class="bg-primary-light text-primary rounded-full px-3 py-1 text-sm font-semibold"
                  >
                    {{ authStore.user?.firstName?.[0] || 'U' }}
                  </span>
                </button>
                <div
                  class="invisible absolute right-0 z-50 mt-2 w-48 origin-top-right transform rounded-xl border border-gray-100 bg-white py-1 opacity-0 shadow-lg transition-all duration-200 group-hover:visible group-hover:opacity-100"
                >
                  <router-link
                    to="/profile"
                    class="text-text hover:text-primary block px-4 py-2 text-sm hover:bg-gray-50"
                    >Profile</router-link
                  >
                  <router-link
                    to="/my-issues"
                    class="text-text hover:text-primary block px-4 py-2 text-sm hover:bg-gray-50"
                    >My Issues</router-link
                  >
                  <button
                    @click="handleLogout"
                    class="text-danger hover:bg-danger-light/30 block w-full px-4 py-2 text-left text-sm"
                  >
                    Sign out
                  </button>
                </div>
              </div>
            </template>

            <!-- Auth Buttons -->
            <template v-else>
              <div class="flex items-center gap-3">
                <router-link
                  to="/login"
                  class="text-primary hover:text-primary-hover px-4 py-2 font-semibold transition-colors"
                  >Sign In</router-link
                >
                <router-link
                  to="/register"
                  class="btn-primary hover-lift shadow-primary/30 rounded-lg px-5 py-2 font-semibold shadow-sm"
                  >Get Started</router-link
                >
              </div>
            </template>
          </div>

          <!-- Mobile menu button (Simplified) -->
          <div class="flex items-center md:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="text-text rounded-lg p-2 transition-colors hover:bg-gray-100"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="h-6 w-6"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-if="mobileMenuOpen"
        class="absolute left-0 z-40 w-full border-t border-gray-100 bg-white shadow-lg md:hidden"
      >
        <div class="space-y-1 px-4 pt-2 pb-6">
          <template v-if="authStore.isAuthenticated">
            <router-link
              to="/dashboard"
              class="text-text hover:text-primary block rounded-lg px-3 py-3 text-base font-medium hover:bg-gray-50"
              @click="mobileMenuOpen = false"
              >Dashboard</router-link
            >
            <router-link
              to="/issues"
              class="text-text hover:text-primary block rounded-lg px-3 py-3 text-base font-medium hover:bg-gray-50"
              @click="mobileMenuOpen = false"
              >All Issues</router-link
            >
            <router-link
              to="/my-issues"
              class="text-text hover:text-primary block rounded-lg px-3 py-3 text-base font-medium hover:bg-gray-50"
              @click="mobileMenuOpen = false"
              >My Issues</router-link
            >
            <template v-if="authStore.isAdmin">
              <router-link
                to="/admin/dashboard"
                class="text-text hover:text-primary block rounded-lg px-3 py-3 text-base font-medium hover:bg-gray-50"
                @click="mobileMenuOpen = false"
                >Admin Dashboard</router-link
              >
            </template>
            <button
              @click="handleLogout"
              class="text-danger hover:bg-danger-light/20 block w-full rounded-lg px-3 py-3 text-left text-base font-medium"
            >
              Sign out
            </button>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="text-text hover:text-primary block rounded-lg px-3 py-3 text-base font-medium hover:bg-gray-50"
              @click="mobileMenuOpen = false"
              >Sign In</router-link
            >
            <router-link
              to="/register"
              class="text-primary bg-primary-light/30 block rounded-lg px-3 py-3 text-base font-medium"
              @click="mobileMenuOpen = false"
              >Get Started</router-link
            >
          </template>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="grow">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-gray-100 bg-white py-12">
      <div
        class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:px-6 md:flex-row lg:px-8"
      >
        <div class="flex items-center gap-2 opacity-80 transition-opacity hover:opacity-100">
          <div class="bg-primary rounded-md p-1 text-white">
            <span class="text-xs font-bold">CC</span>
          </div>
          <span class="text-text font-display text-lg font-bold">CivicConnect</span>
        </div>
        <div class="text-text-light text-sm">
          &copy; {{ new Date().getFullYear() }} CivicConnect. Empowering communities.
        </div>
        <div class="flex gap-6">
          <!-- Social placeholders -->
          <a href="#" class="text-text-lighter hover:text-primary transition-colors">
            <span class="sr-only">Twitter</span>
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"
              />
            </svg>
          </a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/authStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const mobileMenuOpen = ref(false)

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
  mobileMenuOpen.value = false
}
</script>
